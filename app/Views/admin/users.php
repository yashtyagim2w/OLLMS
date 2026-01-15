<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'User Management']) ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">User Management</li>
        </nav>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">View and manage all registered users</p>
    </div>
</div>

<!-- Filters -->
<div class="filters-container">
    <form id="filtersForm" class="filtersForm">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                id="search_input"
                placeholder="Search users..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="not_uploaded">Not Uploaded</option>
            </select>

            <select name="test_status" class="form-select">
                <option value="">Test Status</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="not_taken">Not Taken</option>
            </select>

            <select name="sort_by" id="sort_by" class="form-select">
                <option value="">Sort by</option>
                <option value="name">Name</option>
                <option value="email">Email</option>
                <option value="dob">Age (DOB)</option>
                <option value="created_at">Registered Date</option>
            </select>

            <select name="sort_order" id="sort_order" class="form-select">
                <option value="ASC">Ascending</option>
                <option value="DESC" selected>Descending</option>
            </select>

            <select name="limit" id="limit_filter" class="form-select">
                <option value="10">Show 10</option>
                <option value="25">Show 25</option>
                <option value="50">Show 50</option>
                <option value="100">Show 100</option>
            </select>

            <button type="button" class="btn btn-danger" id="resetBtn">Reset</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>DOB</th>
                        <th>Document Status</th>
                        <th>Video Progress</th>
                        <th>Test Result</th>
                        <th>Certificate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="10" class="text-center py-5" style="color: var(--gray-500);">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div id="paginationContainer" class="d-flex justify-content-center"></div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Edit User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <?php helper('validation'); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editFirstName"
                                    minlength="2" maxlength="100"
                                    pattern="<?= get_name_pattern_html() ?>"
                                    title="<?= get_validation_message('name') ?>"
                                    oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editLastName"
                                    minlength="2" maxlength="100"
                                    pattern="<?= get_name_pattern_html() ?>"
                                    title="<?= get_validation_message('name') ?>"
                                    oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" id="editEmail"
                            pattern="<?= get_email_pattern_html() ?>"
                            title="<?= get_validation_message('email') ?>"
                            oninput="this.value = this.value.replace(/[^A-Za-z0-9@.+]/g, '')"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                <input type="date" class="form-control" id="editDob"
                                    min="<?= get_min_dob() ?>"
                                    max="<?= get_max_dob() ?>"
                                    title="<?= get_validation_message('dob') ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Aadhar Number <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editAadharNumber"
                                    minlength="12" maxlength="12"
                                    pattern="<?= get_aadhaar_pattern_html() ?>"
                                    title="<?= get_validation_message('aadhaar') ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="12 digit Aadhar"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Status Toggles -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email Verification</label>
                                <select class="form-select" id="editEmailVerification">
                                    <option value="PENDING">Not Verified</option>
                                    <option value="COMPLETED">Verified</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Document Status</label>
                                <select class="form-select" id="editDocStatus">
                                    <option value="NOT_UPLOADED">Not Uploaded</option>
                                    <option value="PENDING">Pending</option>
                                    <option value="APPROVED">Approved</option>
                                    <option value="REJECTED">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    // Store all users data for editing
    let allUsersData = [];

    function renderUserRow({
        row,
        rowNumber
    }) {
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

    // Store original user data for comparison
    let originalUserData = {};

    // Open edit modal with user data
    window.openEditModal = function(userId) {
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
            doc_status: user.docStatus || ''
        };
        console.log(user);
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editFirstName').value = user.first_name;
        document.getElementById('editLastName').value = user.last_name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editDob').value = user.dob || '';
        document.getElementById('editAadharNumber').value = user.aadhar_number || '';
        document.getElementById('editEmailVerification').value = user.verificationStatus || 'PENDING';
        document.getElementById('editDocStatus').value = user.docStatus || '';

        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    };

    // Save user - calls real API
    window.saveUser = async function() {
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

        // Check for empty required fields
        if (!firstName || !lastName || !email || !dob || !aadhar) {
            SwalHelper.error('Validation Error', 'First Name, Last Name, Email, Date of Birth, and Aadhaar are required fields.');
            return;
        }

        // Check if anything has changed
        const hasFirstNameChanged = firstName !== originalUserData.first_name;
        const hasLastNameChanged = lastName !== originalUserData.last_name;
        const hasEmailChanged = email !== originalUserData.email;
        const hasDobChanged = dob !== originalUserData.dob;
        const hasAadharChanged = aadhar !== originalUserData.aadhar_number;
        const hasVerificationStatusChanged = emailVerified !== originalUserData.verification_status;
        const hasDocStatusChanged = docStatus !== originalUserData.doc_status;

        if (!hasFirstNameChanged && !hasLastNameChanged && !hasEmailChanged && !hasDobChanged && !hasAadharChanged && !hasVerificationStatusChanged && !hasDocStatusChanged) {
            SwalHelper.warning('No Changes', 'No changes detected. Please modify at least one field to update.');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('email', email);
            formData.append('dob', dob);
            formData.append('aadhar_number', aadhar);
            formData.append('verification_status', emailVerified);
            formData.append('doc_status', docStatus);

            const response = await axios.post(`/admin/api/users/${userId}`, formData);

            if (response.data.success) {
                SwalHelper.success('Success', response.data.message || 'User updated successfully.');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                modal.hide();
                // Refresh list
                window.location.reload();
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

    // Ban or Activate user
    window.toggleUserStatus = async function(userId, action) {
        const isBan = action === 'ban';
        const title = isBan ? 'Ban User' : 'Activate User';
        const text = isBan ? 'Are you sure you want to ban this user?' : 'Are you sure you want to activate this user?';
        const confirmButtonText = isBan ? 'Yes, Ban' : 'Yes, Activate';

        const result = await Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isBan ? '#dc3545' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                const response = await axios.post(`/admin/api/users/${userId}/${action}`, formData);

                if (response.data.success) {
                    SwalHelper.success('Success', response.data.message || `User ${isBan ? 'banned' : 'activated'} successfully.`);
                    window.location.reload();
                } else {
                    SwalHelper.error('Error', response.data.message || `Failed to ${action} user.`);
                }
            } catch (error) {
                console.error(error);
                SwalHelper.error('Error', error.response?.data?.message || `Failed to ${action} user.`);
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        // Event delegation for edit buttons
        document.getElementById('main-table').addEventListener('click', function(e) {
            const editBtn = e.target.closest('[data-user-id]');
            if (editBtn) {
                const userId = editBtn.dataset.userId;
                openEditModal(userId);
            }
        });

        initializeListPage({
            apiEndpoint: '/admin/api/users',
            renderRow: renderUserRow,
            columnCount: 10,
            onDataLoaded: (data) => {
                allUsersData = data; // Store for edit functionality
            }
        });
    });
</script>
<?= $this->endSection() ?>