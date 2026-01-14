<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Password Reset Token Model
 * 
 * Manages custom password reset tokens
 * Tokens are hashed (SHA256) before storage for security
 * Stores both user_id and email for data integrity
 */
class PasswordResetTokenModel extends Model
{
    protected $table            = 'password_reset_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'email',
        'token',
        'expires_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at for tokens

    /**
     * Token expiry time in seconds (1 hour)
     */
    protected const TOKEN_EXPIRY = 3600;

    /**
     * Create a new password reset token
     * 
     * @param int $userId User's ID
     * @param string $email User's email (for display in emails)
     * @return string|false The plain token to send via email, or false on failure
     */
    public function createToken(int $userId, string $email): string|false
    {
        // Invalidate any existing tokens for this user
        $this->invalidateTokensForUser($userId);

        // Generate secure random token
        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);

        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);

        $result = $this->insert([
            'user_id'    => $userId,
            'email'      => $email,
            'token'      => $hashedToken,
            'expires_at' => $expiresAt,
        ]);

        return $result ? $plainToken : false;
    }

    /**
     * Find a valid (non-expired) token
     * 
     * @param string $plainToken The plain token from the URL
     * @return array|null Token record if valid, null otherwise
     */
    public function findValidToken(string $plainToken): ?array
    {
        $hashedToken = hash('sha256', $plainToken);

        return $this->where('token', $hashedToken)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Invalidate a specific token (after use)
     */
    public function invalidateToken(string $plainToken): bool
    {
        $hashedToken = hash('sha256', $plainToken);
        return $this->where('token', $hashedToken)->delete();
    }

    /**
     * Invalidate all tokens for a user
     */
    public function invalidateTokensForUser(int $userId): bool
    {
        return $this->where('user_id', $userId)->delete();
    }

    /**
     * Invalidate all tokens for an email (legacy support)
     */
    public function invalidateTokensForEmail(string $email): bool
    {
        return $this->where('email', $email)->delete();
    }

    /**
     * Clean up expired tokens
     * Should be run periodically (cron job or on-demand)
     */
    public function cleanExpired(): int
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
        return $this->db->affectedRows();
    }

    /**
     * Check if a user has a valid (non-expired) token
     */
    public function hasValidToken(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->countAllResults() > 0;
    }
}
