<?php

namespace App\Controllers\Admin;

/**
 * Identity Review Controller
 * Handles identity document verification
 */
class IdentityReviewController extends BaseAdminController
{
    /**
     * Identity Verification Review Page
     */
    public function index()
    {
        $documentModel = new \App\Models\UserDocumentModel();
        $pendingCount = $documentModel->countPending();

        return view('admin/identity_review', [
            'pageTitle' => 'Identity Verification',
            'pendingCount' => $pendingCount
        ]);
    }

    /**
     * Get Identity Reviews API
     */
    public function getList()
    {
        $documentService = service('documents');

        $filters = [
            'status' => $this->request->getGet('status'),
            'search' => $this->request->getGet('search'),
            'sort_by' => $this->request->getGet('sort_by'),
            'sort_order' => $this->request->getGet('sort_order'),
        ];

        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 10);

        $result = $documentService->getDocumentsForReview($filters, $page, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get Identity Review Detail API
     */
    public function getDetail($id)
    {
        $documentService = service('documents');
        $document = $documentService->getDocumentDetail((int) $id);

        if (!$document) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Document not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Approve Identity Document API
     */
    public function approve($id)
    {
        $documentService = service('documents');
        $adminId = $this->adminUser()->id;
        $remarks = $this->request->getPost('remarks');

        $result = $documentService->approveDocument((int) $id, $adminId, $remarks);

        return $this->response->setJSON($result);
    }

    /**
     * Reject Identity Document API
     */
    public function reject($id)
    {
        $documentService = service('documents');
        $adminId = $this->adminUser()->id;
        $remarks = $this->request->getPost('remarks');

        if (empty($remarks)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rejection reason is required'
            ]);
        }

        $result = $documentService->rejectDocument((int) $id, $adminId, $remarks);

        return $this->response->setJSON($result);
    }
}
