<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'My Profile']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">My Profile</li>
    </nav>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">View and manage your admin account information</p>
</div>

<?php
$firstName = $profile['first_name'] ?? 'RTO';
$lastName = $profile['last_name'] ?? 'Admin';
$email = $user->email ?? '';
$createdAt = $user->created_at ?? 'now';
?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="profile-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                    <?= strtoupper(substr($firstName ?: $email, 0, 1)) ?>
                </div>
                <h4><?= esc($firstName ? "$firstName $lastName" : $email) ?></h4>
                <p class="text-muted"><?= esc($email) ?></p>
                <span class="badge badge-primary">RTO Admin</span>
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
                        <span>Role</span>
                        <span class="badge badge-primary">Admin</span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span>Member Since</span>
                        <span><?= date('d M, Y', strtotime($createdAt)) ?></span>
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
                                <input type="text" class="form-control" value="<?= esc($firstName) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="<?= esc($lastName) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?= esc($email) ?>" readonly>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>