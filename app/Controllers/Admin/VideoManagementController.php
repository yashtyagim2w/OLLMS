<?php

namespace App\Controllers\Admin;

class VideoManagementController extends BaseAdminController
{
    public function index()
    {
        return view('admin/videos', ['pageTitle' => 'Video Management']);
    }

    public function getList()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('active_status');
        $category = $this->request->getGet('category');

        $videos = [
            ['id' => 1, 'title' => 'Introduction to Traffic Signs', 'category' => 'Traffic Rules', 'duration' => '10:30', 'active' => true, 'url' => '...'],
            ['id' => 2, 'title' => 'Road Mark

ings Explained', 'category' => 'Traffic Rules', 'duration' => '08:45', 'active' => true, 'url' => '...'],
            ['id' => 3, 'title' => 'Pre-Drive Safety Check', 'category' => 'Vehicle Safety', 'duration' => '07:20', 'active' => false, 'url' => '...'],
            ['id' => 4, 'title' => 'Defensive Driving Basics', 'category' => 'Traffic Rules', 'duration' => '12:00', 'active' => true, 'url' => '...'],
            ['id' => 5, 'title' => 'Changing Lanes Safely', 'category' => 'Driving Techniques', 'duration' => '05:30', 'active' => true, 'url' => '...'],
            ['id' => 6, 'title' => 'Parking Maneuvers', 'category' => 'Driving Techniques', 'duration' => '15:15', 'active' => true, 'url' => '...'],
            ['id' => 7, 'title' => 'Night Driving Tips', 'category' => 'Refresher', 'duration' => '09:00', 'active' => false, 'url' => '...'],
        ];

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
}
