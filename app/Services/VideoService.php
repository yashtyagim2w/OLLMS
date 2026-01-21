<?php

namespace App\Services;

use App\Models\VideoModel;
use Config\Aws;

/**
 * Video Service
 * 
 * Handles business logic for video management including:
 * - Generating upload URLs for videos and thumbnails
 * - Validating video upload parameters
 * - Managing pending uploads
 * - Creating video records
 */
class VideoService
{
    protected VideoModel $videoModel;
    protected $s3;
    protected Aws $awsConfig;

    public function __construct()
    {
        $this->videoModel = model('VideoModel');
        $this->s3 = service('s3');
        $this->awsConfig = config('Aws');
    }

    /**
     * Validate video upload parameters
     *
     * @param array $params [content_type, file_size, duration_seconds, filename]
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateUploadParams(array $params): array
    {
        $contentType = $params['content_type'] ?? '';
        $fileSize = (int) ($params['file_size'] ?? 0);
        $durationSeconds = (int) ($params['duration_seconds'] ?? 0);

        // Validate duration
        if ($durationSeconds < 0) {
            return ['valid' => false, 'error' => 'Duration cannot be negative.'];
        }

        if ($durationSeconds > VIDEO_MAX_DURATION_SECONDS) {
            return [
                'valid' => false,
                'error' => "Maximum video duration is " . VIDEO_MAX_DURATION_SECONDS . " seconds (" . getVideoMaxDurationFormatted() . ")."
            ];
        }

        // Validate file type
        if (!in_array($contentType, VIDEO_ALLOWED_MIME_TYPES)) {
            return ['valid' => false, 'error' => 'Only MP4 video files are allowed.'];
        }

        // Validate file size
        if ($fileSize > VIDEO_MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => "File size must be less than " . getVideoMaxFileSizeMB() . "MB."
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Generate presigned upload URLs for video and thumbnail
     *
     * @param string $filename Original filename
     * @param string $contentType MIME type
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    private function generateUploadUrls(string $filename, string $contentType): array
    {
        // Prepare extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($extension) || strtolower($extension) !== 'mp4') {
            $extension = 'mp4';
        }

        // Generate unique filename
        $newFilename = $this->s3->generateFilename($extension);

        // Generate video presigned URL
        $videoResult = $this->s3->getPresignedUploadUrl(
            $this->awsConfig->videosPrefix,
            $newFilename,
            $contentType,
            5, // 5 minutes expiry
            VIDEO_MAX_FILE_SIZE
        );

        if (!$videoResult['success']) {
            return ['success' => false, 'error' => 'Failed to generate video upload URL.'];
        }

        // Generate thumbnail presigned URL
        $thumbnailFilename = pathinfo($newFilename, PATHINFO_FILENAME) . '.jpg';
        $thumbnailResult = $this->s3->getPresignedUploadUrl(
            $this->awsConfig->thumbnailsPrefix,
            $thumbnailFilename,
            'image/jpeg',
            5 // 5 minutes expiry
        );

        $thumbnailKey = $this->awsConfig->thumbnailsPrefix . '/' . $thumbnailFilename;

        return [
            'success' => true,
            'data' => [
                'uploadUrl' => $videoResult['uploadUrl'],
                'key' => $videoResult['key'],
                'thumbnailUploadUrl' => $thumbnailResult['uploadUrl'] ?? null,
                'thumbnailKey' => $thumbnailKey,
            ],
            'error' => null,
        ];
    }

    /**
     * Initiate video upload - generates URLs and stores pending upload in session
     *
     * @param array $uploadData [filename, content_type, category_id, title, description, duration_seconds]
     * @return array ['success' => bool, 'data' => array, 'error' => string|null]
     */
    public function initiateUpload(array $uploadData): array
    {
        // Generate upload URLs
        $urlResult = $this->generateUploadUrls(
            $uploadData['filename'],
            $uploadData['content_type']
        );

        if (!$urlResult['success']) {
            return $urlResult;
        }

        // Store pending upload in session
        $this->storePendingUpload([
            'key' => $urlResult['data']['key'],
            'thumbnail_key' => $urlResult['data']['thumbnailKey'],
            'category_id' => $uploadData['category_id'],
            'title' => $uploadData['title'],
            'description' => $uploadData['description'] ?? '',
            'duration_seconds' => $uploadData['duration_seconds'] ?? 0,
        ]);

        return $urlResult;
    }

