<?php

namespace App\Controllers\Admin;

use App\Traits\ResponseTrait;
use App\Services\VideoService;

class VideoManagementController extends BaseAdminController
{
    use ResponseTrait;

    protected $videoModel;
    protected $categoryModel;
    protected VideoService $videoService;

    public function __construct()
    {
        $this->videoModel = model('VideoModel');
        $this->categoryModel = model('CategoryModel');
        $this->videoService = new VideoService();
    }

    /**
     * Display video management page
     */
    public function index()
    {
        return view('admin/videos', [
            'pageTitle' => 'Video Management',
            'maxFileSize' => VIDEO_MAX_FILE_SIZE,
            'maxFileSizeMB' => getVideoMaxFileSizeMB(),
            'maxDurationSeconds' => VIDEO_MAX_DURATION_SECONDS,
            'maxDurationFormatted' => getVideoMaxDurationFormatted(),
        ]);
    }

    /**
     * API: Get active categories for dropdown
     */
    public function apiGetCategories()
    {
        $categories = $this->categoryModel->getActiveCategories();
        return $this->jsonSuccess('Categories retrieved.', $categories);
    }

    /**
     * API: Get videos list with filters and pagination
     */
    public function getList()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = 10;
        $search = $this->request->getGet('search');
        $categoryId = $this->request->getGet('category');
        $activeStatus = $this->request->getGet('active_status');

        $videos = $this->videoModel->getAllVideos();

        // Apply search filter
        if ($search) {
            $videos = array_filter($videos, function ($video) use ($search) {
                return stripos($video['title'], $search) !== false ||
                    stripos($video['description'] ?? '', $search) !== false;
            });
        }

        // Apply category filter
        if ($categoryId !== null && $categoryId !== '') {
            $videos = array_filter($videos, fn($v) => $v['category_id'] == $categoryId);
        }

        // Apply active status filter
        if ($activeStatus !== null && $activeStatus !== '') {
            $isActive = filter_var($activeStatus, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $videos = array_filter($videos, fn($v) => $v['is_active'] == $isActive);
        }

        // Format duration for display and generate presigned URLs
        $videos = array_map(function ($video) {
            $video['duration'] = $this->videoService->formatDuration($video['duration_seconds']);

            // Generate presigned URLs for video and thumbnail
            $video['video_presigned_url'] = $this->videoService->getVideoViewUrl($video['video_url'], 15);
            $video['thumbnail_presigned_url'] = $video['thumbnail_url']
                ? $this->videoService->getVideoViewUrl($video['thumbnail_url'], 15)
                : null;

            return $video;
        }, $videos);

        $total = count($videos);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($videos, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($pagedData),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'totalRecords' => $total,
                ],
            ],
        ]);
    }

    /**
     * API: Generate presigned URL for video upload
     */
    public function apiGetUploadUrl()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        $title = trim($this->request->getPost('title') ?? '');
        $description = trim($this->request->getPost('description') ?? '');
        $filename = $this->request->getPost('filename');
        $contentType = $this->request->getPost('content_type');
        $fileSize = (int) $this->request->getPost('file_size');
        $durationSeconds = (int) ($this->request->getPost('duration_seconds') ?? 0);

        // Validation - Category exists
        if (!$this->categoryModel->categoryExists($categoryId)) {
            return $this->jsonError('Invalid category selected.');
        }

        // Validation - Title required
        if (empty($title)) {
            return $this->jsonError('Video title is required.');
        }

        // Validation - Title min length
        if (strlen($title) < 3) {
            return $this->jsonError('Title must be at least 3 characters.');
        }

        // Validation - Title max length
        if (strlen($title) > 255) {
            return $this->jsonError('Title cannot exceed 255 characters.');
        }

        // Validation - Description max length
        if (strlen($description) > 1000) {
            return $this->jsonError('Description cannot exceed 1000 characters.');
        }

        // Validate upload params via service
        $validation = $this->videoService->validateUploadParams([
            'content_type' => $contentType,
            'file_size' => $fileSize,
            'duration_seconds' => $durationSeconds,
        ]);

        if (!$validation['valid']) {
            return $this->jsonError($validation['error']);
        }

        // Initiate upload via service (generates URLs + stores in session)
        $result = $this->videoService->initiateUpload([
            'filename' => $filename,
            'content_type' => $contentType,
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'duration_seconds' => $durationSeconds,
        ]);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Upload URL generated.', $result['data']);
    }

    /**
     * API: Confirm video upload and save to database
     */
    public function apiConfirmUpload()
    {
        // Confirm upload via service (handles session, expiry, verification, and DB save)
        $result = $this->videoService->confirmUpload();

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Video uploaded successfully!', [
            'videoId' => $result['videoId'],
            'redirect' => '/admin/videos',
        ]);
    }

    /**
     * API: Toggle video active status
     */
    public function apiToggleActive($id)
    {
        if (!$this->videoModel->toggleActive($id)) {
            return $this->jsonError('Failed to update video status.');
        }

        return $this->jsonSuccess('Video status updated successfully.');
    }

    /**
     * API: Get presigned URL for viewing video
     */
    public function apiGetViewUrl()
    {
        $key = $this->request->getGet('key');

        if (empty($key)) {
            return $this->jsonError('Video key is required.');
        }

        // Generate presigned URL for viewing via service
        $url = $this->videoService->getVideoViewUrl($key, 15);

        if (!$url) {
            return $this->jsonError('Failed to generate video URL.');
        }

        return $this->jsonSuccess('Video URL generated.', [
            'url' => $url,
        ]);
    }

    /**
     * API: Delete video
     */
    public function apiDelete($id)
    {
        $result = $this->videoService->deleteVideo((int) $id);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Video deleted successfully.');
    }

    /**
     * API: Update video details
     */
    public function apiUpdate($id)
    {
        $json = $this->request->getJSON(true);

        $title = trim($json['title'] ?? '');
        $description = trim($json['description'] ?? '');
        $categoryId = (int) ($json['category_id'] ?? 0);

        // Get original values for change detection
        $originalTitle = trim($json['original_title'] ?? '');
        $originalDescription = trim($json['original_description'] ?? '');
        $originalCategoryId = (int) ($json['original_category_id'] ?? 0);

        // Validation - Title required
        if (empty($title)) {
            return $this->jsonError('Video title is required.');
        }

        // Validation - Title min length
        if (strlen($title) < 3) {
            return $this->jsonError('Title must be at least 3 characters.');
        }

        // Validation - Title max length
        if (strlen($title) > 255) {
            return $this->jsonError('Title cannot exceed 255 characters.');
        }

        // Validation - Description max length
        if (strlen($description) > 1000) {
            return $this->jsonError('Description cannot exceed 1000 characters.');
        }

        // Validation - Category exists
        if ($categoryId > 0 && !$this->categoryModel->categoryExists($categoryId)) {
            return $this->jsonError('Invalid category selected.');
        }

        // Check if any field has changed
        $hasChanges = $title !== $originalTitle
            || $description !== $originalDescription
            || $categoryId !== $originalCategoryId;

        if (!$hasChanges) {
            return $this->jsonError('No changes detected. Please modify at least one field to update.');
        }

        $data = ['title' => $title, 'description' => $description];
        if ($categoryId > 0) {
            $data['category_id'] = $categoryId;
        }

        $result = $this->videoService->updateVideo((int) $id, $data);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Video updated successfully.');
    }
}
