<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'My Progress', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">My Progress</li>
    </nav>
    <h1 class="page-title">My Progress</h1>
    <p class="page-subtitle">Track your learning journey</p>
</div>

<!-- Overall Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= $completedVideos ?? 5 ?></h3>
            <p>Videos Completed</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="bi bi-play-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= $inProgressVideos ?? 2 ?></h3>
            <p>In Progress</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="bi bi-clock"></i>
        </div>
        <div class="stat-content">
            <h3><?= $totalWatchTime ?? '2h 15m' ?></h3>
            <p>Total Watch Time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="bi bi-percent"></i>
        </div>
        <div class="stat-content">
            <h3><?= $overallProgress ?? 70 ?>%</h3>
            <p>Overall Progress</p>
        </div>
    </div>
</div>

<!-- Detailed Progress -->
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-bar-chart me-2"></i>Video Progress Details</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Video Title</th>
                        <th>Category</th>
                        <th style="width: 200px;">Progress</th>
                        <th>Last Watched</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $progressData = $progressData ?? [
                        ['title' => 'Introduction to Traffic Signs', 'category' => 'Traffic Rules', 'progress' => 100, 'lastWatched' => '2 hours ago', 'status' => 'COMPLETED'],
                        ['title' => 'Road Markings Explained', 'category' => 'Traffic Rules', 'progress' => 100, 'lastWatched' => '1 day ago', 'status' => 'COMPLETED'],
                        ['title' => 'Right of Way Rules', 'category' => 'Traffic Rules', 'progress' => 65, 'lastWatched' => '3 hours ago', 'status' => 'IN_PROGRESS'],
                        ['title' => 'Pre-Drive Safety Check', 'category' => 'Vehicle Safety', 'progress' => 30, 'lastWatched' => 'Yesterday', 'status' => 'IN_PROGRESS'],
                        ['title' => 'Seat Belt & Mirror Adjustment', 'category' => 'Vehicle Safety', 'progress' => 0, 'lastWatched' => '-', 'status' => 'NOT_STARTED'],
                    ];
                    foreach ($progressData as $item):
                    ?>
                        <tr>
                            <td><?= esc($item['title']) ?></td>
                            <td><span class="badge badge-primary"><?= esc($item['category']) ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar <?= $item['progress'] >= 90 ? 'success' : '' ?>" style="width: <?= $item['progress'] ?>%"></div>
                                    </div>
                                    <span style="width: 40px;"><?= $item['progress'] ?>%</span>
                                </div>
                            </td>
                            <td class="text-muted"><?= $item['lastWatched'] ?></td>
                            <td>
                                <?php if ($item['status'] === 'COMPLETED'): ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php elseif ($item['status'] === 'IN_PROGRESS'): ?>
                                    <span class="badge badge-warning">In Progress</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Not Started</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>