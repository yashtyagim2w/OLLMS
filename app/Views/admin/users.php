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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editFirstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="editLastName" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="editDob">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Document Status</label>
                                <select class="form-control form-select" id="editDocStatus">
                                    <option value="PENDING">Pending</option>
                                    <option value="APPROVED">Approved</option>
                                    <option value="REJECTED">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Test Result</label>
                                <select class="form-control form-select" id="editTestResult">
                                    <option value="">Not Taken</option>
                                    <option value="PASS">Passed</option>
                                    <option value="FAIL">Failed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Certificate Issued</label>
                                <select class="form-control form-select" id="editHasCert">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
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
        const docStatusPill = row.docStatus === 'APPROVED' ? '<span class="badge badge-success">Approved</span>' :
            (row.docStatus === 'PENDING' ? '<span class="badge badge-warning">Pending</span>' :
                '<span class="badge badge-danger">Rejected</span>');

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

    // Open edit modal with user data
    window.openEditModal = function(userId) {
        const user = allUsersData.find(u => u.id == userId);
        if (!user) return;

        document.getElementById('editUserId').value = user.id;
        document.getElementById('editFirstName').value = user.first_name;
        document.getElementById('editLastName').value = user.last_name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editDob').value = user.dob;
        document.getElementById('editDocStatus').value = user.docStatus;
        document.getElementById('editTestResult').value = user.testResult || '';
        document.getElementById('editHasCert').value = user.hasCert ? '1' : '0';

        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    };

    // Save user (UI only - would call API in real implementation)
    window.saveUser = function() {
        const userId = document.getElementById('editUserId').value;

        // In real implementation, this would call an API
        SwalHelper.success('Success', 'User details have been updated successfully.');

        const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
        modal.hide();
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