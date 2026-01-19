<?php

namespace App\Controllers\Admin;

class ReportController extends BaseAdminController
{
    public function index()
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
}
