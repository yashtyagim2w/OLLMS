<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Training Videos', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Training Videos</li>
    </nav>
    <h1 class="page-title">Training Videos</h1>
    <p class="page-subtitle">Complete all videos in sequence to unlock the test</p>
</div>

<!-- Progress Overview -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Overall Progress</h5>
            <span class="fw-bold"><?= $completedVideos ?? 3 ?>/<?= $totalVideos ?? 10 ?> Videos Completed</span>
        </div>
        <div class="progress" style="height: 10px;">
            <div class="progress-bar success" style="width: <?= ($completedVideos ?? 3) / ($totalVideos ?? 10) * 100 ?>%"></div>
        </div>
    </div>
</div>

<!-- Video Categories -->
<?php
// Mock data with sequential unlock logic
$categories = $categories ?? [
    ['id' => 1, 'name' => 'Traffic Rules & Regulations', 'videos' => [
        ['id' => 1, 'title' => 'Introduction to Traffic Signs', 'duration' => '10:30', 'status' => 'COMPLETED'],
        ['id' => 2, 'title' => 'Road Markings Explained', 'duration' => '08:45', 'status' => 'COMPLETED'],
        ['id' => 3, 'title' => 'Right of Way Rules', 'duration' => '12:15', 'status' => 'IN_PROGRESS'],
    ]],
    ['id' => 2, 'name' => 'Vehicle Safety', 'videos' => [
        ['id' => 4, 'title' => 'Pre-Drive Safety Check', 'duration' => '07:20', 'status' => 'NOT_STARTED'],
        ['id' => 5, 'title' => 'Seat Belt & Mirror Adjustment', 'duration' => '06:00', 'status' => 'NOT_STARTED'],
    ]],
    ['id' => 3, 'name' => 'Driving Techniques', 'videos' => [
        ['id' => 6, 'title' => 'Steering Control Basics', 'duration' => '11:30', 'status' => 'NOT_STARTED'],
        ['id' => 7, 'title' => 'Gear Shifting Techniques', 'duration' => '09:45', 'status' => 'NOT_STARTED'],
    ]],
];

// Flatten all videos to determine sequential order
$allVideos = [];
foreach ($categories as $cat) {
    foreach ($cat['videos'] as $video) {
        $allVideos[] = $video;
    }
}

// Determine which video index is unlocked (first non-completed)
$unlockedIndex = 0;
foreach ($allVideos as $idx => $video) {
    if ($video['status'] === 'COMPLETED') {
        $unlockedIndex = $idx + 1; // Next video is unlocked
    } elseif ($video['status'] === 'IN_PROGRESS') {
        $unlockedIndex = $idx; // Current video is unlocked
        break;
    } else {
        break;
    }
}

// Create a map of video IDs to their unlock status
$videoUnlockMap = [];
foreach ($allVideos as $idx => $video) {
    $videoUnlockMap[$video['id']] = $idx <= $unlockedIndex;
}
?>

<?php foreach ($categories as $category): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="bi bi-collection-play me-2"></i><?= esc($category['name']) ?></h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Video Title</th>
                            <th style="width: 100px;">Duration</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category['videos'] as $index => $video): ?>
                            <?php
                            $isUnlocked = $videoUnlockMap[$video['id']] ?? false;
                            $isLocked = !$isUnlocked && $video['status'] === 'NOT_STARTED';
                            ?>
                            <tr class="<?= $isLocked ? 'table-secondary' : '' ?>">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="video-thumbnail d-flex align-items-center justify-content-center" style="width: 80px; height: 45px; <?= $isLocked ? 'opacity: 0.5;' : '' ?>">
                                            <?php if ($isLocked): ?>
                                                <i class="bi bi-lock-fill" style="font-size: 24px; color: var(--gray-400);"></i>
                                            <?php else: ?>
                                                <i class="bi bi-play-circle" style="font-size: 24px; color: var(--gray-500);"></i>
                                            <?php endif; ?>
                                        </div>
                                        <span class="<?= $isLocked ? 'text-muted' : '' ?>"><?= esc($video['title']) ?></span>
                                    </div>
                                </td>
                                <td class="<?= $isLocked ? 'text-muted' : '' ?>"><i class="bi bi-clock me-1"></i><?= $video['duration'] ?></td>
                                <td>
                                    <?php if ($video['status'] === 'COMPLETED'): ?>
                                        <span class="badge badge-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                                    <?php elseif ($video['status'] === 'IN_PROGRESS'): ?>
                                        <span class="badge badge-warning"><i class="bi bi-play me-1"></i>In Progress</span>
                                    <?php elseif ($isLocked): ?>
                                        <span class="badge badge-secondary"><i class="bi bi-lock me-1"></i>Locked</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><i class="bi bi-circle me-1"></i>Not Started</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Complete previous video to unlock">
                                            <i class="bi bi-lock"></i> Locked
                                        </button>
                                    <?php else: ?>
                                        <a href="/video/<?= $video['id'] ?>" class="btn btn-sm btn-primary">
                                            <?= $video['status'] === 'COMPLETED' ? 'Rewatch' : ($video['status'] === 'IN_PROGRESS' ? 'Continue' : 'Watch') ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Note:</strong> Videos must be completed in sequence. Complete the current video to unlock the next one.
</div>
<?= $this->endSection() ?>