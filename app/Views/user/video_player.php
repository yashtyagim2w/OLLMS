<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Video Player', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/videos">Videos</a></li>
        <li class="breadcrumb-item active"><?= esc($videoTitle ?? 'Video') ?></li>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Video Player -->
        <div class="card mb-4">
            <div class="video-container">
                <video id="videoPlayer" controls>
                    <source src="<?= esc($videoUrl ?? '/assets/videos/sample.mp4') ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="card-body">
                <h3 class="mb-2"><?= esc($videoTitle ?? 'Introduction to Traffic Signs') ?></h3>
                <p class="text-muted mb-3">
                    <span class="me-3"><i class="bi bi-clock me-1"></i><?= $duration ?? '10:30' ?></span>
                    <span class="me-3"><i class="bi bi-folder me-1"></i><?= esc($categoryName ?? 'Traffic Rules') ?></span>
                </p>
                <p><?= esc($videoDescription ?? 'Learn about various traffic signs and their meanings. This video covers regulatory, warning, and informational signs.') ?></p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Video Progress</span>
                    <span class="fw-bold" id="progressPercent"><?= $watchedPercent ?? 45 ?>%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" id="progressBar" style="width: <?= $watchedPercent ?? 45 ?>%"></div>
                </div>
                <small class="text-muted mt-2 d-block">
                    Watch at least 90% of the video to mark as complete
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Video Navigation -->
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-list-ul me-2"></i>Up Next</h3>
            </div>
            <div class="card-body p-0">
                <?php
                $nextVideos = $nextVideos ?? [
                    ['id' => 2, 'title' => 'Road Markings Explained', 'duration' => '08:45', 'current' => false],
                    ['id' => 3, 'title' => 'Right of Way Rules', 'duration' => '12:15', 'current' => true],
                    ['id' => 4, 'title' => 'Pre-Drive Safety Check', 'duration' => '07:20', 'current' => false],
                ];
                ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($nextVideos as $video): ?>
                        <li class="p-3 border-bottom <?= $video['current'] ? 'bg-light' : '' ?>">
                            <a href="/video/<?= $video['id'] ?>" class="d-flex gap-3 text-decoration-none">
                                <div class="video-thumbnail d-flex align-items-center justify-content-center" style="width: 60px; height: 40px; flex-shrink: 0;">
                                    <i class="bi bi-play-circle"></i>
                                </div>
                                <div>
                                    <p class="mb-0 <?= $video['current'] ? 'fw-bold text-primary' : 'text-dark' ?>">
                                        <?= $video['current'] ? '▶ ' : '' ?><?= esc($video['title']) ?>
                                    </p>
                                    <small class="text-muted"><?= $video['duration'] ?></small>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <a href="/videos" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Videos
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const video = document.getElementById('videoPlayer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');

    video.addEventListener('timeupdate', function() {
        const percent = Math.round((video.currentTime / video.duration) * 100) || 0;
        progressBar.style.width = percent + '%';
        progressPercent.textContent = percent + '%';

        // Mark as complete at 90%
        if (percent >= 90) {
            progressBar.classList.add('success');
        }
    });
</script>
<?= $this->endSection() ?>