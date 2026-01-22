<?php

namespace App\Controllers\Admin;

/**
 * User Management Controller
 * Handles admin user CRUD operations
 */
class UserManagementController extends BaseAdminController
{
    /**
     * User Management Page
     */
    public function index()
    {
        return view('admin/users', [
            'pageTitle' => 'User Management'
        ]);
    }

    /**
     * Get Users API
     */
    public function getList()
    {
        $userService = service('users');

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'test_status' => $this->request->getGet('test_status'),
            'active_status' => $this->request->getGet('active_status'),
            'sort_by' => $this->request->getGet('sort_by'),
            'sort_order' => $this->request->getGet('sort_order'),
        ];

        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 10);

        $result = $userService->getUsers($filters, $page, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Export Users to CSV
     */
    public function export()
    {
        $userService = service('users');

        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'test_status' => $this->request->getGet('test_status'),
            'active_status' => $this->request->getGet('active_status'),
            'sort_by' => $this->request->getGet('sort_by'),
            'sort_order' => $this->request->getGet('sort_order'),
        ];

        // Get all data without pagination for export
        $result = $userService->getUsers($filters, 1, 999999);
        $users = $result['data'] ?? [];

        // Prepare CSV data
        $csvData = [];

        // Add header row
        $csvData[] = [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Date of Birth',
            'Aadhaar Number',
            'Active Status',
            'Email Verified',
            'Document Status',
            'Video Progress (%)',
            'Test Result',
            'Test Score',
            'Certificate Number'
        ];

        // Add data rows
        foreach ($users as $user) {
            $csvData[] = [
                $user['id'] ?? '',
                $user['first_name'] ?? '',
                $user['last_name'] ?? '',
                $user['email'] ?? '',
                $user['dob'] ?? '',
                $user['aadhar_number'] ?? '',
                $user['active'] ? 'Active' : 'Inactive',
                $user['verificationStatus'] === 'COMPLETED' ? 'Yes' : 'No',
                $user['docStatus'] ?? 'NOT_UPLOADED',
                $user['videoProgress'] ?? 0,
                $user['testResult'] ?? '-',
                $user['testScore'] ?? '-',
                $user['certificateNumber'] ?? '-'
            ];
        }

        // Generate filename with current date
        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';

        // Set headers for CSV download
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->arrayToCsv($csvData));
    }

    /**
     * Get Single User API
     */
    public function getDetail($id)
    {
        $userService = service('users');
        $user = $userService->getUserById((int) $id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update User API
     */
    public function update($id)
    {
        $userService = service('users');

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'dob' => $this->request->getPost('dob'),
            'aadhar_number' => $this->request->getPost('aadhar_number'),
            'verification_status' => $this->request->getPost('verification_status'),
            'doc_status' => $this->request->getPost('doc_status'),
            'active' => $this->request->getPost('active'),
        ];

        // Remove empty values
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        $result = $userService->updateUser((int) $id, $data);

        return $this->response->setJSON($result);
    }

    /**
     * Ban User API (Soft Delete)
     */
    public function ban($id)
    {
        $userService = service('users');
        $result = $userService->banUser((int) $id);

        return $this->response->setJSON($result);
    }

    /**
     * Activate User API (Restore)
     */
    public function activate($id)
    {
        $userService = service('users');
        $result = $userService->activateUser((int) $id);

        return $this->response->setJSON($result);
    }

    /**
     * Set User Password API
     */
    public function setPassword($id)
    {
        $password = $this->request->getPost('password');

        if (empty($password) || strlen($password) < 8) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password must be at least 8 characters',
            ]);
        }

        $userService = service('users');
        $result = $userService->setPassword((int) $id, $password);

        return $this->response->setJSON($result);
    }
    
}
