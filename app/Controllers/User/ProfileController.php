<?php

namespace App\Controllers\User;

class ProfileController extends BaseUserController
{
    public function index()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

        $isFullyVerified = ($profile['verification_status'] ?? 'PENDING') === 'COMPLETED'
            && ($document['status'] ?? 'NOT_UPLOADED') === 'APPROVED';

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
}
