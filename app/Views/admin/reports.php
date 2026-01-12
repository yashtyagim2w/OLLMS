<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Reports & Analytics']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Reports</li>
    </nav>
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-subtitle">View system analytics and generate reports</p>
</div>

<!-- Date Filter -->
<div class="filter-bar mb-4">
    <input type="date" class="form-control" value="2026-01-01">
    <span class="text-muted">to</span>
    <input type="date" class="form-control" value="2026-01-12">
    <button class="btn btn-primary"><i class="bi bi-filter"></i> Apply</button>
    <button class="btn btn-outline-primary ms-auto"><i class="bi bi-download"></i> Export Report</button>
</div>

<!-- Summary Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="bi bi-people"></i></div>
        <div class="stat-content">
            <h3><?= $totalRegistrations ?? 156 ?></h3>
            <p>Total Registrations</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="bi bi-patch-check"></i></div>
        <div class="stat-content">
            <h3><?= $verified ?? 124 ?></h3>
            <p>Verified Users</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="bi bi-pencil-square"></i></div>
        <div class="stat-content">
            <h3><?= $testsTaken ?? 89 ?></h3>
            <p>Tests Taken</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i class="bi bi-award"></i></div>
        <div class="stat-content">
            <h3><?= $certificates ?? 67 ?></h3>
            <p>Certificates Issued</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-graph-up me-2"></i>Registration Trend</h3>
            </div>
            <div class="card-body">
                <div class="chart-container d-flex align-items-center justify-content-center" style="height: 300px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-bar-chart" style="font-size: 48px;"></i>
                        <p class="mt-3">Chart will be rendered here with Chart.js</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-pie-chart me-2"></i>Test Results</h3>
            </div>
            <div class="card-body">
                <div class="chart-container d-flex align-items-center justify-content-center" style="height: 200px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-pie-chart" style="font-size: 48px;"></i>
                        <p class="mt-3">Pie Chart</p>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span><span class="status-dot success"></span>Passed</span>
                        <strong><?= $passPercentage ?? 76 ?>%</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><span class="status-dot danger"></span>Failed</span>
                        <strong><?= 100 - ($passPercentage ?? 76) ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Report Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-table me-2"></i>Monthly Summary</h3>
    </div>
    <div class="card-body p-0">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Registrations</th>
                    <th>Verified</th>
                    <th>Tests Taken</th>
                    <th>Pass Rate</th>
                    <th>Certificates</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $months = ['January 2026', 'December 2025', 'November 2025'];
                foreach ($months as $month):
                ?>
                    <tr>
                        <td><?= $month ?></td>
                        <td><?= rand(30, 60) ?></td>
                        <td><?= rand(25, 50) ?></td>
                        <td><?= rand(20, 40) ?></td>
                        <td><?= rand(65, 85) ?>%</td>
                        <td><?= rand(15, 30) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>