<?php

namespace App\Controllers;

use App\Traits\ResponseTrait;
use App\Models\UserProfileModel;
use App\Models\UserDocumentModel;

/**
 * User Controller
 * Handles user-facing view rendering with real data from models
 */
class UserController extends BaseController
{
    use ResponseTrait;

    protected UserProfileModel $profileModel;
    protected UserDocumentModel $documentModel;

    public function __construct()
    {
        $this->profileModel = new UserProfileModel();
        $this->documentModel = new UserDocumentModel();
    }

    /**
     * Get current user helper
     */
    protected function user()
    {
        return auth()->user();
    }

    /**
     * Get current user's profile
     */
    protected function getProfile(): ?array
    {
        $user = $this->user();
        return $user ? $this->profileModel->getByUserId($user->id) : null;
    }

    /**
     * Get current user's document
     */
    protected function getDocument(): ?array
    {
        $user = $this->user();
        return $user ? $this->documentModel->getLatestDocument($user->id) : null;
    }

    /**
     * User Dashboard
     */
    public function dashboard()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        // Calculate video progress (placeholder - will be implemented in video module)
        $videosCompleted = 0;
        $totalVideos = 10;
        $progressPercent = $totalVideos > 0 ? round(($videosCompleted / $totalVideos) * 100) : 0;

        // Calculate display name and initial for header
        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

        // Check if user is fully verified (email + documents approved)
        $isFullyVerified = ($profile['verification_status'] ?? 'PENDING') === 'COMPLETED'
            && ($document['status'] ?? 'NOT_UPLOADED') === 'APPROVED';

