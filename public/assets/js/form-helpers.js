/**
 * FormHelpers - Global form protection utilities
 * - Loading overlay
 * - Button disable on submit to prevent double submissions
 */

const FormHelpers = {

    /**
     * Show full-page loading overlay
     * @param {string} message - Loading message to display
     */
    showLoading: function (message = 'Please wait...') {
        // Remove any existing overlay first
        this.hideLoading();

        const overlay = document.createElement('div');
        overlay.id = 'form-loading-overlay';
        overlay.className = 'form-loading-overlay';
        overlay.innerHTML = `
            <div class="form-loading-content">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="form-loading-message mt-3 mb-0">${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    },

    /**
     * Hide loading overlay
     */
    hideLoading: function () {
        const overlay = document.getElementById('form-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
        document.body.style.overflow = '';
    },

    /**
     * Disable a button and show loading state
     * @param {HTMLElement} button - The button to disable
     * @param {string} loadingText - Text to show while loading
     */
    disableButton: function (button, loadingText = 'Processing...') {
        if (!button) return;

        // Store original content
        button.dataset.originalContent = button.innerHTML;
        button.dataset.originalWidth = button.style.minWidth;

        // Set minimum width to prevent size jumping
        button.style.minWidth = button.offsetWidth + 'px';

        // Update button
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
    },

    /**
     * Re-enable a button and restore original state
     * @param {HTMLElement} button - The button to enable
     */
    enableButton: function (button) {
        if (!button) return;

        // Restore original content
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
            delete button.dataset.originalContent;
        }

        // Restore width
        button.style.minWidth = button.dataset.originalWidth || '';
        delete button.dataset.originalWidth;

        button.disabled = false;
    },

    /**
     * Protect a form from double submission
     * @param {HTMLFormElement|string} form - Form element or selector
     * @param {Object} options - Configuration options
     */
    protectForm: function (form, options = {}) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }

        if (!form) return;

        const settings = {
            showOverlay: true,
            loadingMessage: 'Please wait...',
            buttonText: 'Please wait...',
            ...options
        };

        form.addEventListener('submit', function (e) {
            // Skip if already submitting
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }

            // Mark as submitting
            form.dataset.submitting = 'true';

            // Find submit button
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');

            if (submitBtn) {
                FormHelpers.disableButton(submitBtn, settings.buttonText);
            }

            if (settings.showOverlay) {
                FormHelpers.showLoading(settings.loadingMessage);
            }
        });
    },

    /**
     * Auto-protect all forms on the page
     * @param {Object} options - Default options for all forms
     */
    protectAllForms: function (options = {}) {
        document.querySelectorAll('form').forEach(form => {
            // Skip forms that are explicitly excluded
            if (form.dataset.noProtect === 'true') return;

            // Get form-specific options from data attributes
            const formOptions = {
                showOverlay: form.dataset.showOverlay !== 'false',
                loadingMessage: form.dataset.loadingMessage || options.loadingMessage || 'Please wait...',
                buttonText: form.dataset.buttonText || options.buttonText || 'Please wait...',
                ...options
            };

            this.protectForm(form, formOptions);
        });
    },

    /**
     * Reset form protection state (e.g., after AJAX error)
     * @param {HTMLFormElement|string} form - Form element or selector
     */
    resetFormState: function (form) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }

        if (!form) return;

        // Reset submitting state
        form.dataset.submitting = 'false';

        // Re-enable submit button
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            this.enableButton(submitBtn);
        }

        // Hide loading overlay
        this.hideLoading();
    }
};

// Auto-protect all forms on page load
document.addEventListener('DOMContentLoaded', function () {
    FormHelpers.protectAllForms();
});

window.FormHelpers = FormHelpers;
