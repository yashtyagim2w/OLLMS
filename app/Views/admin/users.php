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
                <option value="">Document Status</option>
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

            <select name="active_status" class="form-select">
                <option value="">Account Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
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
            <button type="button" class="btn btn-success" id="exportBtn">
                <i class="bi bi-download"></i> Export CSV
            </button>
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
                        <th>Status</th>
                        <th>Document Status</th>
                        <th>Video Progress</th>
                        <th>Test Result</th>
                        <th>Certificate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="11" class="text-center py-5" style="color: var(--gray-500);">
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
                                    minlength="<?= NAME_MIN_LENGTH ?>" maxlength="<?= NAME_MAX_LENGTH ?>"
                                    pattern="<?= get_name_pattern_html() ?>"
                                    title="<?= get_validation_message('name') ?>"
                                    oninput="this.value = this.value.replace(/[^A-Za-z']/g, '')"
                                    required>
                                <div id="firstNameError" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editLastName"
                                    minlength="<?= NAME_MIN_LENGTH ?>" maxlength="<?= NAME_MAX_LENGTH ?>"
                                    pattern="<?= get_name_pattern_html() ?>"
                                    title="<?= get_validation_message('name') ?>"
                                    oninput="this.value = this.value.replace(/[^A-Za-z']/g, '')"
                                    required>
                                <div id="lastNameError" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" id="editEmail"
                            maxlength="<?= EMAIL_MAX_LENGTH ?>"
                            pattern="<?= get_email_pattern_html() ?>"
                            title="<?= get_validation_message('email') ?>"
                            oninput="this.value = this.value.replace(/[^A-Za-z0-9@.+\-_]/g, '')"
                            required>
                        <div id="emailError" class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                <input type="date" class="form-control" id="editDob"
                                    min="<?= get_min_dob() ?>"
                                    max="<?= get_max_dob() ?>"
                                    title="<?= get_validation_message('dob') ?>">
                                <div id="dobError" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Aadhar Number <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editAadharNumber"
                                    minlength="<?= AADHAR_LENGTH ?>" maxlength="<?= AADHAR_LENGTH ?>"
                                    pattern="<?= get_aadhaar_pattern_html() ?>"
                                    title="<?= get_validation_message('aadhaar') ?>"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    placeholder="<?= AADHAR_LENGTH ?> digit Aadhar"
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

                    <!-- Account Status -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Account Status</label>
                                <select class="form-select" id="editActiveStatus">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
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
    import initAdminUsers from '/assets/js/admin/users.js';

    document.addEventListener('DOMContentLoaded', () => {
        initAdminUsers({
            csrfToken: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        });
    });
</script>
<?= $this->endSection() ?>