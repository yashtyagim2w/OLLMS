<?php

namespace App\Controllers\User;

class CertificateController extends BaseUserController
{
    public function index()
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
