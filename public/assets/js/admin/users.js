/**
 * Admin Users Page JavaScript
 * Handles user list rendering, editing, and status management
 */
import initializeListPage from '/assets/js/list-page.js';
import { FieldValidator, validationRules } from '/assets/js/validation.js';

// Store all users data for editing
let allUsersData = [];

// Store original user data for comparison
let originalUserData = {};

// Config object to store CSRF token (set by initAdminUsers)
let config = {
    csrfToken: '',
    csrfHash: ''
};

// Validation state for edit form
let validationState = {
    firstName: true,
    lastName: true,
    email: true,
    dob: true,
    aadhar: true
};

/**
 * Validate a field and update state
 */
function validateFormField(name, ruleKey, fieldId, errorId, value, options = {}) {
    const isValid = FieldValidator.validate(ruleKey, value, fieldId, errorId, options);
    validationState[name] = isValid;
    updateSaveButtonState();
    return isValid;
}

/**
 * Update save button state based on validation
 */
function updateSaveButtonState() {
    const saveBtn = document.querySelector('#editUserModal .btn-primary');
    if (!saveBtn) return;

    const allValid = Object.values(validationState).every(v => v === true);
    saveBtn.disabled = !allValid;
}

/**
 * Validate all fields
 */
function validateAllFields() {
    const firstName = document.getElementById('editFirstName').value.trim();
    const lastName = document.getElementById('editLastName').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const dob = document.getElementById('editDob').value;
    const aadhar = document.getElementById('editAadharNumber').value.trim();

    validateFormField('firstName', 'firstName', 'editFirstName', 'firstNameError', firstName);
    validateFormField('lastName', 'lastName', 'editLastName', 'lastNameError', lastName);
    validateFormField('email', 'email', 'editEmail', 'emailError', email);
    validateFormField('dob', 'dob', 'editDob', 'dobError', dob);
    validateFormField('aadhar', 'aadhar', 'editAadharNumber', 'aadharError', aadhar);

    return Object.values(validationState).every(v => v === true);
}

/**
 * Setup field validation event listeners
 */
function setupValidationListeners() {
    const fields = [
        { name: 'firstName', ruleKey: 'firstName', fieldId: 'editFirstName', errorId: 'firstNameError' },
        { name: 'lastName', ruleKey: 'lastName', fieldId: 'editLastName', errorId: 'lastNameError' },
        { name: 'email', ruleKey: 'email', fieldId: 'editEmail', errorId: 'emailError' },
        { name: 'dob', ruleKey: 'dob', fieldId: 'editDob', errorId: 'dobError' },
        { name: 'aadhar', ruleKey: 'aadhar', fieldId: 'editAadharNumber', errorId: 'aadharError' }
    ];

    fields.forEach(field => {
        const element = document.getElementById(field.fieldId);
        if (!element) return;

        // Input sanitization and validation
        element.addEventListener('input', () => {
            // Sanitize input
            const sanitized = FieldValidator.sanitize(field.ruleKey, element.value);
            if (sanitized !== element.value) {
                element.value = sanitized;
            }
            validateFormField(field.name, field.ruleKey, field.fieldId, field.errorId, element.value);
        });

        // Blur validation
        element.addEventListener('blur', () => {
            validateFormField(field.name, field.ruleKey, field.fieldId, field.errorId, element.value);
        });
    });
}

/**
 * Reset validation state
 */
function resetValidationState() {
    validationState = {
        firstName: true,
        lastName: true,
        email: true,
        dob: true,
        aadhar: true
    };

    // Clear all error states
    FieldValidator.clearError('editFirstName', 'firstNameError');
    FieldValidator.clearError('editLastName', 'lastNameError');
    FieldValidator.clearError('editEmail', 'emailError');
    FieldValidator.clearError('editDob', 'dobError');
    FieldValidator.clearError('editAadharNumber', 'aadharError');

    updateSaveButtonState();
}

/**
 * Render a user row in the table
 */
function renderUserRow({ row, rowNumber }) {
    let docStatusPill;
    switch (row.docStatus) {
        case 'APPROVED':
            docStatusPill = '<span class="badge badge-success">Approved</span>';
            break;
        case 'PENDING':
            docStatusPill = '<span class="badge badge-warning">Pending</span>';
            break;
        case 'REJECTED':
            docStatusPill = '<span class="badge badge-danger">Rejected</span>';
            break;
        case 'NOT_UPLOADED':
        default:
            docStatusPill = '<span class="badge badge-secondary">Not Uploaded</span>';
    }

    const testResultPill = row.testResult === 'PASS' ? '<span class="badge badge-success">Passed</span>' :
        (row.testResult === 'FAIL' ? '<span class="badge badge-danger">Failed</span>' :
            '<span class="badge badge-secondary">-</span>');

    const certIcon = row.hasCert ?
        '<i class="bi bi-check-circle-fill text-success" title="Issued"></i>' :
        '<span class="text-muted">-</span>';

    return `
        <tr>
            <td>${rowNumber}</td>
            <td>${row.first_name}</td>
            <td>${row.last_name}</td>
            <td class="text-muted">${row.email}</td>
            <td>${row.dob}</td>
            <td>${row.active
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>'}</td>
            <td>${docStatusPill}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                     <div class="progress" style="width: 80px; height: 6px;">
                        <div class="progress-bar ${row.videoProgress === 100 ? 'bg-success' : 'bg-primary'}" 
                            style="width: ${row.videoProgress}%"></div>
                    </div>
                    <span class="small text-muted">${row.videoProgress}%</span>
                </div>
            </td>
            <td>${testResultPill}</td>
            <td class="text-center">${certIcon}</td>
            <td class="actions">
                <button class="btn btn-sm btn-outline-primary" title="Edit User" data-user-id="${row.id}">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>
    `;
}

