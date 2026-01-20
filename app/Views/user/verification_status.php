<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'Verification Status', 'profileVerificationStatus' => $profileVerificationStatus ?? 'COMPLETED', 'documentStatus' => $documentStatus ?? 'PENDING']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Verification Status</li>
    </nav>
    <h1 class="page-title">Verification Status</h1>
    <p class="page-subtitle">Track your identity verification progress</p>
</div>

<?php $documentStatus = $documentStatus ?? 'PENDING'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <!-- Status Display -->
                <div class="text-center py-4">
                    <?php if ($documentStatus === 'PENDING'): ?>
                        <div class="mb-4">
                            <span class="badge badge-warning" style="font-size: 16px; padding: 12px 24px;">
                                <i class="bi bi-hourglass-split me-2"></i>Pending Review
                            </span>
                        </div>
                        <i class="bi bi-clock-history" style="font-size: 80px; color: var(--warning-color);"></i>
                        <h3 class="mt-4">Document Under Review</h3>
                        <p class="text-muted">Your document has been submitted and is being reviewed by our team.</p>
                        <p class="text-muted">This usually takes 1-2 business days.</p>

                    <?php elseif ($documentStatus === 'APPROVED'): ?>
                        <div class="mb-4">
                            <span class="badge badge-success" style="font-size: 16px; padding: 12px 24px;">
                                <i class="bi bi-check-circle me-2"></i>Approved
                            </span>
                        </div>
                        <i class="bi bi-patch-check-fill" style="font-size: 80px; color: var(--success-color);"></i>
                        <h3 class="mt-4">Verification Successful!</h3>
                        <p class="text-muted">Your identity has been verified. You now have full access to the portal.</p>
                        <a href="/videos" class="btn btn-primary mt-3">
                            <i class="bi bi-play-circle"></i> Start Learning
                        </a>

                    <?php elseif ($documentStatus === 'REJECTED'): ?>
                        <div class="mb-4">
                            <span class="badge badge-danger" style="font-size: 16px; padding: 12px 24px;">
                                <i class="bi bi-x-circle me-2"></i>Rejected
                            </span>
                        </div>
                        <i class="bi bi-exclamation-triangle" style="font-size: 80px; color: var(--danger-color);"></i>
                        <h3 class="mt-4">Document Rejected</h3>
                        <p class="text-muted">Unfortunately, your document was not approved.</p>
                    <?php endif; ?>
                </div>

                <?php if ($documentStatus === 'REJECTED'): ?>
                    <!-- Rejection Details -->
                    <div class="alert alert-danger mt-4">
                        <h5><i class="bi bi-info-circle me-2"></i>Rejection Reason</h5>
                        <p class="mb-0"><?= esc($rejectionNote ?? 'The document provided was unclear or did not meet verification requirements. Please upload a clearer copy.') ?></p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="/identity-upload" class="btn btn-primary btn-lg">
                            <i class="bi bi-upload"></i> Upload New Document
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="mt-5 pt-4 border-top">
                    <h5 class="mb-4">Verification Timeline</h5>
                    <div class="timeline">

                        <?php if ($documentStatus !== 'PENDING'): ?>
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <span class="badge bg-<?= $documentStatus === 'APPROVED' ? 'success' : 'danger' ?> rounded-circle p-2">
                                        <i class="bi bi-<?= $documentStatus === 'APPROVED' ? 'check' : 'x' ?>"></i>
                                    </span>
                                </div>
                                <div>
                                    <strong><?= $documentStatus === 'APPROVED' ? 'Document Approved' : 'Document Rejected' ?></strong>
                                    <p class="text-muted mb-0">
                                        <span class="format-datetime" data-datetime="<?= esc($reviewedAt) ?>">
                                            <?= $reviewedAt ? date('M d, Y - h:i A', strtotime($reviewedAt)) : 'N/A' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <span class="badge bg-warning rounded-circle p-2">
                                        <i class="bi bi-hourglass"></i>
                                    </span>
                                </div>
                                <div>
                                    <strong>Under Review</strong>
                                    <p class="text-muted mb-0">Waiting for admin review</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <span class="badge bg-success rounded-circle p-2">
                                    <i class="bi bi-check"></i>
                                </span>
                            </div>
                            <div>
                                <strong>Document Submitted</strong>
                                <p class="text-muted mb-0">
                                    <span class="format-datetime" data-datetime="<?= esc($submittedAt) ?>">
                                        <?= $submittedAt ? date('M d, Y - h:i A', strtotime($submittedAt)) : 'N/A' ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/js/time-utils.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.format-datetime').forEach(el => {
            const datetime = el.getAttribute('data-datetime');
            if (datetime) {
                el.textContent = window.TimeUtils.formatDateTime(datetime);
            }
        });
    });
</script>
<?= $this->endSection() ?>