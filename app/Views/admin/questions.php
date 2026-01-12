<?= $this->extend('layouts/admin') ?>
<?php $this->setData(['pageTitle' => 'Question Bank']) ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Question Bank</li>
        </nav>
        <h1 class="page-title">Question Bank</h1>
        <p class="page-subtitle">Manage test questions and answers</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal">
        <i class="bi bi-plus-circle"></i> Add Question
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
                placeholder="Search questions..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="Traffic Rules">Traffic Rules</option>
                <option value="Traffic Signs">Traffic Signs</option>
                <option value="Vehicle Safety">Vehicle Safety</option>
                <option value="Rules of Road">Rules of Road</option>
                <option value="Speed Limits">Speed Limits</option>
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
                        <th>Question</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
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

<!-- Add/Edit Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="form-label">Question Text <span class="required">*</span></label>
                        <textarea class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Category <span class="required">*</span></label>
                                <select class="form-control form-select" required>
                                    <option value="">Select Category</option>
                                    <option>Traffic Rules</option>
                                    <option>Traffic Signs</option>
                                    <option>Vehicle Safety</option>
                                </select>
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
                    <div class="form-group mt-3">
                        <label class="form-label">Options <span class="required">*</span></label>
                        <div class="option-group mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_option" checked>
                                </div>
                                <input type="text" class="form-control" placeholder="Option 1">
                            </div>
                        </div>
                        <div class="option-group mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_option">
                                </div>
                                <input type="text" class="form-control" placeholder="Option 2">
                            </div>
                        </div>
                        <div class="option-group mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_option">
                                </div>
                                <input type="text" class="form-control" placeholder="Option 3">
                            </div>
                        </div>
                        <div class="option-group mb-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correct_option">
                                </div>
                                <input type="text" class="form-control" placeholder="Option 4">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Question</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import initializeListPage from '/assets/js/list-page.js';

    function renderQuestionRow({
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
                    <p class="mb-0 fw-bold">${row.question}</p>
                </td>
                <td><span class="badge badge-info">${row.category}</span></td>
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
            apiEndpoint: '/admin/api/questions',
            renderRow: renderQuestionRow,
            columnCount: 5
        });
    });
</script>
<?= $this->endSection() ?>