<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-key"></i>
        </div>
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter your new password</p>
    </div>

    <div class="auth-body">
        <!-- Hidden elements for SWAL to pick up -->
        <?php if (session('error')): ?>
            <div id="session-error" style="display:none;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (session('message')): ?>
            <div id="session-success" style="display:none;"><?= esc(session('message')) ?></div>
        <?php endif; ?>

        <?php if (isset($showForm) && $showForm): ?>
            <!-- Reset Password Form -->
            <form action="/reset-password" method="post" id="resetPasswordForm">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= esc($email ?? '') ?>" disabled>
                    <div class="form-text">Resetting password for this email</div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                    <?php helper('validation'); ?>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Enter new password (min <?= PASSWORD_MIN_LENGTH ?> characters)"
                            minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                            title="<?= get_validation_message('password') ?>"
                            required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="passwordError" class="invalid-feedback"></div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                            placeholder="Confirm new password"
                            minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                            required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirm')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="confirmPasswordError" class="invalid-feedback"></div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="resetBtn">
                        <i class="bi bi-check-circle me-2"></i>Reset Password
                    </button>
                </div>
            </form>
        <?php else: ?>
            <!-- Token expired or invalid -->
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 64px;"></i>
                <h4 class="mt-3">Invalid or Expired Link</h4>
                <p class="text-muted">This password reset link is invalid or has expired.</p>
                <a href="/forgot-password" class="btn btn-primary">Request New Link</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="/login"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
        </div>

        <div class="gov-notice">
            <i class="bi bi-shield-check"></i>
            <p>This is an official portal of the Ministry of Road Transport & Highways.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="/assets/js/auth/reset-password.js"></script>
<?= $this->endSection() ?>