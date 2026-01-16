/**
 * Reset Password Page Validation
 * Uses window.ValidationPatterns from PHP for single source of truth
 */
import { FieldValidator } from '/assets/js/validation.js';

export function initResetPasswordForm() {
    const form = document.getElementById('resetPasswordForm');

    if (!form) return;

    const submitBtn = document.getElementById('resetBtn');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirm');

    let validationState = {
        password: false,
        confirmPassword: false
    };

    // Password validation
    function validatePassword() {
        const value = passwordField.value;
        const minLen = window.ValidationPatterns?.passwordMinLength || 8;
        const maxLen = window.ValidationPatterns?.passwordMaxLength || 128;
        const pattern = window.ValidationPatterns?.password || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
        const message = window.ValidationPatterns?.messages?.password || 'Password must contain uppercase, lowercase, and a number.';

        if (!value) {
            FieldValidator.showError('password', 'passwordError', 'Password is required.');
            validationState.password = false;
            updateSubmitButton();
            return false;
        }

        if (value.length < minLen) {
            FieldValidator.showError('password', 'passwordError', `Password must be at least ${minLen} characters.`);
            validationState.password = false;
            updateSubmitButton();
            return false;
        }

        if (value.length > maxLen) {
            FieldValidator.showError('password', 'passwordError', `Password must be at most ${maxLen} characters.`);
            validationState.password = false;
            updateSubmitButton();
            return false;
        }

        if (!pattern.test(value)) {
            FieldValidator.showError('password', 'passwordError', message);
            validationState.password = false;
            updateSubmitButton();
            return false;
        }

        FieldValidator.clearError('password', 'passwordError', true);
        validationState.password = true;

        // Re-validate confirm password if it has value
        if (confirmPasswordField.value) {
            validateConfirmPassword();
        }

        updateSubmitButton();
        return true;
    }

    // Confirm password validation
    function validateConfirmPassword() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;

        if (!confirmPassword) {
            FieldValidator.showError('password_confirm', 'confirmPasswordError', 'Please confirm your password.');
            validationState.confirmPassword = false;
            updateSubmitButton();
            return false;
        }

        if (password !== confirmPassword) {
            FieldValidator.showError('password_confirm', 'confirmPasswordError', 'Passwords do not match.');
            validationState.confirmPassword = false;
            updateSubmitButton();
            return false;
        }

        FieldValidator.clearError('password_confirm', 'confirmPasswordError', true);
        validationState.confirmPassword = true;
        updateSubmitButton();
        return true;
    }

    // Event listeners
    passwordField.addEventListener('input', validatePassword);
    passwordField.addEventListener('blur', validatePassword);
    confirmPasswordField.addEventListener('input', validateConfirmPassword);
    confirmPasswordField.addEventListener('blur', validateConfirmPassword);

    function updateSubmitButton() {
        const allValid = Object.values(validationState).every(v => v === true);
        submitBtn.disabled = !allValid;
    }

    // Validate on submit
    form.addEventListener('submit', function (e) {
        let allValid = true;

        if (!validatePassword()) {
            allValid = false;
        }

        if (!validateConfirmPassword()) {
            allValid = false;
        }

        if (!allValid) {
            e.preventDefault();
            SwalHelper.error('Validation Error', 'Please fix the errors before submitting.');
            return false;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';
    });

    // Initial state
    updateSubmitButton();
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', initResetPasswordForm);
