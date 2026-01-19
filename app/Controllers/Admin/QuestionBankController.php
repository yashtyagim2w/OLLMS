<?php

namespace App\Controllers\Admin;

class QuestionBankController extends BaseAdminController
{
    public function index()
    {
        return view('admin/questions', ['pageTitle' => 'Question Bank']);
    }

    public function getList()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 10;
        $search = $this->request->getGet('search');
        $category = $this->request->getGet('category');

        $questions = [
            ['id' => 1, 'question' => 'What does a red traffic light mean?', 'category' => 'Traffic Signals', 'active' => true],
            ['id' => 2, 'question' => 'When can you overtake from the left?', 'category' => 'Rules of Road', 'active' => true],
            ['id' => 3, 'question' => 'What is the maximum speed limit in a city?', 'category' => 'Speed Limits', 'active' => true],
            ['id' => 4, 'question' => 'What does this sign indicate? (Triangle with kids)', 'category' => 'Traffic Signs', 'active' => true],
            ['id' => 5, 'question' => 'Who has the right of way at a roundabout?', 'category' => 'Rules of Road', 'active' => true],
            ['id' => 6, 'question' => 'What is the legal alcohol limit for driving?', 'category' => 'Safety', 'active' => true],
            ['id' => 7, 'question' => 'How close can you park to a fire hydrant?', 'category' => 'Parking', 'active' => false],
        ];

        if ($search) {
            $questions = array_filter($questions, fn($q) => str_contains(strtolower($q['question']), strtolower($search)));
        }
        if ($category) {
            $questions = array_filter($questions, fn($q) => $q['category'] === $category);
        }

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
}
