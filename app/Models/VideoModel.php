<?php

namespace App\Models;

use CodeIgniter\Model;

class VideoModel extends Model
{
    protected $table = 'training_videos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false; // Using is_active=0 for soft delete instead
    protected $protectFields = true;
    protected $allowedFields = [
        'category_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'duration_seconds',
        'is_active',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_id' => 'required|integer|is_not_unique[categories.id]',
        'title' => 'required|max_length[255]',
        'video_url' => 'required|max_length[500]',
    ];

    protected $validationMessages = [
        'category_id' => [
            'required' => 'Category is required.',
            'is_not_unique' => 'Selected category does not exist.',
        ],
        'title' => [
            'required' => 'Video title is required.',
            'max_length' => 'Title cannot exceed 255 characters.',
        ],
        'video_url' => [
            'required' => 'Video URL is required.',
        ],
    ];

    /**
     * Create a new video record
     *
     * @param int $categoryId
     * @param string $title
     * @param string|null $description
     * @param string $videoUrl S3 key
     * @param int $durationSeconds
     * @return int|false Video ID or false on failure
     */
    public function createVideo(int $categoryId, string $title, ?string $description, string $videoUrl, int $durationSeconds = 0)
    {
        $data = [
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $description,
            'video_url' => $videoUrl,
            'duration_seconds' => $durationSeconds,
            'is_active' => 1,
        ];

        if ($this->insert($data)) {
            return $this->getInsertID();
        }

        return false;
    }

    /**
     * Get all videos with category information
     *
     * @param bool $activeOnly
     * @return array
     */
    public function getAllVideos(bool $activeOnly = false): array
    {
        $this->select('training_videos.*, categories.name as category_name')
            ->join('categories', 'categories.id = training_videos.category_id', 'left')
            ->orderBy('training_videos.created_at', 'DESC');

        if ($activeOnly) {
            $this->where('training_videos.is_active', 1);
        }

        return $this->findAll();
    }

    /**
     * Get videos by category
     *
     * @param int $categoryId
     * @param bool $activeOnly
     * @return array
     */
    public function getVideosByCategory(int $categoryId, bool $activeOnly = false): array
    {
        $this->select('training_videos.*, categories.name as category_name')
            ->join('categories', 'categories.id = training_videos.category_id', 'left')
            ->where('training_videos.category_id', $categoryId)
            ->orderBy('training_videos.created_at', 'DESC');

        if ($activeOnly) {
            $this->where('training_videos.is_active', 1);
        }

        return $this->findAll();
    }

    /**
     * Get a single video by ID with category info
     *
     * @param int $id
     * @return array|null
     */
    public function getVideoById(int $id): ?array
    {
        return $this->select('training_videos.*, categories.name as category_name')
            ->join('categories', 'categories.id = training_videos.category_id', 'left')
            ->where('training_videos.id', $id)
            ->first();
    }

    /**
     * Update video details
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateVideo(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Delete a video
     *
     * @param int $id
     * @return bool
     */
    public function deleteVideo(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Toggle video active status
     *
     * @param int $id
     * @return bool
     */
    public function toggleActive(int $id): bool
    {
        $video = $this->find($id);
        if (!$video) {
            return false;
        }

        return $this->update($id, ['is_active' => $video['is_active'] ? 0 : 1]);
    }
}
