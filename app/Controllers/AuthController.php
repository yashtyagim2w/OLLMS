<?php

namespace App\Controllers;

use App\Traits\ResponseTrait;
use App\Models\UserProfileModel;
use App\Models\PasswordResetTokenModel;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Auth Controller
 * Handles authentication view rendering and password reset
 */
class AuthController extends BaseController
{
    use ResponseTrait;

    protected UserProfileModel $profileModel;
    protected PasswordResetTokenModel $resetTokenModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->profileModel = new UserProfileModel();
        $this->resetTokenModel = new PasswordResetTokenModel();
        $this->userModel = new UserModel();
    }

    /**
     * Show login page
     */
    public function login()
    {
        // If already logged in, redirect appropriately
        if (auth()->loggedIn()) {
            return $this->redirectBasedOnStatus();
        }

        return view('auth/login', [
            'pageTitle' => 'Login',
        ]);
    }

    /**
     * Show registration page
     */
    public function register()
    {
        // If already logged in, redirect appropriately
        if (auth()->loggedIn()) {
            return $this->redirectBasedOnStatus();
        }

        return view('auth/register', [
            'pageTitle' => 'Create Account'
        ]);
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        if (auth()->loggedIn()) {
            return $this->redirectBasedOnStatus();
        }

        return view('auth/forgot_password', [
            'pageTitle' => 'Forgot Password'
        ]);
    }

    /**
     * Process forgot password - send reset link
     */
    public function sendResetLink()
    {
        $email = $this->request->getPost('email');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->backWithError('Please enter a valid email address.');
        }

        // Check if user exists (but don't reveal this to prevent enumeration)
        $user = $this->userModel->findByCredentials(['email' => $email]);

        if ($user) {
            // Create reset token with user_id and email
            $plainToken = $this->resetTokenModel->createToken($user->id, $email);

            if ($plainToken) {
                // Send email with reset link
                $resetLink = site_url("reset-password/{$plainToken}");

                $emailService = \Config\Services::email();
                $emailService->setFrom(config('Email')->fromEmail, config('Email')->fromName ?? 'OLLMS');
                $emailService->setTo($email);
                $emailService->setSubject('Password Reset Request - OLLMS');
                $emailService->setMessage($this->getResetEmailBody($resetLink));

                $emailService->send();
            }
        }

        // Always show success message to prevent email enumeration
        return $this->redirectWithSuccess(
            '/login',
            'If your email is registered, you will receive a password reset link shortly.'
        );
    }

    /**
     * Show reset password form
     */
    public function showResetForm(string $token)
    {
        // Validate token
        $tokenData = $this->resetTokenModel->findValidToken($token);

        if (!$tokenData) {
            return $this->redirectWithError(
                '/forgot-password',
                'Invalid or expired reset link. Please request a new one.'
            );
        }

        return view('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'token'     => $token,
            'email'     => $tokenData['email'],
            'showForm'  => true,
        ]);
    }

    /**
     * Process password reset
     */
    public function resetPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        // Validate token
        $tokenData = $this->resetTokenModel->findValidToken($token);

        if (!$tokenData) {
            return $this->redirectWithError(
                '/forgot-password',
                'Invalid or expired reset link. Please request a new one.'
            );
        }

        // Validate passwords
        if (strlen($password) < 8) {
            return $this->backWithError('Password must be at least 8 characters long.');
        }

        if ($password !== $passwordConfirm) {
            return $this->backWithError('Passwords do not match.');
        }

        // Find user and update password
        $user = $this->userModel->findByCredentials(['email' => $tokenData['email']]);

        if (!$user) {
            return $this->redirectWithError(
                '/forgot-password',
                'User not found. Please request a new reset link.'
            );
        }

        // Update password using Shield's password hasher
        $user->password = $password;
        $this->userModel->save($user);

        // Invalidate the token
        $this->resetTokenModel->invalidateToken($token);

        return $this->redirectWithSuccess(
            '/login',
            'Password has been reset successfully. Please login with your new password.'
        );
    }

    /**
     * Redirect user based on their current status
     */
    protected function redirectBasedOnStatus()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->to('/login');
        }

        // Admin goes to admin dashboard
        if ($user->inGroup('admin')) {
            return redirect()->to('/admin/dashboard');
        }

        // Check email verification
        if (!$user->isActivated()) {
            return redirect()->to('/verify-otp');
        }

        // Check document status
        $documentModel = new \App\Models\UserDocumentModel();
        $document = $documentModel->getLatestDocument($user->id);

        if (!$document) {
            return redirect()->to('/identity-upload');
        }

        if ($document['status'] === 'PENDING') {
            return redirect()->to('/verification-status');
        }

        if ($document['status'] === 'REJECTED') {
            return redirect()->to('/identity-upload');
        }

        // All good - go to dashboard
        return redirect()->to('/dashboard');
    }

    /**
     * Generate reset email body
     */
    protected function getResetEmailBody(string $resetLink): string
    {
        return view('emails/password_reset', ['resetLink' => $resetLink]);
    }
}
