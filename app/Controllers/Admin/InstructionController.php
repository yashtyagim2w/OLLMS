<?php

namespace App\Controllers\Admin;

class InstructionController extends BaseAdminController
{
    public function index()
    {
        return view('admin/instructions', ['pageTitle' => 'Test Instructions']);
    }

    public function getList()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');

        $instructions = [
            ['id' => 1, 'title' => 'General Test Guidelines', 'content_preview' => 'All candidates must arrive 30 minutes before...', 'order' => 1, 'active' => true],
            ['id' => 2, 'title' => 'Computer System Usage', 'content_preview' => 'Do not press the refresh button during the test...', 'order' => 2, 'active' => true],
            ['id' => 3, 'title' => 'Passing Criteria', 'content_preview' => 'You must answer at least 12 out of 20 questions corre...', 'order' => 3, 'active' => true],
            ['id' => 4, 'title' => 'Malpractice Warning', 'content_preview' => 'Using mobile phones or talking to others is strict...', 'order' => 4, 'active' => true],
            ['id' => 5, 'title' => 'Emergency Procedures', 'content_preview' => 'In case of fire alarm, exit calmly through...', 'order' => 5, 'active' => false],
        ];

        if ($search) {
            $instructions = array_filter($instructions, fn($i) => str_contains(strtolower($i['title']), strtolower($search)));
        }

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
}
