<?php

namespace App\Controllers;

/**
 * User Controller
 * Handles user-facing view rendering
 */
class UserController extends BaseController
{
    /**
     * User Dashboard
     */
    public function dashboard()
    {
        // Mock data - would come from models in real implementation
        $verificationStatus = 'APPROVED'; // PENDING, APPROVED
        $documentStatus = 'APPROVED'; // NOT_UPLOADED, PENDING, APPROVED, REJECTED

        return view('user/dashboard', [
            'pageTitle' => 'Dashboard',
            'userName' => 'User',
            'verificationStatus' => $verificationStatus,
            'documentStatus' => $documentStatus,
            'videosCompleted' => 5,
            'totalVideos' => 10,
            'progressPercent' => 50,
            'testAttempts' => 1,
            'testResult' => 'NONE' // NONE, PASS, FAIL
        ]);
    }

    /**
     * Email OTP Verification Page
     */
    public function verifyOtp()
    {
        $user = auth()->user();
        return view('user/verify_otp', [
            'pageTitle' => 'Verify Email',
            'email' => $user->email ?? 'your@email.com',
            'isVerified' => false // Mock - would check user's email_verified status
        ]);
    }

    /**
     * User Profile
     */
    public function profile()
    {
        return view('user/profile', [
            'pageTitle' => 'My Profile',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'dob' => '1998-03-15',
            'aadharMasked' => 'XXXX-XXXX-1234',
            'verificationStatus' => 'APPROVED',
            'documentStatus' => 'APPROVED',
            'createdAt' => '2026-01-01'
        ]);
    }

    /**
     * Identity Upload Page
     */
    public function identityUpload()
    {
        return view('user/identity_upload', [
            'pageTitle' => 'Upload Identity'
        ]);
    }

    /**
     * Verification Status Page
     */
    public function verificationStatus()
    {
        return view('user/verification_status', [
            'pageTitle' => 'Verification Status',
            'documentStatus' => 'PENDING', // PENDING, APPROVED, REJECTED
            'rejectionNote' => null,
            'submittedAt' => '2026-01-10 10:30:00',
            'reviewedAt' => null
        ]);
    }

    /**
     * Video Learning Dashboard
     */
    public function videos()
    {
        return view('user/videos', [
            'pageTitle' => 'Training Videos',
            'completedVideos' => 3,
            'totalVideos' => 10
        ]);
    }

    /**
     * Video Player
     */
    public function videoPlayer($videoId = 1)
    {
        return view('user/video_player', [
            'pageTitle' => 'Video Player',
            'videoId' => $videoId,
            'videoTitle' => 'Introduction to Traffic Signs',
            'videoUrl' => '/assets/videos/sample.mp4',
            'duration' => '10:30',
            'categoryName' => 'Traffic Rules',
            'watchedPercent' => 45
        ]);
    }

    /**
     * Video Progress Page
     */
    public function videoProgress()
    {
        return view('user/video_progress', [
            'pageTitle' => 'My Progress',
            'completedVideos' => 5,
            'inProgressVideos' => 2,
            'totalWatchTime' => '2h 15m',
            'overallProgress' => 70
        ]);
    }

    /**
     * Test Instructions
     */
    public function testInstructions()
    {
        return view('user/test_instructions', [
            'pageTitle' => 'Test Instructions',
            'totalQuestions' => 25,
            'testDuration' => 30,
            'passingScore' => 60
        ]);
    }

    /**
     * Online Test
     */
    public function test()
    {
        return view('user/test', [
            'pageTitle' => 'Online Test',
            'totalQuestions' => 25
        ]);
    }

    /**
     * Test Result
     */
    public function testResult($testId = 1)
    {
        return view('user/test_result', [
            'pageTitle' => 'Test Result',
            'score' => 72,
            'totalQuestions' => 25,
            'correctAnswers' => 18,
            'testDate' => date('Y-m-d H:i:s'),
            'timeTaken' => '22:45'
        ]);
    }

    /**
     * Certificate Page
     */
    public function certificate()
    {
        return view('user/certificate', [
            'pageTitle' => 'My Certificate',
            'hasCertificate' => true,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'certificateNumber' => 'LL-2026-0012345',
            'issueDate' => date('Y-m-d')
        ]);
    }
}
