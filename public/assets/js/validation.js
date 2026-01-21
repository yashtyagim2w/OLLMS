/**
 * Form Validation Library
 * Reusable validation functions for all forms across the application
 * 
 * Usage:
 * import { FieldValidator, FormValidator, validationRules } from '/assets/js/validation.js';
 * 
 * // Single field validation
 * FieldValidator.validate('email', 'test@example.com', 'emailError');
 * 
 * // Form validation with auto-setup
 * const form = new FormValidator({
 *     fields: ['firstName', 'lastName', 'email'],
 *     submitButton: '#submitBtn',
 *     onSubmit: (data) => { ... }
 * });
 * 
 * Note: Patterns are loaded from window.ValidationPatterns (injected from PHP) for single source of truth.
 */

// Get patterns from PHP (injected via auth layout) or use fallbacks
const patterns = window.ValidationPatterns || {};
const messages = patterns.messages || {};

// ============================================
// VALIDATION RULES (Using PHP constants from database schema)
// ============================================
export const validationRules = {
    firstName: {
        required: true,
        minLength: patterns.nameMinLength || 2,
        maxLength: patterns.nameMaxLength || 100,
        pattern: patterns.name || /^[A-Za-z']+$/,
        message: messages.name || 'First name must be 2-100 characters only.',
        sanitize: (v) => v.replace(/[^A-Za-z']/g, '')
    },
    lastName: {
        required: true,
        minLength: patterns.nameMinLength || 2,
        maxLength: patterns.nameMaxLength || 100,
        pattern: patterns.name || /^[A-Za-z']+$/,
        message: messages.name || 'Last name must be 2-100 characters only.',
        sanitize: (v) => v.replace(/[^A-Za-z']/g, '')
    },
    email: {
        required: true,
        maxLength: patterns.emailMaxLength || 255,
        pattern: patterns.email || /^[A-Za-z0-9+.]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/,
        message: messages.email || 'Please enter a valid email address.',
        sanitize: (v) => v.replace(/[^A-Za-z0-9@.+\-_]/g, '')
    },
    password: {
        required: true,
        minLength: patterns.passwordMinLength || 8,
        maxLength: patterns.passwordMaxLength || 128,
        pattern: patterns.password || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
        message: messages.password || 'Password must be at least 8 characters with uppercase, lowercase, and number.'
    },
    confirmPassword: {
        required: true,
        matchField: 'password',
        message: 'Passwords do not match.'
    },
    dob: {
        required: true,
        type: 'date',
        minAge: patterns.minAge || 18,
        message: messages.dob || 'Date of birth is required. You must be at least 18 years old.'
    },
    aadhar: {
        required: true,
        exactLength: patterns.aadharLength || 12,
        pattern: patterns.aadhar || /^\d{12}$/,
        message: messages.aadhar || 'Aadhaar must be exactly 12 digits.',
        sanitize: (v) => v.replace(/[^0-9]/g, '')
    },
    otp: {
        required: true,
        exactLength: patterns.otpLength || 6,
        pattern: /^\d{6}$/,
        message: 'OTP must be exactly 6 digits.',
        sanitize: (v) => v.replace(/[^0-9]/g, '')
    },
    // Video validation rules (XSS protection via escapeHtml on display, data preserved)
    videoTitle: {
        required: true,
        minLength: 3,
        maxLength: 255,
        message: 'Title must be 3-255 characters.'
    },
    videoDescription: {
        required: false,
        maxLength: 1000,
        message: 'Description cannot exceed 1000 characters.'
    }
};

// ============================================
// FIELD VALIDATOR
// ============================================
export const FieldValidator = {
    /**
     * Show error on a field
     */
    showError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(errorId);
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    },

    /**
     * Clear error and optionally show valid state
     */
    clearError(fieldId, errorId, showValid = false) {
        const field = document.getElementById(fieldId);
        const errorDiv = document.getElementById(errorId);
        if (field) {
            field.classList.remove('is-invalid');
            if (showValid && field.value.trim()) {
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
            }
        }
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';
        }
    },

    /**
     * Validate a field against rules
     * @param {string} ruleKey - Key from validationRules object
     * @param {string} value - Field value to validate
     * @param {string} fieldId - DOM field ID
     * @param {string} errorId - DOM error div ID
     * @param {Object} options - Optional: { compareValue: value for matchField }
     * @returns {boolean} - True if valid
     */
    validate(ruleKey, value, fieldId, errorId, options = {}) {
        const rules = validationRules[ruleKey];
        if (!rules) {
            console.warn(`No validation rules found for: ${ruleKey}`);
            return true;
        }

        const trimmedValue = typeof value === 'string' ? value.trim() : value;

        // Check required
        if (rules.required && !trimmedValue) {
            this.showError(fieldId, errorId, 'This field is required.');
            return false;
        }

        // Skip other validations if empty and not required
        if (!trimmedValue && !rules.required) {
            this.clearError(fieldId, errorId);
            return true;
        }

        // Check min length
        if (rules.minLength && trimmedValue.length < rules.minLength) {
            this.showError(fieldId, errorId, rules.message);
            return false;
        }

        // Check max length
        if (rules.maxLength && trimmedValue.length > rules.maxLength) {
            this.showError(fieldId, errorId, rules.message);
            return false;
        }

        // Check exact length
        if (rules.exactLength && trimmedValue.length !== rules.exactLength) {
            this.showError(fieldId, errorId, rules.message);
            return false;
        }

        // Check pattern
        if (rules.pattern && !rules.pattern.test(trimmedValue)) {
            this.showError(fieldId, errorId, rules.message);
            return false;
        }

        // Check match field (for confirm password)
        if (rules.matchField && options.compareValue !== undefined) {
            if (trimmedValue !== options.compareValue) {
                this.showError(fieldId, errorId, rules.message);
                return false;
            }
        }

        // Check DOB age
        if (rules.type === 'date' && trimmedValue) {
            const ageResult = this.validateAge(trimmedValue, rules.minAge, rules.maxAge);
            if (!ageResult.valid) {
                this.showError(fieldId, errorId, ageResult.message);
                return false;
            }
        }

        // All validations passed
        this.clearError(fieldId, errorId, true);
        return true;
    },

    /**
     * Validate age from DOB
     */
    validateAge(dobValue, minAge = 18) {
        const dob = new Date(dobValue);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        if (age < minAge) {
            return { valid: false, message: `You must be at least ${minAge} years old.` };
        }
        return { valid: true };
    },

    /**
     * Get sanitized value based on rule
     */
    sanitize(ruleKey, value) {
        const rules = validationRules[ruleKey];
        if (rules && rules.sanitize) {
            return rules.sanitize(value);
        }
        return value;
    }
};

// ============================================
// FORM VALIDATOR CLASS
// ============================================
export class FormValidator {
    /**
     * Create a form validator
     * @param {Object} config
     * @param {Array<Object>} config.fields - Array of { name, fieldId, errorId, ruleKey }
     * @param {string} config.submitButton - Submit button selector
     * @param {Function} config.onSubmit - Called with form data when valid
     * @param {boolean} config.validateOnInput - Validate on input (default: true)
     * @param {boolean} config.validateOnBlur - Validate on blur (default: true)
     */
    constructor(config) {
        this.fields = config.fields || [];
        this.submitButton = document.querySelector(config.submitButton);
        this.onSubmit = config.onSubmit || (() => { });
        this.validateOnInput = config.validateOnInput !== false;
        this.validateOnBlur = config.validateOnBlur !== false;

        this.validationState = {};
        this.fields.forEach(f => {
            this.validationState[f.name] = false;
        });

        this.setupListeners();
    }

    /**
     * Setup event listeners for all fields
     */
    setupListeners() {
        this.fields.forEach(field => {
            const element = document.getElementById(field.fieldId);
            if (!element) return;

            // Input event - sanitize and validate
            if (this.validateOnInput) {
                element.addEventListener('input', () => {
                    // Sanitize input
                    if (validationRules[field.ruleKey]?.sanitize) {
                        element.value = FieldValidator.sanitize(field.ruleKey, element.value);
                    }
                    this.validateField(field);
                });
            }

            // Blur event - validate
            if (this.validateOnBlur) {
                element.addEventListener('blur', () => {
                    this.validateField(field);
                });
            }
        });
    }

    /**
     * Validate a single field
     */
    validateField(field) {
        const element = document.getElementById(field.fieldId);
        if (!element) return false;

        const value = element.value;
        const options = {};

        // Handle confirm password matching
        if (field.ruleKey === 'confirmPassword' && field.matchFieldId) {
            const matchField = document.getElementById(field.matchFieldId);
            if (matchField) {
                options.compareValue = matchField.value;
            }
        }

        const isValid = FieldValidator.validate(
            field.ruleKey,
            value,
            field.fieldId,
            field.errorId,
            options
        );

        this.validationState[field.name] = isValid;
        this.updateSubmitButton();
        return isValid;
    }

    /**
     * Validate all fields
     */
    validateAll() {
        let allValid = true;
        this.fields.forEach(field => {
            const isValid = this.validateField(field);
            if (!isValid) allValid = false;
        });
        return allValid;
    }

    /**
     * Update submit button state
     */
    updateSubmitButton() {
        if (!this.submitButton) return;
        const allValid = Object.values(this.validationState).every(v => v === true);
        this.submitButton.disabled = !allValid;
    }

    /**
     * Get form data
     */
    getData() {
        const data = {};
        this.fields.forEach(field => {
            const element = document.getElementById(field.fieldId);
            if (element) {
                data[field.name] = element.value.trim();
            }
        });
        return data;
    }

    /**
     * Reset validation state
     */
    reset() {
        this.fields.forEach(field => {
            this.validationState[field.name] = false;
            FieldValidator.clearError(field.fieldId, field.errorId);
        });
        this.updateSubmitButton();
    }

    /**
     * Set all fields as valid (for pre-populated forms)
     */
    setAllValid() {
        this.fields.forEach(field => {
            this.validationState[field.name] = true;
        });
        this.updateSubmitButton();
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Quick setup for common forms
 */
export function setupLoginForm(config = {}) {
    return new FormValidator({
        fields: [
            { name: 'email', fieldId: config.emailId || 'email', errorId: config.emailErrorId || 'emailError', ruleKey: 'email' },
            { name: 'password', fieldId: config.passwordId || 'password', errorId: config.passwordErrorId || 'passwordError', ruleKey: 'password' }
        ],
        submitButton: config.submitButton || '#loginBtn',
        onSubmit: config.onSubmit
    });
}

export function setupRegistrationForm(config = {}) {
    return new FormValidator({
        fields: [
            { name: 'firstName', fieldId: config.firstNameId || 'firstName', errorId: config.firstNameErrorId || 'firstNameError', ruleKey: 'firstName' },
            { name: 'lastName', fieldId: config.lastNameId || 'lastName', errorId: config.lastNameErrorId || 'lastNameError', ruleKey: 'lastName' },
            { name: 'email', fieldId: config.emailId || 'email', errorId: config.emailErrorId || 'emailError', ruleKey: 'email' },
            { name: 'dob', fieldId: config.dobId || 'dob', errorId: config.dobErrorId || 'dobError', ruleKey: 'dob' },
            { name: 'password', fieldId: config.passwordId || 'password', errorId: config.passwordErrorId || 'passwordError', ruleKey: 'password' },
            { name: 'confirmPassword', fieldId: config.confirmPasswordId || 'confirmPassword', errorId: config.confirmPasswordErrorId || 'confirmPasswordError', ruleKey: 'confirmPassword', matchFieldId: config.passwordId || 'password' }
        ],
        submitButton: config.submitButton || '#registerBtn',
        onSubmit: config.onSubmit
    });
}

export function setupPasswordResetForm(config = {}) {
    return new FormValidator({
        fields: [
            { name: 'password', fieldId: config.passwordId || 'newPassword', errorId: config.passwordErrorId || 'newPasswordError', ruleKey: 'password' },
            { name: 'confirmPassword', fieldId: config.confirmPasswordId || 'confirmPassword', errorId: config.confirmPasswordErrorId || 'confirmPasswordError', ruleKey: 'confirmPassword', matchFieldId: config.passwordId || 'newPassword' }
        ],
        submitButton: config.submitButton || '#resetBtn',
        onSubmit: config.onSubmit
    });
}

export function setupForgotPasswordForm(config = {}) {
    return new FormValidator({
        fields: [
            { name: 'email', fieldId: config.emailId || 'email', errorId: config.emailErrorId || 'emailError', ruleKey: 'email' }
        ],
        submitButton: config.submitButton || '#submitBtn',
        onSubmit: config.onSubmit
    });
}

// Default export for convenience
export default {
    validationRules,
    FieldValidator,
    FormValidator,
    setupLoginForm,
    setupRegistrationForm,
    setupPasswordResetForm,
    setupForgotPasswordForm
};
