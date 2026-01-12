<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Dashboard', 'verificationStatus' => $verificationStatus ?? 'PENDING']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </nav>
    <h1 class="page-title">Welcome, <?= esc($userName ?? 'User') ?>!</h1>
    <p class="page-subtitle">Track your learner's license application progress</p>
</div>

<?php
$verificationStatus = $verificationStatus ?? 'PENDING';
$documentStatus = $documentStatus ?? 'NOT_UPLOADED';

// Application steps data
$applicationSteps = [
    ['label' => 'Account Created', 'status' => 'completed'],
    ['label' => 'Email Verified', 'status' => 'completed'],
    ['label' => 'Upload Documents', 'status' => $documentStatus === 'NOT_UPLOADED' ? 'current' : 'completed'],
    ['label' => 'Admin Verification', 'status' => $documentStatus === 'PENDING' ? 'current' : ($documentStatus === 'APPROVED' ? 'completed' : 'pending')],
    ['label' => 'Watch Training Videos', 'status' => $documentStatus === 'APPROVED' ? 'current' : 'pending'],
    ['label' => 'Take Test & Get Certificate', 'status' => 'pending'],
];

// Tips for document upload
$uploadTips = [
    'Ensure document is clear and readable',
    'Upload PDF, JPG or PNG format',
    'Make sure all corners are visible',
    'File size should be under 5MB',
];
?>

<?php if ($verificationStatus === 'PENDING'): ?>
    <!-- Not yet verified - Show verification required notice -->
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <div>
            <strong>Document Verification Required</strong>
            <p class="mb-0">Please upload your Aadhar card to proceed with the application.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-upload me-2"></i>Upload Identity Document</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Upload your Aadhar card for identity verification. This is mandatory to proceed.</p>
                    <a href="/identity-upload" class="btn btn-primary btn-lg">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Upload Aadhar Card
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-list-check me-2"></i>Application Steps</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($applicationSteps as $step): ?>
                            <?php
                            $iconClass = match ($step['status']) {
                                'completed' => 'bi-check-circle-fill text-success',
                                'current' => 'bi-circle text-warning',
                                default => 'bi-circle'
                            };
                            $textClass = $step['status'] === 'pending' ? 'text-muted' : '';
                            ?>
                            <li class="mb-3 d-flex align-items-center <?= $textClass ?>">
                                <i class="bi <?= $iconClass ?> me-2"></i>
                                <?= $step['status'] === 'current' ? '<strong>' . esc($step['label']) . '</strong>' : '<span>' . esc($step['label']) . '</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($documentStatus === 'PENDING'): ?>
    <!-- Document uploaded, waiting for verification -->
    <div class="alert alert-info">
        <i class="bi bi-hourglass-split"></i>
        <div>
            <strong>Verification In Progress</strong>
            <p class="mb-0">Your documents are being reviewed by our team. This usually takes 1-2 business days.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-clock-history" style="font-size: 64px; color: var(--info-color);"></i>
                    <h3 class="mt-4">Pending Verification</h3>
                    <p class="text-muted">Your application is under review. We'll notify you once it's approved.</p>
                    <a href="/verification-status" class="btn btn-outline-primary">View Status</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-list-check me-2"></i>Application Steps</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($applicationSteps as $step): ?>
                            <?php
                            $iconClass = match ($step['status']) {
                                'completed' => 'bi-check-circle-fill text-success',
                                'current' => 'bi-circle text-warning',
                                default => 'bi-circle'
                            };
                            $textClass = $step['status'] === 'pending' ? 'text-muted' : '';
                            ?>
                            <li class="mb-3 d-flex align-items-center <?= $textClass ?>">
                                <i class="bi <?= $iconClass ?> me-2"></i>
                                <?= $step['status'] === 'current' ? '<strong>' . esc($step['label']) . '</strong>' : '<span>' . esc($step['label']) . '</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($documentStatus === 'REJECTED'): ?>
    <!-- Document rejected -->
    <div class="alert alert-danger">
        <i class="bi bi-x-circle"></i>
        <div>
            <strong>Document Rejected</strong>
            <p class="mb-0"><?= esc($rejectionNote ?? 'Your document was rejected. Please upload again.') ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-exclamation-triangle me-2"></i>Action Required</h3>
                </div>
                <div class="card-body">
                    <h5>Re-upload Your Document</h5>
                    <p class="text-muted">Please review the rejection reason above and upload a valid document.</p>
                    <a href="/identity-upload" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Upload New Document
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-lightbulb me-2"></i>Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($uploadTips as $tip): ?>
                            <li class="mb-3">
                                <i class="bi bi-check text-success me-2"></i>
                                <?= esc($tip) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Fully verified - Show full dashboard -->
    <?php
    $stats = [
        ['value' => 'Verified', 'label' => 'Account Status', 'icon' => 'bi-patch-check', 'color' => 'success'],
        ['value' => ($videosCompleted ?? 0) . '/' . ($totalVideos ?? 10), 'label' => 'Videos Completed', 'icon' => 'bi-play-circle', 'color' => 'primary'],
        ['value' => $testAttempts ?? 0, 'label' => 'Test Attempts', 'icon' => 'bi-pencil-square', 'color' => 'warning'],
        ['value' => ($testResult ?? 'NONE') === 'PASS' ? 'Passed' : 'Pending', 'label' => 'Test Result', 'icon' => 'bi-award', 'color' => ($testResult ?? 'NONE') === 'PASS' ? 'success' : 'info'],
    ];
    ?>
    <?= view('components/stats_grid', ['stats' => $stats]) ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="bi bi-graph-up me-2"></i>Learning Progress</h3>
                    <a href="/videos" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Overall Progress</span>
                            <span class="fw-bold"><?= $progressPercent ?? 0 ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar success" style="width: <?= $progressPercent ?? 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $quickActions = [
                ['url' => '/videos', 'icon' => 'bi-play-circle', 'label' => 'Continue Learning'],
                ['url' => '/test-instructions', 'icon' => 'bi-pencil-square', 'label' => 'Take Test'],
                ['url' => '/certificate', 'icon' => 'bi-award', 'label' => 'My Certificate'],
            ];
            ?>
            <?= view('components/quick_actions', ['actions' => $quickActions]) ?>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-info-circle me-2"></i>Important Notes</h3>
                </div>
                <div class="card-body">
                    <?php
                    $notes = [
                        'Complete all training videos',
                        'Score at least 60% to pass',
                        'Download certificate after passing',
                        'Visit RTO with certificate',
                    ];
                    ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($notes as $index => $note): ?>
                            <li class="<?= $index < count($notes) - 1 ? 'mb-3 pb-3 border-bottom' : '' ?>">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <?= esc($note) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>