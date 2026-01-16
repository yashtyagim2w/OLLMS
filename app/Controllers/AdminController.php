<?php

namespace App\Controllers;

/**
 * Admin Controller
 * Handles admin panel view rendering
 */
class AdminController extends BaseController
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
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

    /**
     * Admin Profile
     */
    public function profile()
    {
        $user = auth()->user();
        $profileModel = model('App\Models\UserProfileModel');
        $profile = null;

        if ($profileModel) {
            $profile = $profileModel->where('user_id', $user->id)->first();
        }

        return view('admin/profile', [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'profile' => $profile
        ]);
    }

    /**
     * Identity Verification Review
     */
    public function identityReview()
    {
        $documentModel = new \App\Models\UserDocumentModel();
        $pendingCount = $documentModel->countPending();

        return view('admin/identity_review', [
            'pageTitle' => 'Identity Verification',
            'pendingCount' => $pendingCount
        ]);
    }

    /**
     * User Management
     */
    public function users()
    {
        return view('admin/users', [
            'pageTitle' => 'User Management'
        ]);
    }

    /**
     * Video Management
     */
    public function videos()
    {
        return view('admin/videos', [
            'pageTitle' => 'Video Management'
        ]);
    }

    /**
     * Question Bank Management
     */
    public function questions()
    {
        return view('admin/questions', [
            'pageTitle' => 'Question Bank'
        ]);
    }

    /**
     * Test Instruction Management
     */
    public function instructions()
    {
        return view('admin/instructions', [
            'pageTitle' => 'Test Instructions'
        ]);
    }

    /**
     * Progress Monitoring
     */
    public function progress()
    {
        return view('admin/progress', [
            'pageTitle' => 'Progress Monitoring',
            'totalVideoViews' => 1234,
            'videosCompleted' => 456,
            'testsTaken' => 89,
            'avgScore' => 72
        ]);
    }

    /**
     * Reports & Analytics
     */
    /**
     * Reports & Analytics
     */
    public function reports()
    {
        return view('admin/reports', [
            'pageTitle' => 'Reports & Analytics',
            'totalRegistrations' => 156,
            'verified' => 124,
            'testsTaken' => 89,
            'certificates' => 67,
            'passPercentage' => 76
        ]);
    }

    // --------------------------------------------------------------------
    // API Methods for List Pages
    // --------------------------------------------------------------------

    /**
     * Get Identity Reviews API
     */
    public function getIdentityReviews()
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
    public function getIdentityReviewDetail($id)
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
    public function approveIdentity($id)
    {
        $documentService = service('documents');
        $adminId = auth()->user()->id;
        $remarks = $this->request->getPost('remarks');

        $result = $documentService->approveDocument((int) $id, $adminId, $remarks);

        return $this->response->setJSON($result);
    }

    /**
     * Reject Identity Document API
     */
    public function rejectIdentity($id)
    {
        $documentService = service('documents');
        $adminId = auth()->user()->id;
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


    /**
     * Get Users API
     */
    public function getUsers()
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
    public function exportUsers()
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
     * Convert array to CSV string
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get Single User API
     */
    public function getUser($id)
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
    public function updateUser($id)
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
    public function banUser($id)
    {
        $userService = service('users');
        $result = $userService->banUser((int) $id);

        return $this->response->setJSON($result);
    }

    /**
     * Activate User API (Restore)
     */
    public function activateUser($id)
    {
        $userService = service('users');
        $result = $userService->activateUser((int) $id);

        return $this->response->setJSON($result);
    }

    /**
     * Set User Password API
     */
    public function setUserPassword($id)
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

    /**
     * Get Videos API
     */
    public function getVideos()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('active_status');
        $category = $this->request->getGet('category');

        // Mock videos data
        $videos = [
            ['id' => 1, 'title' => 'Introduction to Traffic Signs', 'category' => 'Traffic Rules', 'duration' => '10:30', 'active' => true, 'url' => '...'],
            ['id' => 2, 'title' => 'Road Markings Explained', 'category' => 'Traffic Rules', 'duration' => '08:45', 'active' => true, 'url' => '...'],
            ['id' => 3, 'title' => 'Pre-Drive Safety Check', 'category' => 'Vehicle Safety', 'duration' => '07:20', 'active' => false, 'url' => '...'],
            ['id' => 4, 'title' => 'Defensive Driving Basics', 'category' => 'Traffic Rules', 'duration' => '12:00', 'active' => true, 'url' => '...'],
            ['id' => 5, 'title' => 'Changing Lanes Safely', 'category' => 'Driving Techniques', 'duration' => '05:30', 'active' => true, 'url' => '...'],
            ['id' => 6, 'title' => 'Parking Maneuvers', 'category' => 'Driving Techniques', 'duration' => '15:15', 'active' => true, 'url' => '...'],
            ['id' => 7, 'title' => 'Night Driving Tips', 'category' => 'Refresher', 'duration' => '09:00', 'active' => false, 'url' => '...'],
        ];

        // Filters
        if ($search) {
            $videos = array_filter($videos, fn($v) => str_contains(strtolower($v['title']), strtolower($search)));
        }
        if ($status !== null && $status !== '') {
            $isActive = filter_var($status, FILTER_VALIDATE_BOOLEAN);
            $videos = array_filter($videos, fn($v) => $v['active'] === $isActive);
        }
        if ($category) {
            $videos = array_filter($videos, fn($v) => $v['category'] === $category);
        }

        // Pagination
        $total = count($videos);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($videos, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($pagedData),
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'totalRecords' => $total
                ]
            ]
        ]);
    }

    /**
     * Get Questions API
     */
    public function getQuestions()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');
        $category = $this->request->getGet('category');

        // Mock questions data
        $questions = [
            ['id' => 1, 'question' => 'What does a red traffic light mean?', 'category' => 'Traffic Signals', 'active' => true],
            ['id' => 2, 'question' => 'When can you overtake from the left?', 'category' => 'Rules of Road', 'active' => true],
            ['id' => 3, 'question' => 'What is the maximum speed limit in a city?', 'category' => 'Speed Limits', 'active' => true],
            ['id' => 4, 'question' => 'What does this sign indicate? (Triangle with kids)', 'category' => 'Traffic Signs', 'active' => true],
            ['id' => 5, 'question' => 'Who has the right of way at a roundabout?', 'category' => 'Rules of Road', 'active' => true],
            ['id' => 6, 'question' => 'What is the legal alcohol limit for driving?', 'category' => 'Safety', 'active' => true],
            ['id' => 7, 'question' => 'How close can you park to a fire hydrant?', 'category' => 'Parking', 'active' => false],
        ];

        // Filters
        if ($search) {
            $questions = array_filter($questions, fn($q) => str_contains(strtolower($q['question']), strtolower($search)));
        }
        if ($category) {
            $questions = array_filter($questions, fn($q) => $q['category'] === $category);
        }

        // Pagination
        $total = count($questions);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($questions, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($pagedData),
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'totalRecords' => $total
                ]
            ]
        ]);
    }

    /**
     * Get Instructions API
     */
    public function getInstructions()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');

        // Mock instructions data
        $instructions = [
            ['id' => 1, 'title' => 'General Test Guidelines', 'content_preview' => 'All candidates must arrive 30 minutes before...', 'order' => 1, 'active' => true],
            ['id' => 2, 'title' => 'Computer System Usage', 'content_preview' => 'Do not press the refresh button during the test...', 'order' => 2, 'active' => true],
            ['id' => 3, 'title' => 'Passing Criteria', 'content_preview' => 'You must answer at least 12 out of 20 questions corre...', 'order' => 3, 'active' => true],
            ['id' => 4, 'title' => 'Malpractice Warning', 'content_preview' => 'Using mobile phones or talking to others is strict...', 'order' => 4, 'active' => true],
            ['id' => 5, 'title' => 'Emergency Procedures', 'content_preview' => 'In case of fire alarm, exit calmly through...', 'order' => 5, 'active' => false],
        ];

        // Filters
        if ($search) {
            $instructions = array_filter($instructions, fn($i) => str_contains(strtolower($i['title']), strtolower($search)));
        }

        // Pagination
        $total = count($instructions);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($instructions, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($pagedData),
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'totalRecords' => $total
                ]
            ]
        ]);
    }

    /**
     * Get User Progress API
     */
    public function getUserProgress()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');

        // Mock progress data
        $progress = [
            ['id' => 1, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@email.com', 'videoProgress' => 100, 'lastActivity' => '2 hours ago', 'attempts' => 2, 'bestScore' => 84, 'passed' => true],
            ['id' => 2, 'first_name' => 'Priya', 'last_name' => 'Patel', 'email' => 'priya@email.com', 'videoProgress' => 80, 'lastActivity' => '1 day ago', 'attempts' => 1, 'bestScore' => 56, 'passed' => false],
            ['id' => 3, 'first_name' => 'Amit', 'last_name' => 'Kumar', 'email' => 'amit@email.com', 'videoProgress' => 45, 'lastActivity' => '3 days ago', 'attempts' => 0, 'bestScore' => null, 'passed' => false],
            ['id' => 4, 'first_name' => 'Neha', 'last_name' => 'Singh', 'email' => 'neha@email.com', 'videoProgress' => 0, 'lastActivity' => '1 week ago', 'attempts' => 0, 'bestScore' => null, 'passed' => false],
            ['id' => 5, 'first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@email.com', 'videoProgress' => 100, 'lastActivity' => '5 mins ago', 'attempts' => 3, 'bestScore' => 70, 'passed' => true],
        ];

        // Filters
        if ($search) {
            $progress = array_filter(
                $progress,
                fn($p) =>
                str_contains(strtolower($p['first_name']), strtolower($search)) ||
                    str_contains(strtolower($p['last_name']), strtolower($search)) ||
                    str_contains(strtolower($p['email']), strtolower($search))
            );
        }

        // Pagination
        $total = count($progress);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($progress, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($pagedData),
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                    'totalRecords' => $total
                ]
            ]
        ]);
    }
}
