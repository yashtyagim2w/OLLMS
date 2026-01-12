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
                        <a href="/dashboard" class="btn btn-primary btn-lg">
                            <i class="bi bi-house me-2"></i>Go to Dashboard
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

                        <!-- Hidden element for SWAL -->
                        <?php if (session('error')): ?>
                            <div id="session-error" style="display:none;"><?= esc(session('error')) ?></div>
                        <?php endif; ?>

                        <?php if (session('message')): ?>
                            <div id="session-success" style="display:none;"><?= esc(session('message')) ?></div>
                        <?php endif; ?>

                        <form action="/verify-otp" method="post" id="otpForm">
                            <?= csrf_field() ?>

                            <div class="otp-container d-flex justify-content-center gap-2 mb-4">
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required autofocus>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required>
                                <input type="text" class="form-control otp-input text-center" maxlength="1" name="otp[]" required>
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-shield-check me-2"></i>Verify Email
                                </button>
                            </div>
                        </form>

                        <p class="text-muted mb-2">Didn't receive the code?</p>
                        <form action="/resend-otp" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link" id="resendBtn">
                                Resend Code <span id="resendTimer"></span>
                            </button>
                        </form>

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
    document.addEventListener('DOMContentLoaded', function() {
        // OTP input auto-focus and navigation
        const otpInputs = document.querySelectorAll('.otp-input');

        otpInputs.forEach((input, index) => {
            // Only allow numbers
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                for (let i = 0; i < pastedData.length && i < otpInputs.length; i++) {
                    otpInputs[i].value = pastedData[i];
                }
                if (pastedData.length >= otpInputs.length) {
                    otpInputs[otpInputs.length - 1].focus();
                }
            });
        });

        // Resend timer (60 seconds)
        let countdown = 60;
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');

        function updateTimer() {
            if (countdown > 0) {
                resendBtn.disabled = true;
                resendTimer.textContent = `(${countdown}s)`;
                countdown--;
                setTimeout(updateTimer, 1000);
            } else {
                resendBtn.disabled = false;
                resendTimer.textContent = '';
            }
        }

        updateTimer();
    });
</script>

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