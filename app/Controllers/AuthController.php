<?php

namespace App\Controllers;

/**
 * Auth Controller
 * Handles authentication view rendering
 */
class AuthController extends BaseController
{
    /**
     * Show login page
     */
    public function login()
    {
        // Check if user needs OTP verification
        $needsOtpVerification = false; // This would be determined by backend logic

        return view('auth/login', [
            'pageTitle' => 'Login',
            'needsOtpVerification' => $needsOtpVerification
        ]);
    }

    /**
     * Show registration page
     */
    public function register()
    {
        return view('auth/register', [
            'pageTitle' => 'Create Account'
        ]);
    }

    /**
     * Show OTP verification page
     */
    public function verifyOtp()
    {
        // Get email from session or query param
        $email = session('pending_email') ?? 'your@email.com';

        return view('auth/otp_verify', [
            'pageTitle' => 'Verify Email',
            'email' => $email
        ]);
    }

    /**
     * Show reset password page
     */
    public function resetPassword()
    {
        $token = $this->request->getGet('token');

        return view('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'token' => $token
        ]);
    }
}
