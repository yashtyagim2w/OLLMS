<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Profile Model
 * 
 * Manages user profile data (first_name, last_name, dob, verification_status)
 * One-to-one relationship with Shield's users table
 */
class UserProfileModel extends Model
{
    protected $table            = 'user_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'verification_status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id'    => 'required|integer|is_unique[user_profiles.user_id,id,{id}]',
        'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-Z\s\'-]+$/]',
        'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-Z\s\'-]+$/]',
        'dob'        => 'required|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'first_name' => [
            'regex_match' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
        ],
        'last_name' => [
            'regex_match' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
        ],
    ];

    /**
     * Get user profile by user_id
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Create profile for a user
     */
    public function createForUser(int $userId, array $data): int|false
    {
        $data['user_id'] = $userId;
        $data['verification_status'] = 'PENDING';

        return $this->insert($data);
    }

    /**
     * Update profile verification status
     */
    public function updateVerificationStatus(int $userId, string $status): bool
    {
        return $this->where('user_id', $userId)
            ->set('verification_status', $status)
            ->update();
    }

    /**
     * Get full name
     */
    public function getFullName(int $userId): string
    {
        $profile = $this->getByUserId($userId);
        if (!$profile) {
            return 'Unknown User';
        }
        return trim($profile['first_name'] . ' ' . $profile['last_name']);
    }

    /**
     * Calculate age from DOB
     */
    public function getAge(int $userId): ?int
    {
        $profile = $this->getByUserId($userId);
        if (!$profile || empty($profile['dob'])) {
            return null;
        }

        $dob = new \DateTime($profile['dob']);
        $now = new \DateTime();
        return $now->diff($dob)->y;
    }

    /**
     * Check if user profile is complete (email verified)
     */
    public function isEmailVerified(int $userId): bool
    {
        $profile = $this->getByUserId($userId);
        return $profile && $profile['verification_status'] === 'COMPLETED';
    }
}
