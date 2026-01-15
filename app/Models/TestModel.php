<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Test Model
 * 
 * Manages user test attempts and results
 */
class TestModel extends Model
{
    protected $table            = 'tests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'total_questions',
        'score',
        'result',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get latest test for a user
     */
    public function getLatestTest(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Get all test attempts for a user
     */
    public function getUserTests(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Check if user has passed any test
     */
    public function hasPassedTest(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('result', 'PASS')
            ->countAllResults() > 0;
    }

    /**
     * Get total count of all tests taken
     */
    public function getTotalCount(): int
    {
        return $this->countAllResults();
    }

    /**
     * Get count of passed tests
     */
    public function getPassedCount(): int
    {
        return $this->where('result', 'PASS')->countAllResults();
    }

    /**
     * Calculate pass rate percentage
     */
    public function getPassRate(): int
    {
        $total = $this->getTotalCount();
        if ($total === 0) {
            return 0;
        }
        return (int) round(($this->getPassedCount() / $total) * 100);
    }

    /**
     * Create a new test record
     */
    public function createTest(int $userId, int $totalQuestions, int $score, string $result): int|false
    {
        return $this->insert([
            'user_id' => $userId,
            'total_questions' => $totalQuestions,
            'score' => $score,
            'result' => $result,
        ]);
    }
}
