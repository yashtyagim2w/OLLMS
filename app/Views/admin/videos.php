<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Video Management']) ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Video Management</li>
        </nav>
        <h1 class="page-title">Video Management</h1>
        <p class="page-subtitle">Upload and manage training videos</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal">
        <i class="bi bi-plus-circle"></i> Add Video
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
                placeholder="Search videos..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="Traffic Rules">Traffic Rules</option>
                <option value="Vehicle Safety">Vehicle Safety</option>
                <option value="Driving Techniques">Driving Techniques</option>
                <option value="Refresher">Refresher</option>
            </select>

            <select name="active_status" class="form-select">
                <option value="">All Statuses</option>
                <option value="true">Active</option>
                <option value="false">Inactive</option>
            </select>

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
                        <th style="width: 60px;">#</th>
                        <th>Video</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="6" class="text-center py-5" style="color: var(--gray-500);">
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

<!-- Add/Edit Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Add New Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="form-label">Video Title <span class="required">*</span></label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select class="form-control form-select" required>
                            <option value="">Select Category</option>
                            <option>Traffic Rules</option>
                            <option>Vehicle Safety</option>
                            <option>Driving Techniques</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Video URL <span class="required">*</span></label>
                        <input type="url" class="form-control" placeholder="https://..." required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Duration (seconds)</label>
                                <input type="number" class="form-control" placeholder="630">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select class="form-control form-select">
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
                <button type="button" class="btn btn-primary">Save Video</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    function renderVideoRow({
        row,
        rowNumber
    }) {
        const activeBadge = row.active ?
            '<span class="badge badge-success">Active</span>' :
            '<span class="badge badge-secondary">Inactive</span>';

        return `
            <tr>
                <td>${rowNumber}</td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="video-thumbnail d-flex align-items-center justify-content-center">
                            <i class="bi bi-play-circle"></i>
                        </div>
                        <span>${row.title}</span>
                    </div>
                </td>
                <td><span class="badge badge-primary">${row.category}</span></td>
                <td>${row.duration}</td>
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
            apiEndpoint: '/admin/api/videos',
            renderRow: renderVideoRow,
            columnCount: 6
        });
    });
</script>
<?= $this->endSection() ?>