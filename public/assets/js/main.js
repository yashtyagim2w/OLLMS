/**
 * OLLMS - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Show session errors/messages via SWAL
    const sessionError = document.getElementById('session-error');
    const sessionSuccess = document.getElementById('session-success');

    if (sessionError) {
        SwalHelper.error('Error', sessionError.textContent);
        sessionError.remove();
    }
    if (sessionSuccess) {
        SwalHelper.success('Success', sessionSuccess.textContent);
        sessionSuccess.remove();
    }

    // Profile dropdown toggle
    const profileDropdown = document.querySelector('.profile-dropdown');
    if (profileDropdown) {
        const toggle = profileDropdown.querySelector('.profile-toggle');
        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
            });
        }
        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar on link click (mobile)
    document.querySelectorAll('.sidebar .nav-item').forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });

    // Password visibility toggle
    document.querySelectorAll('.password-toggle .toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // OTP input auto-focus
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            if (this.value.length === 1) {
                this.classList.add('filled');
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            }
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // Form validation styling
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                this.classList.add('was-validated');
            }
        });
    });

    // File upload preview
    const fileUpload = document.querySelector('.file-upload');
    if (fileUpload) {
        const input = fileUpload.querySelector('input[type="file"]');
        fileUpload.addEventListener('click', () => input.click());
        fileUpload.addEventListener('dragover', (e) => { e.preventDefault(); fileUpload.classList.add('dragover'); });
        fileUpload.addEventListener('dragleave', () => fileUpload.classList.remove('dragover'));
        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    // Option selection for tests
    document.querySelectorAll('.option-item').forEach(item => {
        item.addEventListener('click', function () {
            const siblings = this.closest('.options-list').querySelectorAll('.option-item');
            siblings.forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
});

// Global toggle password function
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
