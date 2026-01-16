/**
 * Identity Review Page JavaScript
 * Admin document verification functionality
 */
import initializeListPage from '/assets/js/list-page.js';

// State management
let selectedUserId = null;
let allUsersData = [];

// Use global time utilities from time-utils.js
const { getTimeAgo, formatDateTime, getSimpleFileName } = window.TimeUtils;
const timeAgo = getTimeAgo;
const formatTime = formatDateTime;
const getFileName = getSimpleFileName;

/**
 * Get badge class based on document status
 */
function getStatusBadgeClass(status) {
    return status === 'APPROVED' ? 'success' : (status === 'PENDING' ? 'warning' : 'danger');
}

/**
 * Render a document item in the list
 */
function renderDocumentItem({ row }) {
    const submittedDate = new Date(row.submitted_at);
    const relativeTime = timeAgo(submittedDate);

    return `
        <li class="list-group-item user-list-item p-3 border-bottom ${row.id == selectedUserId ? 'bg-light' : ''}" 
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
                <small class="text-muted" title="${submittedDate.toLocaleString()}">${relativeTime}</small>
            </div>
        </li>
    `;
}

/**
 * Render the document review panel
 */
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
                    <small class="text-muted">Submitted: ${formatTime(user.submitted_at)}</small>
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
                    <p class="mt-3">${getFileName(user.document_url)}</p>
                    <a href="${user.document_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> View Full Document
                    </a>
                </div>
            </div>

            <!-- Review Actions -->
            ${user.status === 'PENDING' ? `
            <div class="mb-4">
                <label class="form-label">Remarks <small class="text-muted">(Required for rejection)</small></label>
                <textarea id="remarks" class="form-control" rows="3" placeholder="Enter remarks for approval (optional) or rejection reason (required)..."></textarea>
            </div>

            <div class="review-actions">
                <button type="button" id="btnApprove" class="btn btn-success btn-lg" onclick="handleAction(${user.id}, 'approve')">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
                <button type="button" id="btnReject" class="btn btn-danger btn-lg" onclick="handleAction(${user.id}, 'reject')">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            </div>
            ` : `
            <div class="alert alert-${user.status === 'APPROVED' ? 'success' : 'danger'}">
                <i class="bi bi-${user.status === 'APPROVED' ? 'check-circle' : 'x-circle'} me-2"></i>
                This document has been <strong>${user.status.toLowerCase()}</strong>.
                ${user.reviewed_at ? `<br><small class="text-muted">Reviewed on: ${formatTime(user.reviewed_at)}</small>` : ''}
            </div>
            ${user.remarks ? `
            <div class="mt-3 p-3 bg-light rounded border-start border-4 ${user.status === 'APPROVED' ? 'border-success' : 'border-danger'}">
                <strong class="d-block mb-2"><i class="bi bi-chat-quote me-1"></i> Admin Remarks:</strong>
                <p class="mb-0">${user.remarks}</p>
            </div>
            ` : ''}
            `}
        </div>
    `;
}

/**
 * Select and display user document details
 */
async function selectUser(id) {
    selectedUserId = id;

    // Highlight active item
    document.querySelectorAll('.user-list-item').forEach(el => {
        el.classList.remove('bg-light');
        if (el.dataset.id == id) el.classList.add('bg-light');
    });

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
        const { success, data, message } = await res.json();

        if (!success) {
            throw new Error(message || 'Failed to fetch details');
        }

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

/**
 * Handle approve/reject actions
 */
async function handleAction(id, action) {
    const remarks = document.getElementById('remarks')?.value || '';
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');

    // Helper function to set loading state
    function setLoading(isLoading) {
        if (btnApprove && btnReject) {
            btnApprove.disabled = isLoading;
            btnReject.disabled = isLoading;
            if (isLoading) {
                if (action === 'approve') {
                    btnApprove.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';
                } else {
                    btnReject.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Rejecting...';
                }
            } else {
                btnApprove.innerHTML = '<i class="bi bi-check-circle"></i> Approve';
                btnReject.innerHTML = '<i class="bi bi-x-circle"></i> Reject';
            }
        }
    }

    // Reset review panel to default state
    function resetPanel() {
        document.getElementById('reviewPanel').innerHTML = `
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5 text-muted">
                <i class="bi bi-file-earmark-text mb-3" style="font-size: 48px;"></i>
                <h5>Select a document to review</h5>
            </div>
        `;
    }

    if (action === 'approve') {
        const result = await SwalHelper.confirm(
            'Approve Document',
            'Are you sure you want to approve this document?'
        );

        if (result.isConfirmed) {
            setLoading(true);
            try {
                const formData = new FormData();
                if (remarks) formData.append('remarks', remarks);

                const res = await fetch(`/admin/api/identity-reviews/${id}/approve`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    selectedUserId = null;
                    SwalHelper.success('Approved', 'Document has been approved successfully. Email notification sent.');
                    resetPanel();

                    if (window.listPageInstance) {
                        window.listPageInstance.reload();
                    } else {
                        location.reload();
                    }
                } else {
                    setLoading(false);
                    SwalHelper.error('Error', data.message || 'Failed to approve document');
                }
            } catch (err) {
                setLoading(false);
                console.error(err);
                SwalHelper.error('Error', 'An error occurred while approving the document');
            }
        }
    } else {
        // Rejection - use the existing remarks textarea
        if (!remarks.trim()) {
            SwalHelper.error('Remarks Required', 'Please enter a reason for rejection in the remarks field.');
            document.getElementById('remarks')?.focus();
            return;
        }

        const result = await SwalHelper.confirm(
            'Reject Document',
            `Are you sure you want to reject this document with the following reason?\n\n"${remarks}"`
        );

        if (result.isConfirmed) {
            setLoading(true);
            try {
                const formData = new FormData();
                formData.append('remarks', remarks);

                const res = await fetch(`/admin/api/identity-reviews/${id}/reject`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    selectedUserId = null;
                    SwalHelper.success('Rejected', 'Document has been rejected. Email notification sent.');
                    resetPanel();

                    if (window.listPageInstance) {
                        window.listPageInstance.reload();
                    } else {
                        location.reload();
                    }
                } else {
                    setLoading(false);
                    SwalHelper.error('Error', data.message || 'Failed to reject document');
                }
            } catch (err) {
                setLoading(false);
                console.error(err);
                SwalHelper.error('Error', 'An error occurred while rejecting the document');
            }
        }
    }
}

// Make functions available globally
window.handleAction = handleAction;
window.selectUser = selectUser;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Event delegation for user list clicks
    document.getElementById('main-table').addEventListener('click', function (e) {
        const listItem = e.target.closest('.user-list-item');
        if (listItem) {
            const id = listItem.dataset.id;
            if (id) selectUser(id);
        }
    });

    // Initialize list page
    window.listPageInstance = initializeListPage({
        apiEndpoint: '/admin/api/identity-reviews',
        renderRow: renderDocumentItem,
        columnCount: 1,
        onDataLoaded: (data) => {
            allUsersData = data;
            const totalBadge = document.getElementById('totalCount');
            if (totalBadge) {
                totalBadge.textContent = data.length;
            }
        }
    });
});
