/**
 * Registration Page Validation
 * Uses window.ValidationPatterns from PHP for single source of truth
 * Requires Flatpickr to be loaded before this script
 */
import { FieldValidator, validationRules } from '/assets/js/validation.js';

export function initRegisterForm() {
    const form = document.getElementById('registerForm');
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');

    // Calculate max date (18 years ago from today)
    const today = new Date();
    const minAge = window.ValidationPatterns?.minAge || 18;
    const maxDate = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());

    // Initialize Flatpickr date picker
    const dobPicker = flatpickr("#dob", {
        dateFormat: "d/m/Y",
        maxDate: maxDate,
        minDate: "01/01/1940",
        defaultDate: null,
        allowInput: false,
        disableMobile: true,
        monthSelectorType: "dropdown",
        yearSelectorType: "dropdown",
        onChange: function (selectedDates, dateStr) {
            validateDob(dateStr);
        }
    });

    // Validation state
    let validationState = {
        firstName: false,
        lastName: false,
        email: false,
        dob: false,
        password: false,
        confirmPassword: false
    };

    // Field configurations
    const fields = [
        { name: 'firstName', ruleKey: 'firstName', fieldId: 'first_name', errorId: 'firstNameError' },
        { name: 'lastName', ruleKey: 'lastName', fieldId: 'last_name', errorId: 'lastNameError' },
        { name: 'email', ruleKey: 'email', fieldId: 'email', errorId: 'emailError' },
        { name: 'password', ruleKey: 'password', fieldId: 'password', errorId: 'passwordError' },
        { name: 'confirmPassword', ruleKey: 'confirmPassword', fieldId: 'password_confirm', errorId: 'confirmPasswordError', matchFieldId: 'password' }
    ];

    // Setup validation listeners
    fields.forEach(field => {
        const element = document.getElementById(field.fieldId);
        if (!element) return;

        element.addEventListener('input', () => {
            const sanitized = FieldValidator.sanitize(field.ruleKey, element.value);
            if (sanitized !== element.value) {
                element.value = sanitized;
            }
            validateField(field);
        });

        element.addEventListener('blur', () => {
            validateField(field);
        });
    });

    // Validate a field
    function validateField(field) {
        const element = document.getElementById(field.fieldId);
        if (!element) return false;

        const options = {};
        if (field.matchFieldId) {
            const matchField = document.getElementById(field.matchFieldId);
            if (matchField) {
                options.compareValue = matchField.value;
            }
        }

        const isValid = FieldValidator.validate(field.ruleKey, element.value, field.fieldId, field.errorId, options);
        validationState[field.name] = isValid;
        updateSubmitButton();
        return isValid;
    }

    // Validate DOB (special case for flatpickr format DD/MM/YYYY)
    function validateDob(dateStr) {
        const fieldId = 'dob';
        const errorId = 'dobError';
        const dobMessage = window.ValidationPatterns?.messages?.dob || 'You must be at least 18 years old.';

        if (!dateStr) {
            FieldValidator.showError(fieldId, errorId, 'Date of birth is required.');
            validationState.dob = false;
            updateSubmitButton();
            return false;
        }

        // Parse DD/MM/YYYY
        const parts = dateStr.split('/');
        if (parts.length !== 3) {
            FieldValidator.showError(fieldId, errorId, 'Invalid date format.');
            validationState.dob = false;
            updateSubmitButton();
            return false;
        }

        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);
        const dob = new Date(year, month, day);

        // Calculate age
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        if (age < minAge) {
            FieldValidator.showError(fieldId, errorId, dobMessage);
            validationState.dob = false;
            updateSubmitButton();
            return false;
        }

        FieldValidator.clearError(fieldId, errorId, true);
        validationState.dob = true;
        updateSubmitButton();
        return true;
    }

    // Update submit button state
    function updateSubmitButton() {
        const allValid = Object.values(validationState).every(v => v === true);
        submitBtn.disabled = !allValid;
    }

    // Validate all on submit
    form.addEventListener('submit', function (e) {
        let allValid = true;

        fields.forEach(field => {
            if (!validateField(field)) {
                allValid = false;
            }
        });

        const dobValue = document.getElementById('dob').value;
        if (!validateDob(dobValue)) {
            allValid = false;
        }

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
document.addEventListener('DOMContentLoaded', initRegisterForm);
