<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Progress Monitoring']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Progress Monitoring</li>
    </nav>
    <h1 class="page-title">Progress Monitoring</h1>
    <p class="page-subtitle">Track user learning progress and test performance</p>
</div>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="bi bi-play-circle"></i></div>
        <div class="stat-content">
            <h3><?= $totalVideoViews ?? 1234 ?></h3>
            <p>Total Video Views</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
        <div class="stat-content">
            <h3><?= $videosCompleted ?? 456 ?></h3>
            <p>Videos Completed</p>
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
        <div class="stat-icon info"><i class="bi bi-percent"></i></div>
        <div class="stat-content">
            <h3><?= $avgScore ?? 72 ?>%</h3>
            <p>Avg Test Score</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filters-container">
    <form id="filtersForm" class="filtersForm">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                id="search_input"
                placeholder="Search user..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="limit" id="limit_filter" class="form-select">
                <option value="10">Show 10</option>
                <option value="25">Show 25</option>
                <option value="50">Show 50</option>
            </select>

            <button type="button" class="btn btn-danger" id="resetBtn">Reset</button>
        </div>
    </form>
</div>

<!-- User Progress Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3><i class="bi bi-people me-2"></i>User Progress</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Video Progress</th>
                        <th>Last Activity</th>
                        <th>Test Attempts</th>
                        <th>Best Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color: var(--gray-500);">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div id="paginationContainer" class="d-flex justify-content-center"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    function renderProgressRow({
        row,
        rowNumber
    }) {
        let statusBadge = '<span class="badge badge-info">In Progress</span>';
        if (row.passed) {
            statusBadge = '<span class="badge badge-success">Passed</span>';
        } else if (row.attempts > 0 && !row.passed) {
            statusBadge = '<span class="badge badge-danger">Failed</span>';
        }

        return `
            <tr>
                <td>${rowNumber}</td>
                <td><span class="fw-medium">${row.first_name}</span></td>
                <td>${row.last_name}</td>
                <td class="text-muted">${row.email}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress" style="width: 100px; height: 6px;">
                            <div class="progress-bar ${row.videoProgress === 100 ? 'bg-success' : 'bg-primary'}" style="width: ${row.videoProgress}%"></div>
                        </div>
                        <small>${row.videoProgress}%</small>
                    </div>
                </td>
                <td class="text-muted">${row.lastActivity}</td>
                <td>${row.attempts}</td>
                <td>${row.bestScore ? row.bestScore + '%' : '-'}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    }

    document.addEventListener('DOMContentLoaded', () => {
        initializeListPage({
            apiEndpoint: '/admin/api/progress',
            renderRow: renderProgressRow,
            columnCount: 9
        });
    });
</script>
<?= $this->endSection() ?>