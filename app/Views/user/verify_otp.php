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
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const resendTimer = document.getElementById('resendTimer');
        const csrfToken = '<?= csrf_hash() ?>';
        const csrfName = '<?= csrf_token() ?>';
        const needsOtp = <?= ($needsOtp ?? true) ? 'true' : 'false' ?>;
        const remainingCooldown = <?= $remainingCooldown ?? 0 ?>;

        let countdown = 60;
        let timerInterval = null;

        // Send OTP on page load if needed
        async function sendInitialOtp() {
            try {
                const formData = new FormData();
                formData.append(csrfName, csrfToken);

                const response = await axios.post('/api/send-otp', formData);

                if (response.data.success) {
                    if (response.data.data?.redirect) {
                        window.location.href = response.data.data.redirect;
                        return;
                    }
                    // Start timer after OTP is sent (fresh or already sent)
                    const cooldown = response.data.data?.cooldown || 60;
                    startTimer(cooldown);
                }
            } catch (error) {
                console.error('Failed to send OTP:', error);
                // Even on error, enable resend button so user can try again
                resendBtn.disabled = false;
                resendTimer.textContent = '';
            }
        }

        // Send OTP when page loads
        if (needsOtp) {
            sendInitialOtp();
        }

        // OTP INPUT HANDLING
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
                // Handle Enter to submit
                if (e.key === 'Enter') {
                    submitOtp();
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

        // VERIFY OTP
        function getOtpValue() {
            return Array.from(otpInputs).map(input => input.value).join('');
        }

        async function submitOtp() {
            const otp = getOtpValue();

            if (otp.length !== 6) {
                SwalHelper.error('Invalid Code', 'Please enter all 6 digits.');
                return;
            }

            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

            try {
                const formData = new FormData();
                formData.append(csrfName, csrfToken);
                formData.append('otp', otp);

                const response = await axios.post('/api/verify-otp', formData);

                if (response.data.success) {
                    SwalHelper.success('Success', response.data.message).then(() => {
                        if (response.data.data?.redirect) {
                            window.location.href = response.data.data.redirect;
                        }
                    });
                } else {
                    SwalHelper.error('Error', response.data.message);
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Verify Email';
                }
            } catch (error) {
                const message = error.response?.data?.message || 'An error occurred. Please try again.';
                SwalHelper.error('Error', message);
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Verify Email';
            }
        }

        verifyBtn.addEventListener('click', submitOtp);

        // RESEND OTP
        function startTimer(seconds) {
            countdown = seconds;
            resendBtn.disabled = true;

            if (timerInterval) clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                if (countdown > 0) {
                    resendTimer.textContent = `(${countdown}s)`;
                    countdown--;
                } else {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                    resendTimer.textContent = '';
                }
            }, 1000);
        }

        async function resendOtp() {
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            try {
                const formData = new FormData();
                formData.append(csrfName, csrfToken);

                const response = await axios.post('/api/resend-otp', formData);

                if (response.data.success) {
                    SwalHelper.success('Sent', response.data.message);
                    startTimer(response.data.data?.cooldown || 60);
                } else {
                    SwalHelper.error('Error', response.data.message);
                    if (response.data.errors?.cooldown) {
                        startTimer(response.data.errors.cooldown);
                    }
                }
            } catch (error) {
                const errorData = error.response?.data;
                const message = errorData?.message || 'An error occurred. Please try again.';
                SwalHelper.error('Error', message);
                if (errorData?.errors?.cooldown) {
                    startTimer(errorData.errors.cooldown);
                }
            } finally {
                resendBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Resend Code <span id="resendTimer"></span>';
            }
        }

        resendBtn.addEventListener('click', resendOtp);

        // Start timer with remaining cooldown if OTP was already sent
        if (!needsOtp && remainingCooldown > 0) {
            startTimer(remainingCooldown);
        } else if (!needsOtp) {
            // OTP was sent but cooldown expired - enable resend button
            resendBtn.disabled = false;
            resendTimer.textContent = '';
        }
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