/**
 * Open edit modal with user data
 */
window.openEditModal = function (userId) {
    const user = allUsersData.find(u => u.id == userId);
    if (!user) return;

    // Store original values for comparison
    originalUserData = {
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        dob: user.dob || '',
        aadhar_number: user.aadhar_number || '',
        verification_status: user.verificationStatus || 'PENDING',
        doc_status: user.docStatus || '',
        active: user.active ? '1' : '0'
    };

    // Populate form fields
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFirstName').value = user.first_name;
    document.getElementById('editLastName').value = user.last_name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editDob').value = user.dob || '';
    document.getElementById('editAadharNumber').value = user.aadhar_number || '';
    document.getElementById('editEmailVerification').value = user.verificationStatus || 'PENDING';
    document.getElementById('editDocStatus').value = user.docStatus || '';
    document.getElementById('editActiveStatus').value = user.active ? '1' : '0';

    // Reset validation state for new modal
    resetValidationState();

    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
};

/**
 * Save user - calls API to update user
 */
window.saveUser = async function () {
    // Run full validation before submitting
    if (!validateAllFields()) {
        SwalHelper.error('Validation Error', 'Please fix the errors before saving.');
        return;
    }

    const userId = document.getElementById('editUserId').value;
    const saveBtn = document.querySelector('#editUserModal .btn-primary');

    // Get current values
    const firstName = document.getElementById('editFirstName').value.trim();
    const lastName = document.getElementById('editLastName').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const dob = document.getElementById('editDob').value;
    const aadhar = document.getElementById('editAadharNumber').value.trim();
    const emailVerified = document.getElementById('editEmailVerification').value;
    const docStatus = document.getElementById('editDocStatus').value;
    const activeStatus = document.getElementById('editActiveStatus').value;

    // Check if anything has changed
    const hasFirstNameChanged = firstName !== originalUserData.first_name;
    const hasLastNameChanged = lastName !== originalUserData.last_name;
    const hasEmailChanged = email !== originalUserData.email;
    const hasDobChanged = dob !== originalUserData.dob;
    const hasAadharChanged = aadhar !== originalUserData.aadhar_number;
    const hasVerificationStatusChanged = emailVerified !== originalUserData.verification_status;
    const hasDocStatusChanged = docStatus !== originalUserData.doc_status;
    const hasActiveStatusChanged = activeStatus !== originalUserData.active;

    if (!hasFirstNameChanged && !hasLastNameChanged && !hasEmailChanged && !hasDobChanged && !hasAadharChanged && !hasVerificationStatusChanged && !hasDocStatusChanged && !hasActiveStatusChanged) {
        SwalHelper.warning('No Changes', 'No changes detected. Please modify at least one field to update.');
        return;
    }

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    try {
        const formData = new FormData();
        formData.append(config.csrfToken, config.csrfHash);
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', email);
        formData.append('dob', dob);
        formData.append('aadhar_number', aadhar);
        formData.append('verification_status', emailVerified);
        formData.append('doc_status', docStatus);
        formData.append('active', activeStatus);

        const response = await axios.post(`/admin/api/users/${userId}`, formData);

        if (response.data.success) {
            SwalHelper.success('Success', response.data.message || 'User updated successfully.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            modal.hide();
            // Refresh list dynamically without page reload
            if (window.refreshListData) {
                window.refreshListData();
            }
        } else {
            SwalHelper.error('Error', response.data.message || 'Failed to update user.');
        }
    } catch (error) {
        console.error(error);
        SwalHelper.error('Error', error.response?.data?.message || 'Failed to update user.');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Changes';
    }
};

/**
 * Initialize the admin users page
 * @param {Object} options - Configuration options
 * @param {string} options.csrfToken - CSRF token name
 * @param {string} options.csrfHash - CSRF hash value
 */
export default function initAdminUsers(options = {}) {
    // Store CSRF config
    config.csrfToken = options.csrfToken || '';
    config.csrfHash = options.csrfHash || '';

    // Setup validation listeners
    setupValidationListeners();

    // Event delegation for edit buttons
    document.getElementById('main-table').addEventListener('click', function (e) {
        const editBtn = e.target.closest('[data-user-id]');
        if (editBtn) {
            const userId = editBtn.dataset.userId;
            openEditModal(userId);
        }
    });

    // Initialize list page
    const listPage = initializeListPage({
        apiEndpoint: '/admin/api/users',
        renderRow: renderUserRow,
        columnCount: 11,
        onDataLoaded: (data) => {
            allUsersData = data; // Store for edit functionality
        }
    });

    // Expose refresh function globally for use in saveUser
    window.refreshListData = listPage.fetchData;

    // Export CSV button handler
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            // Get current filter values
            const filtersForm = document.getElementById('filtersForm');
            const params = new URLSearchParams();

            if (filtersForm) {
                const formData = new FormData(filtersForm);
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });
            }

            // Show loading state
            const originalHTML = exportBtn.innerHTML;
            exportBtn.disabled = true;
            exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';

            // Trigger download
            const exportUrl = `/admin/api/users/export?${params.toString()}`;
            window.location.href = exportUrl;

            // Reset button after a short delay (download starts immediately)
            setTimeout(() => {
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalHTML;
            }, 1500);
        });
    }

    return listPage;
}
