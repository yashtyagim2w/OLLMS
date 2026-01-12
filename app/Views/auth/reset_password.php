<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-key"></i>
        </div>
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter your email to receive reset instructions</p>
    </div>

    <div class="auth-body">
        <!-- Step 1: Request Reset -->
        <div id="requestStep">
            <form id="resetRequestForm" action="/auth/reset-password" method="post">
                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i>
                        Send Reset Link
                    </button>
                </div>
            </form>
        </div>

        <!-- Step 2: Enter New Password (shown after token verification) -->
        <div id="resetStep" class="d-none">
            <form id="resetPasswordForm" action="/auth/reset-password/confirm" method="post">
                <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

                <div class="form-group">
                    <label class="form-label">New Password <span class="required">*</span></label>
                    <div class="password-toggle">
                        <input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="8">
                        <button type="button" class="toggle-btn">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password <span class="required">*</span></label>
                    <div class="password-toggle">
                        <input type="password" name="password_confirm" class="form-control" placeholder="Confirm new password" required>
                        <button type="button" class="toggle-btn">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Reset Password
                    </button>
                </div>
            </form>
        </div>

        <div class="auth-footer">
            <a href="/login"><i class="bi bi-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>