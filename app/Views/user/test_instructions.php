<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Test Instructions', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Test Instructions</li>
    </nav>
    <h1 class="page-title">Test Instructions</h1>
    <p class="page-subtitle">Read carefully before starting the test</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0 text-white"><i class="bi bi-info-circle me-2"></i>Important Instructions</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Please read all instructions carefully before starting the test.</strong>
                </div>

                <ol class="mb-4" style="line-height: 2;">
                    <?php
                    $instructions = $instructions ?? [
                        'The test consists of 25 multiple choice questions.',
                        'You have 30 minutes to complete the test.',
                        'Each question carries equal marks.',
                        'You need to score at least 60% to pass the test.',
                        'Once started, you cannot pause or restart the test.',
                        'Do not refresh the page during the test.',
                        'Each question has only one correct answer.',
                        'You can navigate between questions using Next/Previous buttons.',
                        'You can review and change your answers before submitting.',
                        'After submission, you will see your result immediately.',
                    ];
                    foreach ($instructions as $instruction):
                    ?>
                        <li class="mb-2"><?= esc($instruction) ?></li>
                    <?php endforeach; ?>
                </ol>

                <div class="bg-light p-4 rounded mb-4">
                    <h5><i class="bi bi-clipboard-data me-2"></i>Test Summary</h5>
                    <div class="row mt-3">
                        <div class="col-md-4 text-center mb-3">
                            <div class="display-6 text-primary"><?= $totalQuestions ?? 25 ?></div>
                            <small class="text-muted">Total Questions</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="display-6 text-primary"><?= $testDuration ?? 30 ?></div>
                            <small class="text-muted">Minutes</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="display-6 text-primary"><?= $passingScore ?? 60 ?>%</div>
                            <small class="text-muted">Passing Score</small>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="agreeTerms">
                    <label class="form-check-label" for="agreeTerms">
                        I have read and understood all the instructions. I agree to abide by the examination rules.
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg" id="startTestBtn" disabled onclick="window.location='/test'">
                        <i class="bi bi-pencil-square"></i> Start Test
                    </button>
                    <a href="/dashboard" class="btn btn-outline-secondary">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('agreeTerms').addEventListener('change', function() {
        document.getElementById('startTestBtn').disabled = !this.checked;
    });
</script>
<?= $this->endSection() ?>