        return view('user/dashboard', [
            'pageTitle'                 => 'Dashboard',
            'userName'                  => $profile ? $profile['first_name'] : 'User',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus'            => $document['status'] ?? 'NOT_UPLOADED',
            'isPendingVerification'     => ($profile['verification_status'] ?? 'PENDING') === 'PENDING',
            'isFullyVerified'           => $isFullyVerified,
            'displayName'               => $displayName,
            'displayInitial'            => $displayInitial,
            'videosCompleted'           => $videosCompleted,
            'totalVideos'               => $totalVideos,
            'progressPercent'           => $progressPercent,
            'testAttempts'              => 0,
            'testResult'                => 'NONE',
        ]);
    }

    /**
     * User Profile
     */
    public function profile()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        // Calculate display name and initial for header
        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

        // Check if user is fully verified (email + documents approved)
        $isFullyVerified = ($profile['verification_status'] ?? 'PENDING') === 'COMPLETED'
            && ($document['status'] ?? 'NOT_UPLOADED') === 'APPROVED';

        // Format DOB as dd/mm/yyyy
        $dob = $profile['dob'] ?? '';
        $dobFormatted = $dob ? date('d/m/Y', strtotime($dob)) : '';

        return view('user/profile', [
            'pageTitle'                 => 'My Profile',
            'firstName'                 => $profile['first_name'] ?? '',
            'lastName'                  => $profile['last_name'] ?? '',
            'email'                     => $user->email,
            'dob'                       => $dob,
            'dobFormatted'              => $dobFormatted,
            'aadharMasked'              => $this->documentModel->getMaskedAadhaar($user->id),
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus'            => $document['status'] ?? 'NOT_UPLOADED',
            'isPendingVerification'     => ($profile['verification_status'] ?? 'PENDING') === 'PENDING',
            'isFullyVerified'           => $isFullyVerified,
            'displayName'               => $displayName,
            'displayInitial'            => $displayInitial,
            'createdAt'                 => $user->created_at?->format('Y-m-d') ?? '',
        ]);
    }

    /**
     * Email OTP Verification Page
     * Shows when user_profiles.verification_status = PENDING
     */
    public function verifyOtp()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        // If already verified, redirect to next step
        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return redirect()->to('/identity-upload');
        }

        // Calculate remaining cooldown time
        $otpSentAt = session('otp_sent_at') ?? 0;
        $cooldown = 60;
        $elapsed = time() - $otpSentAt;
        $remainingCooldown = max(0, $cooldown - $elapsed);

        return view('user/verify_otp', [
            'pageTitle'             => 'Verify Email',
            'email'                 => $user->email ?? 'your@email.com',
            'isVerified'            => $profile && $profile['verification_status'] === 'COMPLETED',
            'isPendingVerification' => true, // Always true on OTP page - hide dropdown, show only logout
            'needsOtp'              => !session('otp_sent'),
            'remainingCooldown'     => $remainingCooldown,
        ]);
    }

    /**
     * Process OTP verification
     * Validates OTP and updates user_profiles.verification_status to COMPLETED
     */
    public function processOtp()
    {
        $user = $this->user();

        // Get the OTP from input
        $inputOtp = $this->request->getPost('otp');
        if (is_array($inputOtp)) {
            $inputOtp = implode('', $inputOtp);
        }

        // Validate OTP length
        if (strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
            return $this->backWithError('Please enter a valid 6-digit code.');
        }

        // Get stored OTP from session
        $storedOtp = session('email_otp');
        $otpExpiry = session('otp_expiry');

        // Check if OTP exists and is not expired
        if (!$storedOtp || !$otpExpiry) {
            return $this->backWithError('No verification code found. Please request a new one.');
        }

        if (time() > $otpExpiry) {
            session()->remove(['email_otp', 'otp_expiry', 'otp_sent']);
            return $this->backWithError('Verification code has expired. Please request a new one.');
        }

        // Verify OTP
        if ($inputOtp !== $storedOtp) {
            return $this->backWithError('Invalid verification code. Please try again.');
        }

        // OTP is valid - update profile status
        $this->profileModel->updateVerificationStatus($user->id, 'COMPLETED');

        // Clear OTP from session
        session()->remove(['email_otp', 'otp_expiry', 'otp_sent']);

        return $this->redirectWithSuccess('/identity-upload', 'Email verified successfully! Please upload your identity document.');
    }

    /**
     * API: Verify OTP (JSON response)
     */
    public function apiVerifyOtp()
    {
        try {
            $user = $this->user();

            // Get the OTP from input (try POST first, then JSON body)
            $inputOtp = $this->request->getPost('otp');

            if (!$inputOtp) {
                $json = $this->request->getJSON(true);
                $inputOtp = $json['otp'] ?? '';
            }

            if (is_array($inputOtp)) {
                $inputOtp = implode('', $inputOtp);
            }

            // Validate OTP length
            if (strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
                return $this->jsonError('Please enter a valid 6-digit code.');
            }

            // Get stored OTP from session
            $storedOtp = session('email_otp');
            $otpExpiry = session('otp_expiry');

            // Check if OTP exists and is not expired
            if (!$storedOtp || !$otpExpiry) {
                return $this->jsonError('No verification code found. Please request a new one.');
            }

            if (time() > $otpExpiry) {
                session()->remove(['email_otp', 'otp_expiry', 'otp_sent', 'otp_sent_at']);
                return $this->jsonError('Verification code has expired. Please request a new one.');
            }

            // Verify OTP
            if ($inputOtp !== $storedOtp) {
                return $this->jsonError('Invalid verification code. Please try again.');
            }

            // OTP is valid - update profile status
            $this->profileModel->updateVerificationStatus($user->id, 'COMPLETED');

            // Clear OTP from session
            session()->remove(['email_otp', 'otp_expiry', 'otp_sent', 'otp_sent_at']);

            // Send email verification success notification
            $profile = $this->getProfile();
            $this->sendEmailVerifiedNotification($user->email, $profile['first_name'] ?? 'User');

            return $this->jsonSuccess('Email verified successfully!', [
                'redirect' => '/identity-upload'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'OTP Verification Error: ' . $e->getMessage());
            return $this->jsonError('An error occurred. Please try again.');
        }
    }

    /**
     * API: Send initial OTP (JSON response) - called on page load
     */
    public function apiSendOtp()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->jsonSuccess('Email already verified.', [
                'redirect' => '/identity-upload'
            ]);
        }

        // If OTP already sent recently, return remaining cooldown
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

        // Generate and send OTP
        $this->generateAndSendOtp($user->email);

        return $this->jsonSuccess('Verification code sent to your email.', [
            'cooldown' => 60
        ]);
    }

    /**
     * API: Resend OTP (JSON response)
     */
    public function apiResendOtp()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->jsonSuccess('Email already verified.', [
                'redirect' => '/identity-upload'
            ]);
        }

        // Check cooldown (60 seconds between resends)
        $lastSentAt = session('otp_sent_at') ?? 0;
        $cooldown = 60;
        $elapsed = time() - $lastSentAt;

        if ($elapsed < $cooldown) {
            $remaining = $cooldown - $elapsed;
            return $this->jsonError("Please wait {$remaining} seconds before requesting a new code.", [
                'cooldown' => $remaining
            ]);
        }

        // Generate and send new OTP
        $this->generateAndSendOtp($user->email);

        return $this->jsonSuccess('A new verification code has been sent to your email.', [
            'cooldown' => $cooldown
        ]);
    }

    /**
     * Resend OTP (form submission fallback)
     */
    public function resendOtp()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if ($profile && $profile['verification_status'] === 'COMPLETED') {
            return $this->redirectWithSuccess('/identity-upload', 'Email already verified.');
        }

        // Generate and send new OTP
        $this->generateAndSendOtp($user->email);

        return $this->backWithSuccess('A new verification code has been sent to your email.');
    }

    /**
     * Generate OTP and send via email
     */
    protected function generateAndSendOtp(string $email): void
    {
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in session (expires in 10 minutes)
        session()->set([
            'email_otp'   => $otp,
            'otp_expiry'  => time() + 600, // 10 minutes
            'otp_sent'    => true,
            'otp_sent_at' => time(), // For cooldown tracking
        ]);

        // Send email
        $emailService = \Config\Services::email();
        $emailService->setFrom(config('Email')->fromEmail, config('Email')->fromName ?? 'OLLMS');
        $emailService->setTo($email);
        $emailService->setSubject('Email Verification Code - OLLMS');
        $emailService->setMessage($this->getOtpEmailBody($otp));
        $emailService->send();
    }

    /**
     * Generate OTP email body
     */
    protected function getOtpEmailBody(string $otp): string
    {
        return view('emails/otp_verification', ['otp' => $otp]);
    }

    /**
     * Send email verified notification
     */
    protected function sendEmailVerifiedNotification(string $email, string $firstName): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($email);
            $emailService->setSubject('Email Verified Successfully - OLLMS');
            $emailService->setMessage(view('emails/email_verified', [
                'firstName' => $firstName,
            ]));

            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Failed to send email verified notification: ' . $e->getMessage());
        }
    }

    /**
     * Identity Upload Page
     */
    public function identityUpload()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        // Must verify email first
        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return redirect()->to('/verify-otp')
                ->with('error', 'Please verify your email first.');
        }

        // If document is pending or approved, redirect to status page
        if ($document && $document['status'] === 'PENDING') {
            return redirect()->to('/verification-status');
        }

        if ($document && $document['status'] === 'APPROVED') {
            return redirect()->to('/dashboard');
        }

        // Calculate display name and initial for header
        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

        // Check verification status
        $isFullyVerified = ($profile['verification_status'] ?? 'PENDING') === 'COMPLETED'
            && ($document['status'] ?? 'NOT_UPLOADED') === 'APPROVED';

        return view('user/identity_upload', [
            'pageTitle'             => 'Upload Identity',
            'hasRejected'           => $document && $document['status'] === 'REJECTED',
            'rejectionReason'       => $document['remarks'] ?? null,
            'displayName'           => $displayName,
            'displayInitial'        => $displayInitial,
            'isPendingVerification' => false, // Email is verified at this point
            'isFullyVerified'       => $isFullyVerified,
            'profileVerificationStatus' => $profile['verification_status'] ?? 'COMPLETED',
            'documentStatus'        => $document['status'] ?? 'NOT_UPLOADED',
        ]);
    }

    /**
     * Process Identity Upload
     */
    public function processIdentityUpload()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        // Must verify email first
        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return redirect()->to('/verify-otp')
                ->with('error', 'Please verify your email first.');
        }

        // Validate Aadhaar number
        $aadharNumber = $this->request->getPost('aadhar_number');
        $aadharNumber = preg_replace('/\s+/', '', $aadharNumber); // Remove spaces

        if (!preg_match('/^\d{12}$/', $aadharNumber)) {
            return $this->backWithError('Aadhaar number must be exactly 12 digits.');
        }

        // Handle file upload
        $file = $this->request->getFile('document');

        if (!$file || !$file->isValid()) {
            return $this->backWithError('Please upload a valid document.');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->backWithError('Invalid file type. Please upload JPEG, PNG, or PDF.');
        }

        // Validate file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->backWithError('File size must be less than 5MB.');
        }

        // Upload to AWS S3
        $s3 = service('s3');
        $result = $s3->upload($file, 'documents/' . $user->id);

        if (!$result['success']) {
            log_message('error', 'S3 Upload failed: ' . $result['error']);
            return $this->backWithError('Failed to upload document. Please try again.');
        }

        // Store S3 key in database (not full URL)
        $documentUrl = $result['key'];

        // Create document record
        $dbResult = $this->documentModel->createDocument($user->id, $aadharNumber, $documentUrl);

        if (!$dbResult) {
            return $this->backWithError('Failed to save document. Please try again.');
        }

        return $this->redirectWithSuccess(
            '/verification-status',
            'Document uploaded successfully. Please wait for verification.'
        );
    }

    /**
     * API: Get presigned URL for direct S3 upload
     */
    public function apiGetUploadUrl()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        // Must verify email first
        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return $this->jsonError('Please verify your email first.');
        }

        // Validate Aadhaar number
        $aadharNumber = $this->request->getPost('aadhar_number');
        $aadharNumber = preg_replace('/\s+/', '', $aadharNumber ?? '');

        if (!preg_match('/^\d{12}$/', $aadharNumber)) {
            return $this->jsonError('Aadhaar number must be exactly 12 digits.');
        }

        // Check if Aadhaar is already in use by another user
        if ($this->documentModel->isAadhaarInUse($aadharNumber, $user->id)) {
            return $this->jsonError('This Aadhaar number is already registered with another account.');
        }

        // Get file info
        $filename = $this->request->getPost('filename');
        $contentType = $this->request->getPost('content_type');
        $fileSize = (int) $this->request->getPost('file_size');

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($contentType, $allowedTypes)) {
            return $this->jsonError('Invalid file type. Please upload JPEG, PNG, or PDF.');
        }

        // Validate file size (max 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            return $this->jsonError('File size must be less than 5MB.');
        }

        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = match ($contentType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => 'bin'
            };
        }

        // Generate presigned upload URL
        $s3 = service('s3');
        $awsConfig = config('Aws');
        $newFilename = $s3->generateFilename($extension);
        $result = $s3->getPresignedUploadUrl(
            $awsConfig->documentsPrefix . '/' . $user->id,
            $newFilename,
            $contentType
        );

        if (!$result['success']) {
            return $this->jsonError('Failed to generate upload URL.');
        }

        // Store pending upload info in session
        session()->set('pending_upload', [
            'key' => $result['key'],
            'aadhar_number' => $aadharNumber,
            'created_at' => time(),
        ]);

        return $this->jsonSuccess('Upload URL generated.', [
            'uploadUrl' => $result['uploadUrl'],
            'key' => $result['key'],
        ]);
    }

    /**
     * API: Confirm S3 upload and save to database
     */
    public function apiConfirmUpload()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        // Must verify email first
        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return $this->jsonError('Please verify your email first.');
        }

        // Get pending upload from session
        $pendingUpload = session('pending_upload');
        if (!$pendingUpload) {
            return $this->jsonError('No pending upload found. Please try again.');
        }

        // Check if upload was recent (within 10 minutes)
        if (time() - $pendingUpload['created_at'] > 600) {
            session()->remove('pending_upload');
            return $this->jsonError('Upload session expired. Please try again.');
        }

        // Verify the file exists in S3
        $s3 = service('s3');
        if (!$s3->exists($pendingUpload['key'])) {
            return $this->jsonError('File not found. Please upload again.');
        }

        // Create document record
        $result = $this->documentModel->createDocument(
            $user->id,
            $pendingUpload['aadhar_number'],
            $pendingUpload['key']
        );

        if (!$result) {
            return $this->jsonError('Failed to save document. Please try again.');
        }

        // Clear pending upload
        session()->remove('pending_upload');

        return $this->jsonSuccess('Document uploaded successfully!', [
            'redirect' => '/verification-status'
        ]);
    }

    /**
     * Verification Status Page
     */
    public function verificationStatus()
    {
        $user = $this->user();
        $document = $this->getDocument();

        // If no document, redirect to upload
        if (!$document) {
            return redirect()->to('/identity-upload');
        }

        // If approved, redirect to dashboard
        if ($document['status'] === 'APPROVED') {
            return redirect()->to('/dashboard');
        }

        return view('user/verification_status', [
            'pageTitle'         => 'Verification Status',
            'documentStatus' => $document['status'],
            'rejectionNote'  => $document['remarks'],
            'submittedAt'    => $document['created_at'],
            'reviewedAt'     => $document['reviewed_at'],
        ]);
    }

    /**
     * Video Learning Dashboard
     */
    public function videos()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/videos', [
            'pageTitle' => 'Training Videos',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'completedVideos' => 0,
            'totalVideos' => 10,
        ]);
    }

    /**
     * Video Player
     */
    public function videoPlayer($videoId = 1)
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/video_player', [
            'pageTitle' => 'Video Player',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'videoId' => $videoId,
            'videoTitle' => 'Traffic Signs Introduction',
            'duration' => '10:30',
            'videoUrl' => 'https://example.com/video.mp4',
            'currentProgress' => 45,
            'nextVideoId' => $videoId + 1,
        ]);
    }

    /**
     * Video Progress Page
     */
    public function videoProgress()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/video_progress', [
            'pageTitle' => 'My Progress',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'totalVideos' => 10,
            'completedVideos' => 5,
            'lastWatched' => 'Traffic Signs Introduction',
            'overallProgress' => 50
        ]);
    }

    /**
     * Test Instructions
     */
    public function testInstructions()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test_instructions', [
            'pageTitle' => 'Test Instructions',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'testDuration' => 30,
            'totalQuestions' => 20,
            'passingMarks' => 60
        ]);
    }

    /**
     * Online Test
     */
    public function test()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test', [
            'pageTitle' => 'Online Test',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'remainingTime' => 1800,
            'currentQuestion' => 1
        ]);
    }

    /**
     * Test Result
     */
    public function testResult($testId = 1)
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test_result', [
            'pageTitle' => 'Test Result',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'testId' => $testId,
            'score' => 85,
            'totalQuestions' => 20,
            'correctAnswers' => 17,
            'result' => 'PASSED',
            'remarks' => 'Excellent performance!'
        ]);
    }

    /**
     * Certificate Page
     */
    public function certificate()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/certificate', [
            'pageTitle' => 'My Certificate',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'hasCertificate' => false,
            'firstName' => $profile['first_name'] ?? '',
            'lastName' => $profile['last_name'] ?? '',
            'certificateNumber' => '',
            'issueDate' => '',
        ]);
    }
}
