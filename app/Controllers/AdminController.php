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
        return view('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'adminName' => 'Admin',
            'totalUsers' => 156,
            'pendingVerifications' => 12,
            'approvedToday' => 8,
            'certificatesIssued' => 89,
            'newRegistrations' => 45,
            'testsTaken' => 38,
            'passRate' => 76,
            'videosWatched' => 234
        ]);
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
        return view('admin/identity_review', [
            'pageTitle' => 'Identity Verification',
            'pendingCount' => 12
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
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        // Mock Data
        $allData = [
            ['id' => 1, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@email.com', 'submitted_at' => '2026-01-12 10:30:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc1.pdf'],
            ['id' => 2, 'first_name' => 'Priya', 'last_name' => 'Patel', 'email' => 'priya@email.com', 'submitted_at' => '2026-01-12 09:15:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc2.pdf'],
            ['id' => 3, 'first_name' => 'Amit', 'last_name' => 'Kumar', 'email' => 'amit@email.com', 'submitted_at' => '2026-01-11 14:20:00', 'status' => 'REJECTED', 'document_url' => '/uploads/doc3.pdf'],
            ['id' => 4, 'first_name' => 'Sneha', 'last_name' => 'Gupta', 'email' => 'sneha@email.com', 'submitted_at' => '2026-01-11 11:00:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc4.pdf'],
            ['id' => 5, 'first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@email.com', 'submitted_at' => '2026-01-10 16:45:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc5.pdf'],
            ['id' => 6, 'first_name' => 'Anjali', 'last_name' => 'Roy', 'email' => 'anjali@email.com', 'submitted_at' => '2026-01-10 13:10:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc6.pdf'],
            ['id' => 7, 'first_name' => 'Mohammed', 'last_name' => 'Ali', 'email' => 'ali@email.com', 'submitted_at' => '2026-01-09 18:00:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc7.pdf'],
            ['id' => 8, 'first_name' => 'Kavita', 'last_name' => 'Krishnan', 'email' => 'kavita@email.com', 'submitted_at' => '2026-01-09 15:30:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc8.pdf'],
            ['id' => 9, 'first_name' => 'Arjun', 'last_name' => 'Reddy', 'email' => 'arjun@email.com', 'submitted_at' => '2026-01-08 12:20:00', 'status' => 'REJECTED', 'document_url' => '/uploads/doc9.pdf'],
            ['id' => 10, 'first_name' => 'Meera', 'last_name' => 'Nair', 'email' => 'meera@email.com', 'submitted_at' => '2026-01-08 10:00:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc10.pdf'],
            ['id' => 11, 'first_name' => 'Suresh', 'last_name' => 'Babu', 'email' => 'suresh@email.com', 'submitted_at' => '2026-01-07 14:45:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc11.pdf'],
            ['id' => 12, 'first_name' => 'Divya', 'last_name' => 'Thomas', 'email' => 'divya@email.com', 'submitted_at' => '2026-01-07 11:30:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc12.pdf'],
        ];

        // Filter by status
        if ($status) {
            $allData = array_filter($allData, fn($item) => $item['status'] === $status);
        }

        // Filter by search
        if ($search) {
            $search = strtolower($search);
            $allData = array_filter(
                $allData,
                fn($item) =>
                str_contains(strtolower($item['first_name']), $search) ||
                    str_contains(strtolower($item['last_name']), $search) ||
                    str_contains(strtolower($item['email']), $search)
            );
        }

        // Sort by submitted_at desc
        usort($allData, fn($a, $b) => strtotime($b['submitted_at']) - strtotime($a['submitted_at']));

        // Pagination
        $total = count($allData);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $pagedData = array_slice($allData, $offset, $limit);

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
     * Get Identity Review Detail API
     */
    public function getIdentityReviewDetail($id)
    {
        // Mock Data - same as list but find by ID
        $allData = [
            ['id' => 1, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@email.com', 'phone' => '+91 9876543210', 'dob' => '1998-03-15', 'submitted_at' => '2026-01-12 10:30:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc1.pdf', 'document_type' => 'Aadhar Card'],
            ['id' => 2, 'first_name' => 'Priya', 'last_name' => 'Patel', 'email' => 'priya@email.com', 'phone' => '+91 9876543211', 'dob' => '2000-07-22', 'submitted_at' => '2026-01-12 09:15:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc2.pdf', 'document_type' => 'PAN Card'],
            ['id' => 3, 'first_name' => 'Amit', 'last_name' => 'Kumar', 'email' => 'amit@email.com', 'phone' => '+91 9876543212', 'dob' => '1999-11-08', 'submitted_at' => '2026-01-11 14:20:00', 'status' => 'REJECTED', 'document_url' => '/uploads/doc3.pdf', 'document_type' => 'Driving License'],
            ['id' => 4, 'first_name' => 'Sneha', 'last_name' => 'Gupta', 'email' => 'sneha@email.com', 'phone' => '+91 9876543213', 'dob' => '2001-02-14', 'submitted_at' => '2026-01-11 11:00:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc4.pdf', 'document_type' => 'Aadhar Card'],
            ['id' => 5, 'first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@email.com', 'phone' => '+91 9876543214', 'dob' => '1997-05-10', 'submitted_at' => '2026-01-10 16:45:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc5.pdf', 'document_type' => 'Voter ID'],
            ['id' => 6, 'first_name' => 'Anjali', 'last_name' => 'Roy', 'email' => 'anjali@email.com', 'phone' => '+91 9876543215', 'dob' => '2002-09-01', 'submitted_at' => '2026-01-10 13:10:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc6.pdf', 'document_type' => 'PAN Card'],
            ['id' => 7, 'first_name' => 'Mohammed', 'last_name' => 'Ali', 'email' => 'ali@email.com', 'phone' => '+91 9876543216', 'dob' => '1995-12-30', 'submitted_at' => '2026-01-09 18:00:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc7.pdf', 'document_type' => 'Aadhar Card'],
            ['id' => 8, 'first_name' => 'Kavita', 'last_name' => 'Krishnan', 'email' => 'kavita@email.com', 'phone' => '+91 9876543217', 'dob' => '1998-08-15', 'submitted_at' => '2026-01-09 15:30:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc8.pdf', 'document_type' => 'Driving License'],
            ['id' => 9, 'first_name' => 'Arjun', 'last_name' => 'Reddy', 'email' => 'arjun@email.com', 'phone' => '+91 9876543218', 'dob' => '2000-01-26', 'submitted_at' => '2026-01-08 12:20:00', 'status' => 'REJECTED', 'document_url' => '/uploads/doc9.pdf', 'document_type' => 'PAN Card'],
            ['id' => 10, 'first_name' => 'Meera', 'last_name' => 'Nair', 'email' => 'meera@email.com', 'phone' => '+91 9876543219', 'dob' => '1996-06-12', 'submitted_at' => '2026-01-08 10:00:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc10.pdf', 'document_type' => 'Voter ID'],
            ['id' => 11, 'first_name' => 'Suresh', 'last_name' => 'Babu', 'email' => 'suresh@email.com', 'phone' => '+91 9876543220', 'dob' => '1990-03-03', 'submitted_at' => '2026-01-07 14:45:00', 'status' => 'APPROVED', 'document_url' => '/uploads/doc11.pdf', 'document_type' => 'Aadhar Card'],
            ['id' => 12, 'first_name' => 'Divya', 'last_name' => 'Thomas', 'email' => 'divya@email.com', 'phone' => '+91 9876543221', 'dob' => '2001-11-20', 'submitted_at' => '2026-01-07 11:30:00', 'status' => 'PENDING', 'document_url' => '/uploads/doc12.pdf', 'document_type' => 'PAN Card'],
        ];

        $user = null;
        foreach ($allData as $item) {
            if ($item['id'] == $id) {
                $user = $item;
                break;
            }
        }

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get Users API
     */
    public function getUsers()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $status = $this->request->getGet('status');
        $testStatus = $this->request->getGet('test_status');
        $search = $this->request->getGet('search');

        // Mock Data
        $users = [
            ['id' => 1, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@email.com', 'dob' => '1998-03-15', 'docStatus' => 'APPROVED', 'videoProgress' => 100, 'testResult' => 'PASS', 'hasCert' => true],
            ['id' => 2, 'first_name' => 'Priya', 'last_name' => 'Patel', 'email' => 'priya@email.com', 'dob' => '2000-07-22', 'docStatus' => 'APPROVED', 'videoProgress' => 80, 'testResult' => 'FAIL', 'hasCert' => false],
            ['id' => 3, 'first_name' => 'Amit', 'last_name' => 'Kumar', 'email' => 'amit@email.com', 'dob' => '1999-11-08', 'docStatus' => 'PENDING', 'videoProgress' => 0, 'testResult' => null, 'hasCert' => false],
            ['id' => 4, 'first_name' => 'Neha', 'last_name' => 'Singh', 'email' => 'neha@email.com', 'dob' => '2001-02-14', 'docStatus' => 'REJECTED', 'videoProgress' => 0, 'testResult' => null, 'hasCert' => false],
            ['id' => 5, 'first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@email.com', 'dob' => '1997-05-10', 'docStatus' => 'APPROVED', 'videoProgress' => 100, 'testResult' => 'PASS', 'hasCert' => true],
            ['id' => 6, 'first_name' => 'Anjali', 'last_name' => 'Roy', 'email' => 'anjali@email.com', 'dob' => '2002-09-01', 'docStatus' => 'PENDING', 'videoProgress' => 25, 'testResult' => null, 'hasCert' => false],
            ['id' => 7, 'first_name' => 'Mohammed', 'last_name' => 'Ali', 'email' => 'ali@email.com', 'dob' => '1995-12-30', 'docStatus' => 'APPROVED', 'videoProgress' => 100, 'testResult' => 'PASS', 'hasCert' => true],
            ['id' => 8, 'first_name' => 'Kavita', 'last_name' => 'Krishnan', 'email' => 'kavita@email.com', 'dob' => '1998-08-15', 'docStatus' => 'APPROVED', 'videoProgress' => 60, 'testResult' => null, 'hasCert' => false],
            ['id' => 9, 'first_name' => 'Arjun', 'last_name' => 'Reddy', 'email' => 'arjun@email.com', 'dob' => '2000-01-26', 'docStatus' => 'REJECTED', 'videoProgress' => 0, 'testResult' => null, 'hasCert' => false],
            ['id' => 10, 'first_name' => 'Meera', 'last_name' => 'Nair', 'email' => 'meera@email.com', 'dob' => '1996-06-12', 'docStatus' => 'PENDING', 'videoProgress' => 45, 'testResult' => null, 'hasCert' => false],
            ['id' => 11, 'first_name' => 'Suresh', 'last_name' => 'Babu', 'email' => 'suresh@email.com', 'dob' => '1990-03-03', 'docStatus' => 'APPROVED', 'videoProgress' => 100, 'testResult' => 'FAIL', 'hasCert' => false],
            ['id' => 12, 'first_name' => 'Divya', 'last_name' => 'Thomas', 'email' => 'divya@email.com', 'dob' => '2001-11-20', 'docStatus' => 'APPROVED', 'videoProgress' => 90, 'testResult' => null, 'hasCert' => false],
        ];

        // Filtering
        if ($status) {
            $users = array_filter($users, fn($item) => strtolower($item['docStatus']) === strtolower($status));
        }
        if ($testStatus) {
            $users = array_filter($users, function ($item) use ($testStatus) {
                if ($testStatus === 'passed') return $item['testResult'] === 'PASS';
                if ($testStatus === 'failed') return $item['testResult'] === 'FAIL';
                if ($testStatus === 'not_taken') return $item['testResult'] === null;
                return true;
            });
        }
        if ($search) {
            $users = array_filter(
                $users,
                fn($item) =>
                str_contains(strtolower($item['first_name']), strtolower($search)) ||
                    str_contains(strtolower($item['last_name']), strtolower($search)) ||
                    str_contains(strtolower($item['email']), strtolower($search))
            );
        }

        // Pagination
        $total = count($users);
        $totalPages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $users = array_slice($users, $offset, $limit);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'data' => array_values($users),
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
