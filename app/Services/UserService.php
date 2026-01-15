<?php

namespace App\Services;

use App\Models\UserProfileModel;
use App\Models\UserDocumentModel;
use App\Models\VideoProgressModel;
use App\Models\TestModel;
use App\Models\CertificateModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

/**
 * User Service
 * 
 * Handles all user management operations for admin panel
 */
class UserService
{
    protected UserModel $userModel;
    protected UserProfileModel $profileModel;
    protected UserDocumentModel $documentModel;
    protected VideoProgressModel $videoProgressModel;
    protected TestModel $testModel;
    protected CertificateModel $certificateModel;

    public function __construct()
    {
        $this->userModel = auth()->getProvider();
        $this->profileModel = new UserProfileModel();
        $this->documentModel = new UserDocumentModel();
        $this->videoProgressModel = new VideoProgressModel();
        $this->testModel = new TestModel();
        $this->certificateModel = new CertificateModel();
    }

    /**
     * Get paginated list of users with filters
     * Uses profileModel for data, service handles formatting
     */
    public function getUsers(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $result = $this->profileModel->getUsersForAdmin($filters, $page, $limit);

        // Format response
        $formattedUsers = array_map(function ($user) {
            // Determine document status
            $docStatus = 'NOT_UPLOADED';
            if (!empty($user['doc_status'])) {
                $docStatus = $user['doc_status'];
            }

            return [
                'id' => $user['id'],
                'first_name' => $user['first_name'] ?? 'N/A',
                'last_name' => $user['last_name'] ?? '',
                'email' => $user['email'] ?? '',
                'dob' => $user['dob'] ?? '',
                'aadhar_number' => $user['aadhar_number'] ?? '',
                'docStatus' => $docStatus,
                'verificationStatus' => $user['verification_status'] ?? 'PENDING',
                'active' => (bool) $user['active'],
                // Placeholders for future
                'videoProgress' => 0,
                'testResult' => null,
                'hasCert' => false,
            ];
        }, $result['data']);

        return [
            'data' => $formattedUsers,
            'pagination' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'totalPages' => ceil($result['total'] / $result['limit']),
                'totalRecords' => $result['total'],
            ],
        ];
    }

    /**
     * Get dashboard statistics using model methods
     */
    public function getDashboardStats(): array
    {
        return [
            'totalUsers' => $this->profileModel->getTotalCount(),
            'pendingVerifications' => $this->documentModel->countPending(),
            'approvedToday' => $this->documentModel->getApprovedToday(),
            'newRegistrations' => $this->profileModel->getRegistrationsToday(),
            'certificatesIssued' => $this->certificateModel->getTotalCount(),
            'testsTaken' => $this->testModel->getTotalCount(),
            'passRate' => $this->testModel->getPassRate(),
            'videosWatched' => $this->videoProgressModel->countAllCompleted(),
        ];
    }

    /**
     * Get single user by ID with all details
     */
    public function getUserById(int $id): ?array
    {
        return $this->profileModel->getUserDetailsForAdmin($id);
    }

    /**
     * Update user profile and email
     * Business logic only - uses model methods for data operations
     */
    public function updateUser(int $id, array $data): array
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $db = db_connect();
        $db->transStart();

        try {
            helper('validation');

            // Check for empty required fields
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['dob'])) {
                return ['success' => false, 'message' => 'First Name, Last Name, Email, and Date of Birth are required fields.'];
            }

            // Get current user data for comparison
            $currentProfile = $this->profileModel->getByUserId($id);
            $currentEmail = $this->profileModel->getUserEmail($id);
            $latestDoc = $this->documentModel->getLatestDocument($id);
            $currentVerificationStatus = $currentProfile['verification_status'] ?? 'PENDING';

            // Check if anything has changed
            $hasFirstNameChanged = $data['first_name'] !== ($currentProfile['first_name'] ?? '');
            $hasLastNameChanged = $data['last_name'] !== ($currentProfile['last_name'] ?? '');
            $hasEmailChanged = $data['email'] !== ($currentEmail ?? '');
            $hasDobChanged = $data['dob'] !== ($currentProfile['dob'] ?? '');
            $hasAadharChanged = !empty($data['aadhar_number']) && $data['aadhar_number'] !== ($latestDoc['aadhar_number'] ?? '');
            $hasVerificationStatusChanged = isset($data['verification_status']) && $data['verification_status'] !== $currentVerificationStatus;
            $hasDocStatusChanged = !empty($data['doc_status']) && $data['doc_status'] !== ($latestDoc['status'] ?? '');

            if (!$hasFirstNameChanged && !$hasLastNameChanged && !$hasEmailChanged && !$hasDobChanged && !$hasAadharChanged && !$hasVerificationStatusChanged && !$hasDocStatusChanged) {
                return ['success' => false, 'message' => 'No changes detected. Please modify at least one field to update.'];
            }
            // Update email if changed
            if (!empty($data['email'])) {
                // Validate email format
                if (!is_valid_email($data['email'])) {
                    return ['success' => false, 'message' => get_validation_message('email')];
                }
                $this->profileModel->updateUserEmail($id, $data['email']);
            }

            // Validate required name fields
            if (isset($data['first_name']) && !is_valid_name($data['first_name'])) {
                return ['success' => false, 'message' => get_validation_message('name')];
            }
            if (isset($data['last_name']) && !is_valid_name($data['last_name'])) {
                return ['success' => false, 'message' => get_validation_message('name')];
            }

            // Update profile
            $profileData = [];
            if (isset($data['first_name'])) $profileData['first_name'] = $data['first_name'];
            if (isset($data['last_name'])) $profileData['last_name'] = $data['last_name'];

            // Validate DOB if provided - age must be between 18 and 120 years
            if (!empty($data['dob'])) {
                if (!is_valid_dob($data['dob'])) {
                    return ['success' => false, 'message' => get_validation_message('dob')];
                }
                $profileData['dob'] = $data['dob'];
            }

            if (!empty($profileData)) {
                $this->profileModel->updateByUserId($id, $profileData);
            }

            // Validate Aadhaar format if provided
            if (!empty($data['aadhar_number'])) {
                if (!is_valid_aadhaar($data['aadhar_number'])) {
                    return ['success' => false, 'message' => get_validation_message('aadhaar')];
                }
                $latestDoc = $this->documentModel->getLatestDocument($id);
                if ($latestDoc) {
                    $this->documentModel->updateAadharNumber($latestDoc['id'], $data['aadhar_number']);
                }
            }

            // Update email verification status
            if (isset($data['verification_status'])) {
                $this->profileModel->updateVerificationStatus($id, $data['verification_status']);
            }

            // Update document status
            if (!empty($data['doc_status'])) {
                $latestDoc = $this->documentModel->getLatestDocument($id);
                if ($latestDoc) {
                    $this->documentModel->updateStatus($latestDoc['id'], $data['doc_status']);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['success' => false, 'message' => 'Database error'];
            }

            // Send notification email
            $this->sendProfileUpdatedEmail($id);

            return ['success' => true, 'message' => 'User updated successfully'];
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'UserService::updateUser error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }

    /**
     * Ban (soft delete) a user
     */
    public function banUser(int $id): array
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user->ban('Account deactivated by admin');
        $this->userModel->save($user);

        // Send notification email
        $this->sendAccountDeactivatedEmail($id);

        return ['success' => true, 'message' => 'User account deactivated'];
    }

    /**
     * Activate (restore) a user
     */
    public function activateUser(int $id): array
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user->unBan();
        $this->userModel->save($user);

        // Send notification email
        $this->sendAccountReactivatedEmail($id);

        return ['success' => true, 'message' => 'User account reactivated'];
    }

    /**
     * Set user password directly (admin action)
     */
    public function setPassword(int $id, string $password): array
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        $user->setPassword($password);
        $this->userModel->save($user);

        // Send notification email with new password
        $this->sendPasswordResetEmail($id, $password);

        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    /**
     * Toggle email verification status
     */
    public function toggleEmailVerification(int $id): array
    {
        $profile = $this->profileModel->getByUserId($id);
        if (!$profile) {
            return ['success' => false, 'message' => 'User profile not found'];
        }

        $newStatus = $profile['verification_status'] === 'COMPLETED' ? 'PENDING' : 'COMPLETED';
        $this->profileModel->updateVerificationStatus($id, $newStatus);

        return [
            'success' => true,
            'message' => 'Verification status updated to ' . $newStatus,
            'newStatus' => $newStatus,
        ];
    }

    /**
     * Mask Aadhar number for display (XXXX-XXXX-1234)
     */
    private function maskAadhar(?string $aadhar): string
    {
        if (empty($aadhar) || strlen($aadhar) < 4) {
            return 'N/A';
        }
        return 'XXXX-XXXX-' . substr($aadhar, -4);
    }

    /**
     * Get user email by ID
     */
    private function getUserEmail(int $id): ?string
    {
        return $this->profileModel->getUserEmail($id);
    }

    /**
     * Get user name by ID
     */
    private function getUserName(int $id): string
    {
        return $this->profileModel->getFullName($id);
    }

    /**
     * Send profile updated notification email
     */
    private function sendProfileUpdatedEmail(int $userId): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) return;

        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Your Profile Has Been Updated - OLLMS');
        $emailService->setMessage(view('emails/admin_profile_updated', [
            'name' => $name,
        ]));
        $emailService->send();
    }

    /**
     * Send password reset notification email
     */
    private function sendPasswordResetEmail(int $userId, string $newPassword): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) return;

        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Your Password Has Been Reset - OLLMS');
        $emailService->setMessage(view('emails/admin_password_reset', [
            'name' => $name,
            'password' => $newPassword,
        ]));
        $emailService->send();
    }

    /**
     * Send account deactivated notification email
     */
    private function sendAccountDeactivatedEmail(int $userId): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) return;

        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Account Deactivated - OLLMS');
        $emailService->setMessage(view('emails/account_deactivated', [
            'name' => $name,
        ]));
        $emailService->send();
    }

    /**
     * Send account reactivated notification email
     */
    private function sendAccountReactivatedEmail(int $userId): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) return;

        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Account Reactivated - OLLMS');
        $emailService->setMessage(view('emails/account_reactivated', [
            'name' => $name,
        ]));
        $emailService->send();
    }
}
