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
}
