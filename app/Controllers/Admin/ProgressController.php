<?php

namespace App\Controllers\Admin;

class ProgressController extends BaseAdminController
{
    public function index()
    {
        return view('admin/progress', [
            'pageTitle' => 'Progress Monitoring',
            'totalVideoViews' => 1234,
            'videosCompleted' => 456,
            'testsTaken' => 89,
            'avgScore' => 72
        ]);
    }

    public function getUserProgress()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');

        $progress = [
            ['id' => 1, 'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul@email.com', 'videoProgress' => 100, 'lastActivity' => '2 hours ago', 'attempts' => 2, 'bestScore' => 84, 'passed' => true],
            ['id' => 2, 'first_name' => 'Priya', 'last_name' => 'Patel', 'email' => 'priya@email.com', 'videoProgress' => 80, 'lastActivity' => '1 day ago', 'attempts' => 1, 'bestScore' => 56, 'passed' => false],
            ['id' => 3, 'first_name' => 'Amit', 'last_name' => 'Kumar', 'email' => 'amit@email.com', 'videoProgress' => 45, 'lastActivity' => '3 days ago', 'attempts' => 0, 'bestScore' => null, 'passed' => false],
            ['id' => 4, 'first_name' => 'Neha', 'last_name' => 'Singh', 'email' => 'neha@email.com', 'videoProgress' => 0, 'lastActivity' => '1 week ago', 'attempts' => 0, 'bestScore' => null, 'passed' => false],
            ['id' => 5, 'first_name' => 'Vikram', 'last_name' => 'Singh', 'email' => 'vikram@email.com', 'videoProgress' => 100, 'lastActivity' => '5 mins ago', 'attempts' => 3, 'bestScore' => 70, 'passed' => true],
        ];

        if ($search) {
            $progress = array_filter(
                $progress,
                fn($p) =>
                str_contains(strtolower($p['first_name']), strtolower($search)) ||
                    str_contains(strtolower($p['last_name']), strtolower($search)) ||
                    str_contains(strtolower($p['email']), strtolower($search))
            );
        }

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
