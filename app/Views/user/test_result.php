<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Test Result', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Test Result</li>
    </nav>
</div>

<?php
$score = $score ?? 72;
$passed = $score >= 60;
$totalQuestions = $totalQuestions ?? 25;
$correctAnswers = $correctAnswers ?? 18;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <?php if ($passed): ?>
                    <div class="mb-4">
                        <i class="bi bi-trophy-fill" style="font-size: 80px; color: var(--secondary-color);"></i>
                    </div>
                    <h2 class="text-success mb-3">Congratulations! You Passed!</h2>
                    <p class="lead text-muted mb-4">You have successfully completed the Learner's License Test.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="bi bi-emoji-frown" style="font-size: 80px; color: var(--danger-color);"></i>
                    </div>
                    <h2 class="text-danger mb-3">Unfortunately, You Did Not Pass</h2>
                    <p class="lead text-muted mb-4">Don't worry! You can retake the test after reviewing the material.</p>
                <?php endif; ?>

                <!-- Score Display -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8">
                        <div class="bg-light rounded-3 p-4">
                            <div class="display-3 fw-bold <?= $passed ? 'text-success' : 'text-danger' ?>">
                                <?= $score ?>%
                            </div>
                            <p class="text-muted mb-0">Your Score</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row text-center mb-4">
                    <div class="col-4">
                        <h4><?= $totalQuestions ?></h4>
                        <small class="text-muted">Total Questions</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success"><?= $correctAnswers ?></h4>
                        <small class="text-muted">Correct</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-danger"><?= $totalQuestions - $correctAnswers ?></h4>
                        <small class="text-muted">Incorrect</small>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-center gap-3">
                    <?php if ($passed): ?>
                        <a href="/certificate" class="btn btn-primary btn-lg">
                            <i class="bi bi-award"></i> View Certificate
                        </a>
                    <?php else: ?>
                        <a href="/videos" class="btn btn-primary btn-lg">
                            <i class="bi bi-play-circle"></i> Review Videos
                        </a>
                        <a href="/test-instructions" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-arrow-repeat"></i> Retake Test
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Test Summary Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3><i class="bi bi-list-check me-2"></i>Test Summary</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td>Test Date</td>
                        <td class="text-end fw-bold"><?= date('M d, Y - h:i A', strtotime($testDate ?? 'now')) ?></td>
                    </tr>
                    <tr>
                        <td>Time Taken</td>
                        <td class="text-end fw-bold"><?= $timeTaken ?? '22:45' ?> minutes</td>
                    </tr>
                    <tr>
                        <td>Passing Score</td>
                        <td class="text-end fw-bold">60%</td>
                    </tr>
                    <tr>
                        <td>Your Score</td>
                        <td class="text-end fw-bold <?= $passed ? 'text-success' : 'text-danger' ?>"><?= $score ?>%</td>
                    </tr>
                    <tr>
                        <td>Result</td>
                        <td class="text-end">
                            <span class="badge badge-<?= $passed ? 'success' : 'danger' ?>" style="font-size: 14px;">
                                <?= $passed ? 'PASSED' : 'FAILED' ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>