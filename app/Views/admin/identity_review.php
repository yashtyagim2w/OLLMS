<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Identity Verification']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Identity Verification</li>
    </nav>
    <h1 class="page-title">Identity Verification Review</h1>
    <p class="page-subtitle">Review and approve/reject user documents</p>
</div>

<!-- Filters -->
<div class="filters-container">
    <form id="filtersForm" class="filtersForm">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                id="search_input"
                placeholder="Search documents..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="PENDING" selected>Pending</option>
                <option value="APPROVED">Approved</option>
                <option value="REJECTED">Rejected</option>
            </select>

            <select name="sort_by" id="sort_by" class="form-select">
                <option value="">Sort by</option>
                <option value="name">Name</option>
                <option value="submitted_at">Submitted Date</option>
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

<div class="row">
    <!-- Documents List -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Documents Queue</h5>
                <span class="badge badge-primary rounded-pill" id="totalCount">0</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush user-list" id="main-table">
                    <li class="list-group-item text-center py-5" style="color: var(--gray-500);">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Loading...
                    </li>
                </ul>
            </div>
            <div class="card-footer">
                <div id="paginationContainer" class="d-flex justify-content-center"></div>
            </div>
        </div>
    </div>

    <!-- Review Panel -->
    <div class="col-lg-7">
        <div class="card h-100" id="reviewPanel">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5 text-muted">
                <i class="bi bi-file-earmark-text mb-3" style="font-size: 48px;"></i>
                <h5>Select a document to review</h5>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/swal-helper.js"></script>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    let selectedUserId = null;
    let allUsersData = [];

    function renderDocumentItem({
        row
    }) {
        const submittedDate = new Date(row.submitted_at);
        const timeAgo = getTimeAgo(submittedDate);

        return `
            <li class="list-group-item user-list-item p-3 border-bottom ${row.id == selectedUserId ? 'bg-light active' : ''}" 
                data-id="${row.id}" 
                style="cursor: pointer; transition: background 0.2s;">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar" style="width: 40px; height: 40px;">
                        ${row.first_name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-bold">${row.first_name} ${row.last_name}</p>
                        <small class="text-muted">${row.email}</small>
                        <div class="d-flex align-items-center mt-1">
                            <span class="badge badge-sm badge-${getStatusBadgeClass(row.status)} me-2">
                                ${row.status}
                            </span>
                        </div>
                    </div>
                    <small class="text-muted" title="${submittedDate.toLocaleString()}">${timeAgo}</small>
                </div>
            </li>
        `;
    }

    function getStatusBadgeClass(status) {
        return status === 'APPROVED' ? 'success' : (status === 'PENDING' ? 'warning' : 'danger');
    }

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + "y ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + "mo ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + "d ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + "h ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + "m ago";
        return Math.floor(seconds) + "s ago";
    }

    // Cache for user details
    const userDetailsCache = {};

    // Select user function - fetches from API with caching
    async function selectUser(id) {
        selectedUserId = id;

        // Highlight active item
        document.querySelectorAll('.user-list-item').forEach(el => {
            el.classList.remove('bg-light', 'active');
            if (el.dataset.id == id) el.classList.add('bg-light', 'active');
        });

        // Check cache first
        if (userDetailsCache[id]) {
            renderReviewPanel(userDetailsCache[id]);
            return;
        }

        // Show loading state
        const panel = document.getElementById('reviewPanel');
        panel.innerHTML = `
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5 text-muted">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Loading details...</p>
            </div>
        `;

        try {
            const res = await fetch(`/admin/api/identity-reviews/${id}`);
            const {
                success,
                data,
                message
            } = await res.json();

            if (!success) {
                throw new Error(message || 'Failed to fetch details');
            }

            // Cache the result
            userDetailsCache[id] = data;
            renderReviewPanel(data);
        } catch (err) {
            console.error(err);
            panel.innerHTML = `
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5 text-danger">
                    <i class="bi bi-exclamation-circle mb-3" style="font-size: 48px;"></i>
                    <p>Error loading details. Please try again.</p>
                </div>
            `;
        }
    }

    // Event delegation for user list clicks
    document.getElementById('main-table').addEventListener('click', function(e) {
        const listItem = e.target.closest('.user-list-item');
        if (listItem) {
            const id = listItem.dataset.id;
            if (id) selectUser(id);
        }
    });

    function renderReviewPanel(user) {
        const panel = document.getElementById('reviewPanel');
        panel.innerHTML = `
            <div class="card-header">
                <h3><i class="bi bi-file-earmark-text me-2"></i>Document Review</h3>
            </div>
            <div class="card-body">
                <!-- User Info -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                    <div class="profile-avatar" style="width: 60px; height: 60px; font-size: 24px;">
                        ${user.first_name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h5 class="mb-1">${user.first_name} ${user.last_name}</h5>
                        <p class="text-muted mb-0">${user.email}</p>
                        <small class="text-muted">Submitted: ${new Date(user.submitted_at).toLocaleString()}</small>
                    </div>
                    <div class="ms-auto">
                        <span class="badge badge-${getStatusBadgeClass(user.status)} fs-6">
                            ${user.status}
                        </span>
                    </div>
                </div>

                <!-- Document Preview -->
                <div class="document-preview mb-4">
                    <div class="text-center">
                        <i class="bi bi-file-earmark-pdf" style="font-size: 64px; color: var(--danger-color);"></i>
                        <p class="mt-3">${user.document_url.split('/').pop()}</p>
                        <a href="${user.document_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> View Full Document
                        </a>
                    </div>
                </div>

                <!-- Review Actions -->
                ${user.status === 'PENDING' ? `
                <div class="mb-4">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea id="remarks" class="form-control" rows="3" placeholder="Enter remarks..."></textarea>
                </div>

                <div class="review-actions">
                    <button type="button" class="btn btn-success btn-lg" onclick="handleAction(${user.id}, 'approve')">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" onclick="handleAction(${user.id}, 'reject')">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                </div>
                ` : `
                <div class="alert alert-info">
                    This document has already been ${user.status.toLowerCase()}.
                </div>
                `}
            </div>
        `;
    }

    // Handle approve/reject actions
    window.handleAction = function(id, action) {
        const title = action === 'approve' ? 'Approve Document' : 'Reject Document';
        const msg = action === 'approve' ?
            'Are you sure you want to approve this document?' :
            'Please enter rejection reason:';

        if (action === 'approve') {
            SwalHelper.confirm(title, msg).then(result => {
                if (result.isConfirmed) {
                    SwalHelper.success('Approved', 'Document has been approved');
                    // In real app, call API here and then refresh list
                }
            });
        } else {
            SwalHelper.inputPrompt(title, msg).then(result => {
                if (result.isConfirmed) {
                    SwalHelper.success('Rejected', 'Document has been rejected');
                    // In real app, call API here and then refresh list
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        initializeListPage({
            apiEndpoint: '/admin/api/identity-reviews',
            renderRow: renderDocumentItem,
            columnCount: 1, // Doesn't matter for UL
            onDataLoaded: (data) => {
                allUsersData = data; // Store data for details view
                // Optionally select first item if none selected
                if (!selectedUserId && data.length > 0) {
                    // window.selectUser(data[0].id); // Optional: auto-select first
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>