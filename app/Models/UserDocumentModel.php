<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Document Model
 * 
 * Manages identity documents (Aadhaar upload, verification status)
 * One active document per user at a time
 */
class UserDocumentModel extends Model
{
    protected $table            = 'user_documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'aadhar_number',
        'document_url',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id'       => 'required|integer',
        'aadhar_number' => 'required|exact_length[12]|numeric',
        'document_url'  => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'aadhar_number' => [
            'exact_length' => 'Aadhaar number must be exactly 12 digits.',
            'numeric'      => 'Aadhaar number must contain only digits.',
        ],
    ];

    /**
     * Get the latest (active) document for a user
     * Returns the most recent document regardless of status
     */
    public function getLatestDocument(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Get active (non-rejected) document for a user
     */
    public function getActiveDocument(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Check if user has any document uploaded
     */
    public function hasDocument(int $userId): bool
    {
        return $this->where('user_id', $userId)->countAllResults() > 0;
    }

    /**
     * Check if user has an approved document
     */
    public function hasApprovedDocument(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('status', 'APPROVED')
            ->countAllResults() > 0;
    }

    /**
     * Get document status for a user
     * Returns: null (no document), PENDING, APPROVED, or REJECTED
     */
    public function getDocumentStatus(int $userId): ?string
    {
        $doc = $this->getLatestDocument($userId);
        return $doc ? $doc['status'] : null;
    }

    /**
     * Create a new document record for user
     * On rejection, this creates a new record (old one remains as REJECTED)
     */
    public function createDocument(int $userId, string $aadharNumber, string $documentUrl): int|false
    {
        return $this->insert([
            'user_id'       => $userId,
            'aadhar_number' => $aadharNumber,
            'document_url'  => $documentUrl,
            'status'        => 'PENDING',
        ]);
    }

    /**
     * Approve a document
     */
    public function approveDocument(int $documentId, int $reviewerId, ?string $remarks = null): bool
    {
        return $this->update($documentId, [
            'status'      => 'APPROVED',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'remarks'     => $remarks,
        ]);
    }

    /**
     * Reject a document
     */
    public function rejectDocument(int $documentId, int $reviewerId, string $remarks): bool
    {
        return $this->update($documentId, [
            'status'      => 'REJECTED',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'remarks'     => $remarks,
        ]);
    }

    /**
     * Get masked Aadhaar number (XXXX-XXXX-1234)
     */
    public function getMaskedAadhaar(int $userId): ?string
    {
        $doc = $this->getLatestDocument($userId);
        if (!$doc || empty($doc['aadhar_number'])) {
            return null;
        }

        $last4 = substr($doc['aadhar_number'], -4);
        return 'XXXX-XXXX-' . $last4;
    }

    /**
     * Get pending documents for admin review
     */
    public function getPendingDocuments(int $limit = 50, int $offset = 0): array
    {
        return $this->where('status', 'PENDING')
            ->orderBy('created_at', 'ASC')
            ->findAll($limit, $offset);
    }

    /**
     * Count pending documents
     */
    public function countPending(): int
    {
        return $this->where('status', 'PENDING')->countAllResults();
    }

    /**
     * Check if Aadhaar number is already in use by another user
     * Only checks PENDING or APPROVED documents
     * 
     * @param string $aadharNumber The Aadhaar number to check
     * @param int $excludeUserId The current user to exclude from check
     * @return bool True if Aadhaar is already in use by another user
     */
    public function isAadhaarInUse(string $aadharNumber, int $excludeUserId): bool
    {
        return $this->where('aadhar_number', $aadharNumber)
            ->where('user_id !=', $excludeUserId)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->countAllResults() > 0;
    }

    /**
     * Get count of documents approved on a specific date
     */
    public function getApprovedOnDate(string $date): int
    {
        return $this->where('status', 'APPROVED')
            ->where('DATE(reviewed_at)', $date)
            ->countAllResults();
    }

    /**
     * Get count of documents approved today
     */
    public function getApprovedToday(): int
    {
        return $this->getApprovedOnDate(date('Y-m-d'));
    }

    /**
     * Update Aadhaar number for a document
     */
    public function updateAadharNumber(int $documentId, string $aadharNumber): bool
    {
        return $this->update($documentId, ['aadhar_number' => $aadharNumber]);
    }

    /**
     * Update document status directly
     */
    public function updateStatus(int $documentId, string $status): bool
    {
        $validStatuses = ['PENDING', 'APPROVED', 'REJECTED'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        return $this->update($documentId, [
            'status' => $status,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get paginated documents with user details for admin review
     * 
     * @param array $filters ['status' => string, 'search' => string, 'sort_by' => string, 'sort_order' => string]
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array ['data' => array, 'total' => int, 'page' => int, 'limit' => int]
     */
    public function getDocumentsWithUserDetails(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $db = db_connect();
        $builder = $db->table('user_documents d');

        $builder->select('
            d.id,
            d.user_id,
            d.aadhar_number,
            d.document_url,
            d.status,
            d.remarks,
            d.reviewed_by,
            d.reviewed_at,
            d.created_at as submitted_at,
            p.first_name,
            p.last_name,
            auth.secret as email
        ');

        $builder->join('user_profiles p', 'p.user_id = d.user_id', 'left');
        $builder->join('auth_identities auth', 'auth.user_id = d.user_id AND auth.type = "email_password"', 'left');

        // Filter by status
        if (!empty($filters['status'])) {
            $builder->where('d.status', $filters['status']);
        }

        // Search by name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('p.first_name', $search)
                ->orLike('p.last_name', $search)
                ->orLike('auth.secret', $search)
                ->orLike('d.aadhar_number', $search)
                ->groupEnd();
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'submitted_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';

        $validSortFields = ['submitted_at', 'name', 'status'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'submitted_at';
        }

        if ($sortBy === 'name') {
            $builder->orderBy('p.first_name', $sortOrder);
        } else if ($sortBy === 'submitted_at') {
            $builder->orderBy('d.created_at', $sortOrder);
        } else {
            $builder->orderBy('d.' . $sortBy, $sortOrder);
        }

        // Pagination
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        $data = $builder->get()->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * Get single document with user details for admin review
     * 
     * @param int $documentId Document ID
     * @return array|null Document data with user details or null if not found
     */
    public function getDocumentWithUserDetails(int $documentId): ?array
    {
        $db = db_connect();
        return $db->table('user_documents d')
            ->select('
                d.id,
                d.user_id,
                d.aadhar_number,
                d.document_url,
                d.status,
                d.remarks,
                d.reviewed_by,
                d.reviewed_at,
                d.created_at as submitted_at,
                p.first_name,
                p.last_name,
                p.dob,
                auth.secret as email
            ')
            ->join('user_profiles p', 'p.user_id = d.user_id', 'left')
            ->join('auth_identities auth', 'auth.user_id = d.user_id AND auth.type = "email_password"', 'left')
            ->where('d.id', $documentId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get document by Aadhaar number
     * Used to check if Aadhaar is already registered
     * 
     * @param string $aadharNumber Aadhaar number to search
     * @return array|null Document data or null if not found
     */
    public function getDocumentByAadhar(string $aadharNumber): ?array
    {
        return $this->where('aadhar_number', $aadharNumber)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->first();
    }
}