    /**
     * Store pending upload data in session
     *
     * @param array $uploadData Video and thumbnail keys + metadata
     * @return void
     */
    private function storePendingUpload(array $uploadData): void
    {
        $uploadData['created_at'] = time();
        session()->set('pending_video_upload', $uploadData);
    }

    /**
     * Get pending upload from session
     *
     * @return array|null
     */
    private function getPendingUpload(): ?array
    {
        return session('pending_video_upload');
    }

    /**
     * Clear pending upload from session
     *
     * @return void
     */
    private function clearPendingUpload(): void
    {
        session()->remove('pending_video_upload');
    }

    /**
     * Check if pending upload is expired (10 minutes)
     *
     * @param array $pendingUpload
     * @return bool
     */
    private function isPendingUploadExpired(array $pendingUpload): bool
    {
        return time() - ($pendingUpload['created_at'] ?? 0) > 600;
    }

    /**
     * Verify video file exists in S3
     *
     * @param string $key S3 object key
     * @return bool
     */
    public function verifyVideoInStorage(string $key): bool
    {
        return $this->s3->exists($key);
    }

    /**
     * Create video record in database
     *
     * @param array $data Video data
     * @return int|false Video ID or false on failure
     */
    public function createVideo(array $data)
    {
        return $this->videoModel->insert($data);
    }

    /**
     * Confirm and finalize video upload - verify and save to database
     *
     * @return array ['success' => bool, 'videoId' => int|null, 'error' => string|null]
     */
    public function confirmUpload(): array
    {
        // Get pending upload from session
        $pendingUpload = $this->getPendingUpload();

        if (!$pendingUpload) {
            return ['success' => false, 'videoId' => null, 'error' => 'No pending upload found. Please try again.'];
        }

        // Check session expiry
        if ($this->isPendingUploadExpired($pendingUpload)) {
            $this->clearPendingUpload();
            return ['success' => false, 'videoId' => null, 'error' => 'Upload session expired. Please try again.'];
        }

        // Verify video exists in S3
        if (!$this->verifyVideoInStorage($pendingUpload['key'])) {
            return ['success' => false, 'videoId' => null, 'error' => 'File not found in storage. Please upload again.'];
        }

        // Prepare video data
        $videoData = [
            'category_id' => $pendingUpload['category_id'],
            'title' => $pendingUpload['title'],
            'description' => $pendingUpload['description'] ?? '',
            'video_url' => $pendingUpload['key'],
            'thumbnail_url' => $pendingUpload['thumbnail_key'] ?? null,
            'duration_seconds' => $pendingUpload['duration_seconds'] ?? 0,
            'is_active' => 1,
        ];

        // Save to database
        $videoId = $this->createVideo($videoData);

        if (!$videoId) {
            return ['success' => false, 'videoId' => null, 'error' => 'Failed to save video record. Please try again.'];
        }

        // Clear pending upload
        $this->clearPendingUpload();

        return ['success' => true, 'videoId' => $videoId, 'error' => null];
    }

    /**
     * Generate presigned URL for viewing a video
     *
     * @param string $key S3 object key
     * @param int $expiryMinutes URL expiry in minutes
     * @return string|null Presigned URL or null on error
     */
    public function getVideoViewUrl(string $key, int $expiryMinutes = 60): ?string
    {
        return $this->s3->getPresignedUrl($key, $expiryMinutes);
    }

    /**
     * Format duration seconds to MM:SS string
     *
     * @param int $seconds
     * @return string
     */
    public function formatDuration(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Soft delete video (sets is_active = 0, keeps files in S3)
     *
     * @param int $id Video ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function deleteVideo(int $id): array
    {
        $video = $this->videoModel->find($id);

        if (!$video) {
            return ['success' => false, 'error' => 'Video not found.'];
        }

        // Soft delete by setting is_active = 0 (keeps S3 files for potential restore)
        if (!$this->videoModel->update($id, ['is_active' => 0])) {
            return ['success' => false, 'error' => 'Failed to delete video.'];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Update video details (title, description, category)
     *
     * @param int $id Video ID
     * @param array $data [title, description, category_id]
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updateVideo(int $id, array $data): array
    {
        $video = $this->videoModel->find($id);

        if (!$video) {
            return ['success' => false, 'error' => 'Video not found.'];
        }

        // Update allowed fields
        $updateData = array_intersect_key($data, array_flip(['title', 'description', 'category_id']));

        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid fields to update.'];
        }

        if (!$this->videoModel->update($id, $updateData)) {
            return ['success' => false, 'error' => 'Failed to update video.'];
        }

        return ['success' => true, 'error' => null];
    }
}
