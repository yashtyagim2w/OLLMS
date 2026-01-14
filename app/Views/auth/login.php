<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to your account</p>
    </div>

    <div class="auth-body">
        <!-- Hidden elements for SWAL to pick up -->
        <?php if (session('error')): ?>
            <div id="session-error" style="display:none;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (session('message')): ?>
            <div id="session-success" style="display:none;"><?= esc(session('message')) ?></div>
        <?php endif; ?>

        <form action="<?= url_to('login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                    placeholder="Enter email address" value="<?= old('email') ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Enter password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <?php if (setting('Auth.sessionConfig')['allowRemembering'] ?? true): ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" <?= old('remember') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                <?php endif; ?>
                <a href="/forgot-password">Forgot Password?</a>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </div>
        </form>

        <?php if (setting('Auth.allowRegistration') ?? true): ?>
            <div class="text-center mt-4">
                Don't have an account? <a href="<?= url_to('register') ?>">Create Account</a>
            </div>
        <?php endif; ?>

        <div class="gov-notice">
            <i class="bi bi-shield-check"></i>
            <p>This is an official portal of the Ministry of Road Transport & Highways.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>