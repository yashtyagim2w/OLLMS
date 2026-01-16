/**
 * Login Page Validation
 * Uses window.ValidationPatterns from PHP for single source of truth
 */
import { FieldValidator } from '/assets/js/validation.js';

export function initLoginForm() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');

    if (!form || !submitBtn) return;

    // Validation state
    let validationState = {
        email: false,
        password: false
    };

    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');

    // Email validation  
    function validateEmail() {
        const value = emailField.value.trim();
        const emailPattern = window.ValidationPatterns?.email || /^[A-Za-z0-9+.]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
        const emailMessage = window.ValidationPatterns?.messages?.email || 'Please enter a valid email address.';

        if (!value) {
            FieldValidator.showError('email', 'emailError', 'Email address is required.');
            validationState.email = false;
            updateSubmitButton();
            return false;
        }

        if (!emailPattern.test(value)) {
            FieldValidator.showError('email', 'emailError', emailMessage);
            validationState.email = false;
            updateSubmitButton();
            return false;
        }

        FieldValidator.clearError('email', 'emailError', true);
        validationState.email = true;
        updateSubmitButton();
        return true;
    }

    // Password validation - check required, minLength, maxLength (no complex pattern for login)
    function validatePassword() {
        const value = passwordField.value;
        const minLen = window.ValidationPatterns?.passwordMinLength || 8;
        const maxLen = window.ValidationPatterns?.passwordMaxLength || 128;

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

        FieldValidator.clearError('password', 'passwordError', true);
        validationState.password = true;
        updateSubmitButton();
        return true;
    }

    // Setup event listeners
    emailField.addEventListener('input', validateEmail);
    emailField.addEventListener('blur', validateEmail);
    passwordField.addEventListener('input', validatePassword);
    passwordField.addEventListener('blur', validatePassword);

    function updateSubmitButton() {
        const allValid = Object.values(validationState).every(v => v === true);
        submitBtn.disabled = !allValid;
    }

    // Validate all on submit
    form.addEventListener('submit', function (e) {
        let allValid = true;

        if (!validateEmail()) allValid = false;
        if (!validatePassword()) allValid = false;

        if (!allValid) {
            e.preventDefault();
            SwalHelper.error('Validation Error', 'Please fix the errors before submitting.');
            return false;
        }
    });

    // Initial state - disable submit button
    updateSubmitButton();
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', initLoginForm);
