<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Test Instructions']) ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Test Instructions</li>
        </nav>
        <h1 class="page-title">Test Instruction Management</h1>
        <p class="page-subtitle">Manage instructions shown to users before the test</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#instructionModal">
        <i class="bi bi-plus-circle"></i> Add Instruction
    </button>
</div>

<!-- Filters -->
<div class="filters-container">
    <form id="filtersForm" class="filtersForm">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                id="search_input"
                placeholder="Search instructions..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="limit" id="limit_filter" class="form-select">
                <option value="10">Show 10</option>
                <option value="25">Show 25</option>
                <option value="50">Show 50</option>
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
                        <th style="width: 80px;">Order</th>
                        <th>Title</th>
                        <th>Content Preview</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="5" class="text-center py-5" style="color: var(--gray-500);">
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="instructionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Add Instruction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" class="form-control" required placeholder="Short title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Display Order <span class="required">*</span></label>
                        <input type="number" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instruction Content <span class="required">*</span></label>
                        <textarea class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    function renderInstructionRow({
        row
    }) {
        const activeBadge = row.active ?
            '<span class="badge badge-success">Active</span>' :
            '<span class="badge badge-secondary">Inactive</span>';

        return `
            <tr>
                <td>
                    <span class="sortable-handle me-2 text-muted"><i class="bi bi-grip-vertical"></i></span>
                    ${row.order}
                </td>
                <td><strong>${row.title}</strong></td>
                <td class="text-muted text-truncate" style="max-width: 300px;">${row.content_preview}</td>
                <td>${activeBadge}</td>
                <td class="actions">
                    <button class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
    }

    document.addEventListener('DOMContentLoaded', () => {
        initializeListPage({
            apiEndpoint: '/admin/api/instructions',
            renderRow: renderInstructionRow,
            columnCount: 5
        });
    });
</script>
<?= $this->endSection() ?>