/**
 * Forgot Password Page Validation
 * Uses window.ValidationPatterns from PHP for single source of truth
 */
import { FieldValidator } from '/assets/js/validation.js';

export function initForgotPasswordForm() {
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const emailField = document.getElementById('email');

    if (!form || !submitBtn || !emailField) return;

    let isEmailValid = false;

    // Email validation
    function validateEmail() {
        const value = emailField.value.trim();
        const emailPattern = window.ValidationPatterns?.email || /^[A-Za-z0-9+.]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
        const emailMessage = window.ValidationPatterns?.messages?.email || 'Please enter a valid email address.';

        if (!value) {
            FieldValidator.showError('email', 'emailError', 'Email address is required.');
            isEmailValid = false;
            updateSubmitButton();
            return false;
        }

        if (!emailPattern.test(value)) {
            FieldValidator.showError('email', 'emailError', emailMessage);
            isEmailValid = false;
            updateSubmitButton();
            return false;
        }

        FieldValidator.clearError('email', 'emailError', true);
        isEmailValid = true;
        updateSubmitButton();
        return true;
    }

    emailField.addEventListener('input', validateEmail);
    emailField.addEventListener('blur', validateEmail);

    function updateSubmitButton() {
        submitBtn.disabled = !isEmailValid;
    }

    // Validate on submit
    form.addEventListener('submit', function (e) {
        if (!validateEmail()) {
            e.preventDefault();
            SwalHelper.error('Validation Error', 'Please enter a valid email address.');
            return false;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    });

    // Initial state
    updateSubmitButton();
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', initForgotPasswordForm);
