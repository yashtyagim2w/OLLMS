<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Dashboard']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= esc($adminName ?? 'Admin') ?>!</p>
</div>

<?php
// Stats data
$stats = [
    ['value' => $totalUsers ?? 156, 'label' => 'Total Users', 'icon' => 'bi-people', 'color' => 'primary'],
    ['value' => $pendingVerifications ?? 12, 'label' => 'Pending Verifications', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ['value' => $approvedToday ?? 8, 'label' => 'Approved Today', 'icon' => 'bi-patch-check', 'color' => 'success'],
    ['value' => $certificatesIssued ?? 89, 'label' => 'Certificates Issued', 'icon' => 'bi-award', 'color' => 'info'],
];
?>
<?= view('components/stats_grid', ['stats' => $stats]) ?>

<div class="row">
    <!-- Recent Verifications -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="bi bi-person-check me-2"></i>Pending Verifications</h3>
                <a href="/admin/identity-review" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php
                $pendingList = $pendingList ?? [
                    ['name' => 'Rahul Sharma', 'email' => 'rahul@email.com', 'submitted' => '2 hours ago'],
                    ['name' => 'Priya Patel', 'email' => 'priya@email.com', 'submitted' => '4 hours ago'],
                    ['name' => 'Amit Kumar', 'email' => 'amit@email.com', 'submitted' => '1 day ago'],
                ];

                $headers = ['User', 'Submitted', 'Status', 'Action'];
                $rows = [];
                foreach ($pendingList as $item) {
                    $rows[] = [
                        '<div class="d-flex align-items-center gap-2">
                            <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 12px;">' . strtoupper(substr($item['name'], 0, 1)) . '</div>
                            <div>
                                <p class="mb-0 fw-bold">' . esc($item['name']) . '</p>
                                <small class="text-muted">' . esc($item['email']) . '</small>
                            </div>
                        </div>',
                        $item['submitted'],
                        '<span class="badge badge-warning">Pending</span>',
                        '<a href="/admin/identity-review?user=1" class="btn btn-sm btn-primary">Review</a>'
                    ];
                }
                ?>
                <?= view('components/data_table', [
                    'headers' => $headers,
                    'rows' => $rows,
                    'tableClass' => 'table admin-table mb-0',
                    'emptyMessage' => 'No pending verifications'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats & Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="bi bi-graph-up me-2"></i>This Month</h3>
            </div>
            <div class="card-body">
                <?php
                $monthlyStats = [
                    ['label' => 'New Registrations', 'value' => $newRegistrations ?? 45],
                    ['label' => 'Tests Taken', 'value' => $testsTaken ?? 38],
                    ['label' => 'Pass Rate', 'value' => ($passRate ?? 76) . '%', 'color' => 'text-success'],
                    ['label' => 'Videos Watched', 'value' => $videosWatched ?? 234],
                ];
                ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($monthlyStats as $index => $stat): ?>
                        <li class="d-flex justify-content-between py-2 <?= $index < count($monthlyStats) - 1 ? 'border-bottom' : '' ?>">
                            <span><?= $stat['label'] ?></span>
                            <strong class="<?= $stat['color'] ?? '' ?>"><?= $stat['value'] ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php
        $quickActions = [
            ['url' => '/admin/videos', 'icon' => 'bi-upload', 'label' => 'Upload Video'],
            ['url' => '/admin/questions', 'icon' => 'bi-plus-circle', 'label' => 'Add Question'],
            ['url' => '/admin/reports', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Generate Report'],
        ];
        ?>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-lightning me-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body d-grid gap-2">
                <?php foreach ($quickActions as $action): ?>
                    <a href="<?= $action['url'] ?>" class="btn btn-outline-primary">
                        <i class="bi <?= $action['icon'] ?>"></i> <?= $action['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>