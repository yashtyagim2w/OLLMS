<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => false, 'pageTitle' => 'Verify Email']) ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <?php if ($isVerified ?? false): ?>
                        <!-- Already Verified -->
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="mb-3">Email Verified!</h2>
                        <p class="text-muted mb-4">Your email has been successfully verified. You can now proceed with your application.</p>
                        <a href="/identity-upload" class="btn btn-primary btn-lg">
                            <i class="bi bi-upload me-2"></i>Upload Identity Document
                        </a>
                    <?php else: ?>
                        <!-- OTP Verification Form -->
                        <div class="mb-4">
                            <i class="bi bi-envelope-check" style="font-size: 80px; color: var(--primary-color);"></i>
                        </div>
                        <h2 class="mb-3">Verify Your Email</h2>
                        <p class="text-muted mb-4">
                            We've sent a 6-digit verification code to<br>
                            <strong><?= esc($email ?? 'your@email.com') ?></strong>
                        </p>

                        <div id="otpForm">
                            <div class="otp-container d-flex justify-content-center gap-2 mb-4">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="0" autofocus>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="1">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="2">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="3">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="4">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" data-index="5">
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="button" class="btn btn-primary btn-lg" id="verifyBtn">
                                    <i class="bi bi-shield-check me-2"></i>Verify Email
                                </button>
                            </div>
                        </div>

                        <p class="text-muted mb-2">Didn't receive the code?</p>
                        <button type="button" class="btn btn-link" id="resendBtn" disabled>
                            <i class="bi bi-arrow-repeat me-1"></i>Resend Code <span id="resendTimer">(60s)</span>
                        </button>

                        <hr class="my-4">

                        <p class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Check your spam folder if you don't see the email.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Pass PHP config to JS via global object (used by verify-otp.js)
    window.OtpConfig = {
        csrfToken: '<?= csrf_hash() ?>',
        csrfName: '<?= csrf_token() ?>',
        needsOtp: <?= ($needsOtp ?? true) ? 'true' : 'false' ?>,
        remainingCooldown: <?= $remainingCooldown ?? 0 ?>
    };
</script>
<script type="module" src="/assets/js/user/verify-otp.js"></script>

<style>
    .otp-input {
        width: 50px;
        height: 60px;
        font-size: 24px;
        font-weight: 700;
        border-radius: 8px;
    }

    .otp-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(26, 58, 92, 0.15);
    }
</style>
<?= $this->endSection() ?>