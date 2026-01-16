/**
 * OTP Verification Page
 * Uses window.ValidationPatterns from PHP for OTP length
 */

export function initVerifyOtpPage() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    if (!otpInputs.length || !verifyBtn) return;

    // Get config from window.OtpConfig (set by PHP)
    const config = window.OtpConfig || {};
    const csrfToken = config.csrfToken || '';
    const csrfName = config.csrfName || 'csrf_token';
    const needsOtp = config.needsOtp !== false;
    const remainingCooldown = parseInt(config.remainingCooldown || '0', 10);
    const otpLength = window.ValidationPatterns?.otpLength || 6;

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
                const cooldown = response.data.data?.cooldown || 60;
                startTimer(cooldown);
            }
        } catch (error) {
            console.error('Failed to send OTP:', error);
            if (resendBtn) {
                resendBtn.disabled = false;
                resendTimer.textContent = '';
            }
        }
    }

    // OTP INPUT HANDLING
    otpInputs.forEach((input, index) => {
        // Only allow numbers
        input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
            if (e.key === 'Enter') {
                submitOtp();
            }
        });

        // Handle paste
        input.addEventListener('paste', function (e) {
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

        if (otp.length !== otpLength) {
            SwalHelper.error('Invalid Code', `Please enter all ${otpLength} digits.`);
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
        if (resendBtn) resendBtn.disabled = true;

        if (timerInterval) clearInterval(timerInterval);

        timerInterval = setInterval(() => {
            if (countdown > 0) {
                if (resendTimer) resendTimer.textContent = `(${countdown}s)`;
                countdown--;
            } else {
                clearInterval(timerInterval);
                if (resendBtn) resendBtn.disabled = false;
                if (resendTimer) resendTimer.textContent = '';
            }
        }, 1000);
    }

    async function resendOtp() {
        if (!resendBtn) return;

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

    if (resendBtn) {
        resendBtn.addEventListener('click', resendOtp);
    }

    // Initialize
    if (needsOtp) {
        sendInitialOtp();
    } else if (remainingCooldown > 0) {
        startTimer(remainingCooldown);
    } else if (resendBtn) {
        resendBtn.disabled = false;
        if (resendTimer) resendTimer.textContent = '';
    }
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', initVerifyOtpPage);
