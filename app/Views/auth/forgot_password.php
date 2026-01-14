<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-key"></i>
        </div>
        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Enter your email to receive reset instructions</p>
    </div>

    <div class="auth-body">
        <!-- Hidden elements for SWAL to pick up -->
        <?php if (session('error')): ?>
            <div id="session-error" style="display:none;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (session('message')): ?>
            <div id="session-success" style="display:none;"><?= esc(session('message')) ?></div>
        <?php endif; ?>

        <form action="/forgot-password" method="post" id="forgotPasswordForm">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control form-control-lg"
                    placeholder="Enter your registered email" value="<?= old('email') ?>" required autofocus>
                <div class="form-text">We'll send a password reset link to this email</div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-send me-2"></i>Send Reset Link
                </button>
            </div>
        </form>

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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('forgotPasswordForm');

        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
        });
    });
</script>
<?= $this->endSection() ?>