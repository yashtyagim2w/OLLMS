<?php

namespace App\Services;

use App\Models\UserDocumentModel;
use App\Models\UserProfileModel;

/**
 * Document Service
 * 
 * Handles admin document verification operations
 * (Separate from UserService which handles user management)
 */
class DocumentService
{
    protected UserDocumentModel $documentModel;
    protected UserProfileModel $profileModel;

    public function __construct()
    {
        $this->documentModel = new UserDocumentModel();
        $this->profileModel = new UserProfileModel();
    }

    /**
     * Get paginated documents for admin review
     * 
     * @param array $filters Filters (status, search, sort_by, sort_order)
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array Formatted response with data and pagination
     */
    public function getDocumentsForReview(array $filters, int $page = 1, int $limit = 10): array
    {
        $result = $this->documentModel->getDocumentsWithUserDetails($filters, $page, $limit);

        // Generate presigned URLs for documents and format timestamps
        $s3Service = service('s3');
        $formattedData = array_map(function ($doc) use ($s3Service) {
            // Generate presigned URL if document_url exists and is an S3 key
            if (!empty($doc['document_url'])) {
                $doc['document_url'] = $s3Service->getPresignedUrl($doc['document_url']);
            }

            // Convert timestamps to ISO 8601 UTC format for proper JavaScript parsing
            if (!empty($doc['submitted_at'])) {
                $doc['submitted_at'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($doc['submitted_at']));
            }
            if (!empty($doc['reviewed_at'])) {
                $doc['reviewed_at'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($doc['reviewed_at']));
            }

            return $doc;
        }, $result['data']);

        return [
            'data' => $formattedData,
            'pagination' => [
                'page' => $result['page'],
                'limit' => $result['limit'],
                'totalPages' => ceil($result['total'] / $result['limit']),
                'totalRecords' => $result['total'],
            ],
        ];
    }

    /**
     * Get single document detail with presigned URL for admin review
     * 
     * @param int $documentId Document ID
     * @return array|null Document data or null if not found
     */
    public function getDocumentDetail(int $documentId): ?array
    {
        $document = $this->documentModel->getDocumentWithUserDetails($documentId);

        if (!$document) {
            return null;
        }

        // Generate presigned URL for document viewing
        if (!empty($document['document_url'])) {
            $s3Service = service('s3');
            $document['document_url'] = $s3Service->getPresignedUrl($document['document_url'], 15); // 15 minutes
        }

        // Convert timestamps to ISO 8601 UTC format
        if (!empty($document['submitted_at'])) {
            $document['submitted_at'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($document['submitted_at']));
        }
        if (!empty($document['reviewed_at'])) {
            $document['reviewed_at'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($document['reviewed_at']));
        }

        return $document;
    }

    /**
     * Approve a document with optional remarks
     * 
     * @param int $documentId Document ID
     * @param int $reviewerId Admin user ID who is approving
     * @param string|null $remarks Optional remarks
     * @return array Response with success status and message
     */
    public function approveDocument(int $documentId, int $reviewerId, ?string $remarks = null): array
    {
        // Get document to verify it exists and is pending
        $document = $this->documentModel->getDocumentWithUserDetails($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        if ($document['status'] !== 'PENDING') {
            return ['success' => false, 'message' => 'Document is already ' . strtolower($document['status'])];
        }

        // Approve the document
        $result = $this->documentModel->approveDocument($documentId, $reviewerId, $remarks);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to approve document'];
        }

        // Send approval email
        $this->sendApprovalEmail($document['user_id'], $remarks);

        return ['success' => true, 'message' => 'Document approved successfully'];
    }

    /**
     * Reject a document with required remarks
     * 
     * @param int $documentId Document ID
     * @param int $reviewerId Admin user ID who is rejecting
     * @param string $remarks Required rejection reason
     * @return array Response with success status and message
     */
    public function rejectDocument(int $documentId, int $reviewerId, string $remarks): array
    {
        // Get document to verify it exists and is pending
        $document = $this->documentModel->getDocumentWithUserDetails($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        if ($document['status'] !== 'PENDING') {
            return ['success' => false, 'message' => 'Document is already ' . strtolower($document['status'])];
        }

        if (empty($remarks)) {
            return ['success' => false, 'message' => 'Rejection reason is required'];
        }

        // Reject the document
        $result = $this->documentModel->rejectDocument($documentId, $reviewerId, $remarks);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to reject document'];
        }

        // Send rejection email
        $this->sendRejectionEmail($document['user_id'], $remarks);

        return ['success' => true, 'message' => 'Document rejected successfully'];
    }

    /**
     * Send approval email to user
     * 
     * @param int $userId User ID
     * @param string|null $remarks Optional remarks from admin
     */
    private function sendApprovalEmail(int $userId, ?string $remarks = null): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) {
            log_message('warning', 'DocumentService: Could not send approval email - no email found for user ' . $userId);
            return;
        }

        try {
            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Document Approved - OLLMS');
            $emailService->setMessage(view('emails/document_approved', [
                'name' => $name,
                'remarks' => $remarks,
            ]));
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'DocumentService::sendApprovalEmail error: ' . $e->getMessage());
        }
    }

    /**
     * Send rejection email to user
     * 
     * @param int $userId User ID
     * @param string $remarks Rejection reason from admin
     */
    private function sendRejectionEmail(int $userId, string $remarks): void
    {
        $email = $this->getUserEmail($userId);
        $name = $this->getUserName($userId);

        if (!$email) {
            log_message('warning', 'DocumentService: Could not send rejection email - no email found for user ' . $userId);
            return;
        }

        try {
            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Document Rejected - OLLMS');
            $emailService->setMessage(view('emails/document_rejected', [
                'name' => $name,
                'remarks' => $remarks,
            ]));
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'DocumentService::sendRejectionEmail error: ' . $e->getMessage());
        }
    }

    /**
     * Get user email by user ID
     * 
     * @param int $userId User ID
     * @return string|null Email address or null if not found
     */
    private function getUserEmail(int $userId): ?string
    {
        return $this->profileModel->getUserEmail($userId);
    }

    /**
     * Get user full name by user ID
     * 
     * @param int $userId User ID
     * @return string Full name or 'User' if not found
     */
    private function getUserName(int $userId): string
    {
        return $this->profileModel->getFullName($userId) ?: 'User';
    }

    /**
     * Get recent pending documents for dashboard display
     * 
     * @param int $limit Number of documents to return
     * @return array List of pending documents with user info
     */
    public function getRecentPendingDocuments(int $limit = 5): array
    {
        $result = $this->documentModel->getDocumentsWithUserDetails(
            ['status' => 'PENDING'],
            1,
            $limit
        );

        return array_map(function ($doc) {
            return [
                'id' => $doc['id'],
                'name' => trim(($doc['first_name'] ?? '') . ' ' . ($doc['last_name'] ?? '')),
                'email' => $doc['email'] ?? '',
                'submitted_at' => $doc['submitted_at'] ?? '',
            ];
        }, $result['data']);
    }
}
