<?php

namespace App\Controllers\User;

class DashboardController extends BaseUserController
{
    public function index()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        $videosCompleted = 0;
        $totalVideos = 10;
        $progressPercent = $totalVideos > 0 ? round(($videosCompleted / $totalVideos) * 100) : 0;

        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

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
}
