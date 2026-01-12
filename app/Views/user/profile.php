<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'My Profile', 'verificationStatus' => $verificationStatus ?? 'APPROVED']) ?>

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
                <span class="badge badge-<?= ($verificationStatus ?? 'PENDING') === 'APPROVED' ? 'success' : 'warning' ?>">
                    <?= ($verificationStatus ?? 'PENDING') === 'APPROVED' ? 'Verified' : 'Pending Verification' ?>
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
                        <span class="badge badge-<?= ($documentStatus ?? 'PENDING') === 'APPROVED' ? 'success' : 'warning' ?>">
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
                        <input type="date" class="form-control" value="<?= esc($dob ?? '') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" class="form-control" value="<?= esc($aadharMasked ?? 'XXXX-XXXX-XXXX') ?>" readonly>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-key me-2"></i>Change Password</h3>
            </div>
            <div class="card-body">
                <form id="changePasswordForm">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" placeholder="Enter current password">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" placeholder="Enter new password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>