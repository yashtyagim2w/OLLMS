<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Video Progress Model
 * 
 * Tracks user progress on training videos
 */
class VideoProgressModel extends Model
{
    protected $table            = 'video_progress';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'video_id',
        'watched_seconds',
        'last_position_seconds',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get progress for a specific user and video
     */
    public function getProgress(int $userId, int $videoId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();
    }

    /**
     * Get all video progress for a user
     */
    public function getUserProgress(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Count completed videos for a user
     */
    public function countCompleted(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->countAllResults();
    }

    /**
     * Count total completed videos across all users
     */
    public function countAllCompleted(): int
    {
        return $this->where('status', 'COMPLETED')->countAllResults();
    }

    /**
     * Update or create video progress
     */
    public function updateProgress(int $userId, int $videoId, array $data): bool
    {
        $existing = $this->getProgress($userId, $videoId);

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        $data['user_id'] = $userId;
        $data['video_id'] = $videoId;
        return $this->insert($data) !== false;
    }

    /**
     * Mark video as completed
     */
    public function markCompleted(int $userId, int $videoId): bool
    {
        return $this->updateProgress($userId, $videoId, ['status' => 'COMPLETED']);
    }
}
