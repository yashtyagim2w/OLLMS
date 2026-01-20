<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'My Profile', 'profileVerificationStatus' => $profileVerificationStatus ?? 'PENDING', 'documentStatus' => $documentStatus ?? 'NOT_UPLOADED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">My Profile</li>
    </nav>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">View and manage your account information</p>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="profile-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                    <?= strtoupper(substr($firstName ?? 'U', 0, 1)) ?>
                </div>
                <h4><?= esc(($firstName ?? 'User') . ' ' . ($lastName ?? '')) ?></h4>
                <p class="text-muted"><?= esc($email ?? 'user@example.com') ?></p>
                <span class="badge badge-<?= ($profileVerificationStatus ?? 'PENDING') === 'COMPLETED' ? 'success' : 'warning' ?>">
                    <?= ($profileVerificationStatus ?? 'PENDING') === 'COMPLETED' ? 'Verified' : 'Pending Verification' ?>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-shield-check me-2"></i>Account Status</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Email Verified</span>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Documents</span>
                        <span class="badge badge-<?= ($documentStatus ?? 'PENDING') === 'APPROVED' ? 'success' : (($documentStatus ?? 'PENDING') === 'REJECTED' ? 'danger' : 'warning') ?>">
                            <?= ucfirst(strtolower($documentStatus ?? 'Pending')) ?>
                        </span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span>Member Since</span>
                        <span><?= date('M d, Y', strtotime($createdAt ?? 'now')) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="bi bi-person me-2"></i>Personal Information</h3>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="<?= esc($firstName ?? '') ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="<?= esc($lastName ?? '') ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?= esc($email ?? '') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" class="form-control" value="<?= esc($dobFormatted ?? '') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" class="form-control" value="<?= esc($aadharMasked ?? 'XXXX-XXXX-XXXX') ?>" readonly>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($showDocumentHistory ?? false): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-clock-history me-2"></i>Document Submission History</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($documentHistory)): ?>
                        <p class="text-muted text-center py-4">No document submissions yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Submitted On</th>
                                        <th>Aadhaar (Last 4)</th>
                                        <th>Status</th>
                                        <th>Reviewed On</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1;
                                    foreach ($documentHistory as $doc): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td>
                                                <span class="format-datetime" data-datetime="<?= esc($doc['submitted_at']) ?>">
                                                    <?= date('d/m/Y H:i', strtotime($doc['submitted_at'])) ?>
                                                </span>
                                            </td>
                                            <td>XXXX-XXXX-<?= substr($doc['aadhar_number'], -4) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $doc['status'] === 'APPROVED' ? 'success' : ($doc['status'] === 'REJECTED' ? 'danger' : 'warning') ?>">
                                                    <?= esc($doc['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($doc['reviewed_at']): ?>
                                                    <span class="format-datetime" data-datetime="<?= esc($doc['reviewed_at']) ?>">
                                                        <?= date('d/m/Y H:i', strtotime($doc['reviewed_at'])) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($doc['remarks']): ?>
                                                    <div class="table-cell-scrollable">
                                                        <small><?= esc($doc['remarks']) ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/js/time-utils.js"></script>
<script>
    // Format all timestamps using TimeUtils on page load
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