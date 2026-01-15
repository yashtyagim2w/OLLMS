<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Certificate Model
 * 
 * Manages learner's license certificates
 */
class CertificateModel extends Model
{
    protected $table            = 'certificates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'test_id',
        'certificate_number',
        'issued_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get certificate for a user
     */
    public function getUserCertificate(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Check if user has a certificate
     */
    public function hasCertificate(int $userId): bool
    {
        return $this->where('user_id', $userId)->countAllResults() > 0;
    }

    /**
     * Get total count of issued certificates
     */
    public function getTotalCount(): int
    {
        return $this->countAllResults();
    }

    /**
     * Issue a certificate
     */
    public function issueCertificate(int $userId, int $testId): int|false
    {
        // Generate unique certificate number
        $certificateNumber = 'OLLMS-' . date('Y') . '-' . str_pad($userId, 6, '0', STR_PAD_LEFT);

        return $this->insert([
            'user_id' => $userId,
            'test_id' => $testId,
            'certificate_number' => $certificateNumber,
            'issued_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
