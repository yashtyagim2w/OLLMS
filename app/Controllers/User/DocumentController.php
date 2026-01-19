<?php

namespace App\Controllers\User;

use App\Traits\ResponseTrait;

class DocumentController extends BaseUserController
{
    use ResponseTrait;

    public function upload()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return redirect()->to('/verify-otp')->with('error', 'Please verify your email first.');
        }

        if ($document && $document['status'] === 'PENDING') {
            return redirect()->to('/verification-status');
        }

        if ($document && $document['status'] === 'APPROVED') {
            return redirect()->to('/dashboard');
        }

        $displayName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
        $displayName = trim($displayName) ?: $user->email ?? 'User';
        $displayInitial = strtoupper(substr($profile['first_name'] ?? $user->email ?? 'U', 0, 1));

        $isFullyVerified = ($profile['verification_status'] ?? 'PENDING') === 'COMPLETED'
            && ($document['status'] ?? 'NOT_UPLOADED') === 'APPROVED';

        return view('user/identity_upload', [
            'pageTitle'                 => 'Upload Identity',
            'hasRejected'               => $document && $document['status'] === 'REJECTED',
            'rejectionReason'           => $document['remarks'] ?? null,
            'displayName'               => $displayName,
            'displayInitial'            => $displayInitial,
            'isPendingVerification'     => false,
            'isFullyVerified'           => $isFullyVerified,
            'profileVerificationStatus' => $profile['verification_status'] ?? 'COMPLETED',
            'documentStatus'            => $document['status'] ?? 'NOT_UPLOADED',
        ]);
    }

    public function processUpload()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return redirect()->to('/verify-otp')->with('error', 'Please verify your email first.');
        }

        $aadharNumber = preg_replace('/\s+/', '', $this->request->getPost('aadhar_number'));

        if (!preg_match('/^\d{12}$/', $aadharNumber)) {
            return $this->backWithError('Aadhaar number must be exactly 12 digits.');
        }

        $file = $this->request->getFile('document');

        if (!$file || !$file->isValid()) {
            return $this->backWithError('Please upload a valid document.');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->backWithError('Invalid file type. Please upload JPEG, PNG, or PDF.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->backWithError('File size must be less than 5MB.');
        }

        $s3 = service('s3');
        $result = $s3->upload($file, 'documents/' . $user->id);

        if (!$result['success']) {
            log_message('error', 'S3 Upload failed: ' . $result['error']);
            return $this->backWithError('Failed to upload document. Please try again.');
        }

        $documentUrl = $result['key'];
        $dbResult = $this->documentModel->createDocument($user->id, $aadharNumber, $documentUrl);

        if (!$dbResult) {
            return $this->backWithError('Failed to save document. Please try again.');
        }

        return $this->redirectWithSuccess('/verification-status', 'Document uploaded successfully. Please wait for verification.');
    }

    public function apiGetUploadUrl()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return $this->jsonError('Please verify your email first.');
        }

        $aadharNumber = preg_replace('/\s+/', '', $this->request->getPost('aadhar_number') ?? '');

        if (!preg_match('/^\d{12}$/', $aadharNumber)) {
            return $this->jsonError('Aadhaar number must be exactly 12 digits.');
        }

        if ($this->documentModel->isAadhaarInUse($aadharNumber, $user->id)) {
            return $this->jsonError('This Aadhaar number is already registered with another account.');
        }

        $filename = $this->request->getPost('filename');
        $contentType = $this->request->getPost('content_type');
        $fileSize = (int) $this->request->getPost('file_size');

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($contentType, $allowedTypes)) {
            return $this->jsonError('Invalid file type. Please upload JPEG, PNG, or PDF.');
        }

        if ($fileSize > 5 * 1024 * 1024) {
            return $this->jsonError('File size must be less than 5MB.');
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = match ($contentType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => 'bin'
            };
        }

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

    public function apiConfirmUpload()
    {
        $user = $this->user();
        $profile = $this->getProfile();

        if (!$profile || $profile['verification_status'] !== 'COMPLETED') {
            return $this->jsonError('Please verify your email first.');
        }

        $pendingUpload = session('pending_upload');
        if (!$pendingUpload) {
            return $this->jsonError('No pending upload found. Please try again.');
        }

        if (time() - $pendingUpload['created_at'] > 600) {
            session()->remove('pending_upload');
            return $this->jsonError('Upload session expired. Please try again.');
        }

        $s3 = service('s3');
        if (!$s3->exists($pendingUpload['key'])) {
            return $this->jsonError('File not found. Please upload again.');
        }

        $result = $this->documentModel->createDocument(
            $user->id,
            $pendingUpload['aadhar_number'],
            $pendingUpload['key']
        );

        if (!$result) {
            return $this->jsonError('Failed to save document. Please try again.');
        }

        session()->remove('pending_upload');

        return $this->jsonSuccess('Document uploaded successfully!', ['redirect' => '/verification-status']);
    }

    public function status()
    {
        $user = $this->user();
        $document = $this->getDocument();

        if (!$document) {
            return redirect()->to('/identity-upload');
        }

        if ($document['status'] === 'APPROVED') {
            return redirect()->to('/dashboard');
        }

        return view('user/verification_status', [
            'pageTitle'      => 'Verification Status',
            'documentStatus' => $document['status'],
            'rejectionNote'  => $document['remarks'],
            'submittedAt'    => $document['created_at'],
            'reviewedAt'     => $document['reviewed_at'],
        ]);
    }
}
