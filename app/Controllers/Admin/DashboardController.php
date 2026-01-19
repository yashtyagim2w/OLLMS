<?php

namespace App\Controllers\Admin;

/**
 * Admin Dashboard Controller
 */
class DashboardController extends BaseAdminController
{
    /**
     * Admin Dashboard
     */
    public function index()
    {
        $userService = service('users');
        $documentService = service('documents');

        $stats = $userService->getDashboardStats();
        $pendingList = $documentService->getRecentPendingDocuments(5);

        return view('admin/dashboard', array_merge([
            'pageTitle' => 'Dashboard',
            'adminName' => 'Admin',
            'pendingList' => $pendingList,
        ], $stats));
    }
}
