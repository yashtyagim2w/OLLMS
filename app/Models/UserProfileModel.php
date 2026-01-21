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
        'first_name' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-Z]+$/]',
        'last_name'  => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-Z]+$/]',
        'dob'        => 'required|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'first_name' => [
            'regex_match' => 'First name can only contain letters.',
        ],
        'last_name' => [
            'regex_match' => 'Last name can only contain letters.',
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

    /**
     * Get total count of registered users (with profiles)
     */
    public function getTotalCount(): int
    {
        return $this->countAllResults();
    }

    /**
     * Get count of new registrations for a specific date
     */
    public function getRegistrationsOnDate(string $date): int
    {
        return $this->where('DATE(created_at)', $date)->countAllResults();
    }

    /**
     * Get count of new registrations today
     */
    public function getRegistrationsToday(): int
    {
        return $this->getRegistrationsOnDate(date('Y-m-d'));
    }

    /**
     * Update profile by user ID
     */
    public function updateByUserId(int $userId, array $data): bool
    {
        return $this->where('user_id', $userId)->set($data)->update();
    }

    /**
     * Get paginated list of users with filters for admin panel
     * Complex join query across users, profiles, identities, and documents
     */
    public function getUsersForAdmin(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $db = db_connect();
        $builder = $db->table('users u')
            ->select('
                u.id,
                u.username,
                u.active,
                u.status,
                auth.secret as email,
                p.first_name,
                p.last_name,
                p.dob,
                p.verification_status,
                d.aadhar_number,
                d.status as doc_status
            ')
            ->join('auth_identities auth', 'auth.user_id = u.id AND auth.type = "email_password"', 'left')
            ->join('user_profiles p', 'p.user_id = u.id', 'inner')
            ->join('user_documents d', 'd.user_id = u.id AND d.id = (SELECT MAX(id) FROM user_documents WHERE user_id = u.id)', 'left')
            ->where('u.deleted_at IS NULL')
            ->whereIn('u.id', function ($subquery) {
                return $subquery->select('user_id')
                    ->from('auth_groups_users')
                    ->where('group', 'user');
            });

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('p.first_name', $search)
                ->orLike('p.last_name', $search)
                ->orLike('auth.secret', $search)
                ->orLike('d.aadhar_number', $search)
                ->groupEnd();
        }

        // Apply document status filter
        if (!empty($filters['status'])) {
            $status = strtoupper($filters['status']);
            if ($status === 'NOT_UPLOADED') {
                $builder->where('d.id IS NULL');
            } else {
                $builder->where('d.status', $status);
            }
        }

        // Apply active status filter
        if (!empty($filters['active_status'])) {
            if ($filters['active_status'] === 'active') {
                $builder->where('u.active', 1);
            } elseif ($filters['active_status'] === 'inactive') {
                $builder->where('u.active', 0);
            }
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'u.created_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';

        $sortMapping = [
            'name' => 'p.first_name',
            'email' => 'auth.secret',
            'dob' => 'p.dob',
            'created_at' => 'u.created_at',
        ];

        $orderBy = $sortMapping[$sortBy] ?? $sortBy;
        $builder->orderBy($orderBy, $sortOrder);

        // Get total count
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults(false);

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $users = $builder->limit($limit, $offset)->get()->getResultArray();

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * Get single user with full details for admin
     */
    public function getUserDetailsForAdmin(int $userId): ?array
    {
        $db = db_connect();
        return $db->table('users u')
            ->select('
                u.id,
                u.username,
                u.active,
                auth.secret as email,
                p.first_name,
                p.last_name,
                p.dob,
                p.verification_status,
                d.id as document_id,
                d.aadhar_number,
                d.status as doc_status,
                d.document_url
            ')
            ->join('auth_identities auth', 'auth.user_id = u.id AND auth.type = "email_password"', 'left')
            ->join('user_profiles p', 'p.user_id = u.id', 'left')
            ->join('user_documents d', 'd.user_id = u.id AND d.id = (SELECT MAX(id) FROM user_documents WHERE user_id = u.id)', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get user by email (from auth_identities)
     * Returns user_id if found, null otherwise
     */
    public function getUserByEmail(string $email): ?array
    {
        $db = db_connect();
        $result = $db->table('auth_identities')
            ->select('user_id')
            ->where('secret', $email)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();

        return $result;
    }

    /**
     * Get user email by user ID (from auth_identities)
     */
    public function getUserEmail(int $userId): ?string
    {
        $db = db_connect();
        $result = $db->table('auth_identities')
            ->select('secret')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->get()
            ->getRowArray();

        return $result['secret'] ?? null;
    }

    /**
     * Update user email in auth_identities
     */
    public function updateUserEmail(int $userId, string $email): bool
    {
        $db = db_connect();
        return $db->table('auth_identities')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->update(['secret' => $email]);
    }
}
