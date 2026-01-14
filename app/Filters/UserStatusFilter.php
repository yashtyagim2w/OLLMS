<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserProfileModel;
use App\Models\UserDocumentModel;

/**
 * UserStatusFilter
 * 
 * Enforces step-by-step access control for users:
 * 1. Profile verification_status must be COMPLETED (OTP verified)
 * 2. Document must be uploaded
 * 3. Document must be approved
 * 
 * Note: Admin access is blocked at route level via group:user filter
 */
class UserStatusFilter implements FilterInterface
{
    /**
     * Routes that are always accessible (no status checks)
     */
    protected array $bypassRoutes = [
        'verify-otp',
        'resend-otp',
        'identity-upload',
        'verification-status',
        'logout',
    ];

    /**
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        // No user logged in - session filter should handle this
        if (!$user) {
            return redirect()->to('/login');
        }

        // NOTE: Admin access is blocked at the route level via group:user filter

        // Get current URI path
        $currentPath = trim($request->getUri()->getPath(), '/');

        // Check if current route should bypass status checks
        foreach ($this->bypassRoutes as $route) {
            if (str_starts_with($currentPath, $route)) {
                return;
            }
        }

        // Step 1: Check if user profile exists and OTP is verified
        $profileModel = new UserProfileModel();
        $profile = $profileModel->getByUserId($user->id);

        // No profile = something went wrong, redirect to verify-otp
        if (!$profile) {
            return redirect()->to('/verify-otp')
                ->with('error', 'Please complete your registration.');
        }

        // If verification_status is PENDING, show OTP page
        if ($profile['verification_status'] === 'PENDING') {
            return redirect()->to('/verify-otp')
                ->with('message', 'Please verify your email to continue.');
        }

        // Step 2: Check if document is uploaded
        $documentModel = new UserDocumentModel();
        $document = $documentModel->getLatestDocument($user->id);

        if (!$document) {
            return redirect()->to('/identity-upload')
                ->with('message', 'Please upload your identity document.');
        }

        // Step 3: Check document status
        switch ($document['status']) {
            case 'PENDING':
                // Only allow verification-status page
                if ($currentPath !== 'verification-status') {
                    return redirect()->to('/verification-status')
                        ->with('message', 'Your documents are under review. Please wait for approval.');
                }
                break;

            case 'REJECTED':
                // Allow re-upload
                if ($currentPath !== 'identity-upload') {
                    return redirect()->to('/identity-upload')
                        ->with('error', 'Your document was rejected. Please upload again.');
                }
                break;

            case 'APPROVED':
                // All good - allow access
                break;

            default:
                // Unknown status - redirect to identity upload
                return redirect()->to('/identity-upload');
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the request
    }
}
