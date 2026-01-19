<?php

namespace App\Controllers\User;

use App\Traits\ResponseTrait;

class VerificationController extends BaseUserController
{
    use ResponseTrait;

    public function otp()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return redirect()->to('/identity-upload');
        }

        $otpSentAt = session('otp_sent_at') ?? 0;
        $cooldown = 60;
        $elapsed = time() - $otpSentAt;
        $remainingCooldown = max(0, $cooldown - $elapsed);

        return view('user/verify_otp', [
            'pageTitle'             => 'Verify Email',
            'email'                 => $user->email ?? 'your@email.com',
            'isVerified'            => $profile && $profile['verification_status'] === 'COMPLETED',
            'isPendingVerification' => true,
            'needsOtp'              => !session('otp_sent'),
            'remainingCooldown'     => $remainingCooldown,
        ]);
    }

    public function processOtp()
    {
        $user = $this->user();
        $inputOtp = $this->request->getPost('otp');
        if (is_array($inputOtp)) {
            $inputOtp = implode('', $inputOtp);
        }

        if (strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
            return $this->backWithError('Please enter a valid 6-digit code.');
        }

        $storedOtp = session('email_otp');
        $otpExpiry = session('otp_expiry');

        if (!$storedOtp || !$otpExpiry) {
            return $this->backWithError('No verification code found. Please request a new one.');
        }

        if (time() > $otpExpiry) {
            session()->remove(['email_otp', 'otp_expiry', 'otp_sent']);
            return $this->backWithError('Verification code has expired. Please request a new one.');
        }

        if ($inputOtp !== $storedOtp) {
            return $this->backWithError('Invalid verification code. Please try again.');
        }

        $this->profileModel->updateVerificationStatus($user->id, 'COMPLETED');
        session()->remove(['email_otp', 'otp_expiry', 'otp_sent']);

        return $this->redirectWithSuccess('/identity-upload', 'Email verified successfully! Please upload your identity document.');
    }

    public function apiVerify()
    {
        try {
            $user = $this->user();
            $inputOtp = $this->request->getPost('otp');

            if (!$inputOtp) {
                $json = $this->request->getJSON(true);
                $inputOtp = $json['otp'] ?? '';
            }

            if (is_array($inputOtp)) {
                $inputOtp = implode('', $inputOtp);
            }

            if (strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
                return $this->jsonError('Please enter a valid 6-digit code.');
            }

            $storedOtp = session('email_otp');
            $otpExpiry = session('otp_expiry');

            if (!$storedOtp || !$otpExpiry) {
                return $this->jsonError('No verification code found. Please request a new one.');
            }

            if (time() > $otpExpiry) {
                session()->remove(['email_otp', 'otp_expiry', 'otp_sent', 'otp_sent_at']);
                return $this->jsonError('Verification code has expired. Please request a new one.');
            }

            if ($inputOtp !== $storedOtp) {
                return $this->jsonError('Invalid verification code. Please try again.');
            }

            $this->profileModel->updateVerificationStatus($user->id, 'COMPLETED');
            session()->remove(['email_otp', 'otp_expiry', 'otp_sent', 'otp_sent_at']);

            $profile = $this->getProfile();
            $this->sendEmailVerifiedNotification($user->email, $profile['first_name'] ?? 'User');

            return $this->jsonSuccess('Email verified successfully!', ['redirect' => '/identity-upload']);
        } catch (\Throwable $e) {
            log_message('error', 'OTP Verification Error: ' . $e->getMessage());
            return $this->jsonError('An error occurred. Please try again.');
        }
    }

    public function apiSend()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->jsonSuccess('Email already verified.', ['redirect' => '/identity-upload']);
        }

        if (session('otp_sent')) {
            $otpSentAt = session('otp_sent_at') ?? 0;
            $cooldown = 60;
            $elapsed = time() - $otpSentAt;
            $remaining = max(0, $cooldown - $elapsed);

            return $this->jsonSuccess('Verification code already sent.', [
                'alreadySent' => true,
                'cooldown' => $remaining
            ]);
        }

        $this->generateAndSendOtp($user->email);

        return $this->jsonSuccess('Verification code sent to your email.', ['cooldown' => 60]);
    }

    public function apiResend()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->jsonSuccess('Email already verified.', ['redirect' => '/identity-upload']);
        }

        $lastSentAt = session('otp_sent_at') ?? 0;
        $cooldown = 60;
        $elapsed = time() - $lastSentAt;

        if ($elapsed < $cooldown) {
            $remaining = $cooldown - $elapsed;
            return $this->jsonError("Please wait {$remaining} seconds before requesting a new code.", ['cooldown' => $remaining]);
        }

        $this->generateAndSendOtp($user->email);

        return $this->jsonSuccess('A new verification code has been sent to your email.', ['cooldown' => $cooldown]);
    }

    public function resend()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->redirectWithSuccess('/identity-upload', 'Email already verified.');
        }

        $this->generateAndSendOtp($user->email);

        return $this->backWithSuccess('A new verification code has been sent to your email.');
    }

    protected function generateAndSendOtp(string $email): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session()->set([
            'email_otp'   => $otp,
            'otp_expiry'  => time() + 600,
            'otp_sent'    => true,
            'otp_sent_at' => time(),
        ]);

        $emailService = \Config\Services::email();
        $emailService->setFrom(config('Email')->fromEmail, config('Email')->fromName ?? 'OLLMS');
        $emailService->setTo($email);
        $emailService->setSubject('Email Verification Code - OLLMS');
        $emailService->setMessage($this->getOtpEmailBody($otp));
        $emailService->send();
    }

    protected function getOtpEmailBody(string $otp): string
    {
        return view('emails/otp_verification', ['otp' => $otp]);
    }

    protected function sendEmailVerifiedNotification(string $email, string $firstName): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($email);
            $emailService->setSubject('Email Verified Successfully - OLLMS');
            $emailService->setMessage(view('emails/email_verified', ['firstName' => $firstName]));
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Failed to send email verified notification: ' . $e->getMessage());
        }
    }
}
