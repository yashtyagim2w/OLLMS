<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <nav class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Category Management</li>
        </nav>
        <h1 class="page-title">Category Management</h1>
        <p class="page-subtitle">Manage video categories and their display order • <span id="categoryCount">0</span> Categories</p>
    </div>
</div>

<?php helper('validation'); ?>
<!-- Create Category Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Category</h5>
    </div>
    <div class="card-body">
        <form id="createCategoryForm" data-no-protect="true">
            <div class="mb-3" style="max-width: 400px;">
                <label for="categoryName" class="form-label mb-1">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control category-name-input" id="categoryName"
                    placeholder="e.g. Traffic Rules, Road & Signs"
                    minlength="<?= CATEGORY_NAME_MIN_LENGTH ?>" maxlength="<?= CATEGORY_NAME_MAX_LENGTH ?>"
                    pattern="<?= get_category_name_pattern_html() ?>"
                    title="<?= get_validation_message('category_name') ?>"
                    required>
                <small class="text-muted"><?= get_validation_message('category_name') ?></small>
            </div>
            <button type="submit" id="createBtn" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create Category
            </button>
        </form>
    </div>
</div>

<!-- Filters -->
<div class="filters-container">
    <form id="filtersForm" class="filtersForm">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                id="searchInput"
                placeholder="Search categories..."
                maxlength="128"
                style="width:250px;">
        </div>

        <div class="filters-selection">
            <select name="status" id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <select name="sort_by" id="sortByFilter" class="form-select">
                <option value="sort_order">Sort by</option>
                <option value="video_count">Videos Count</option>
                <option value="created_at">Created At</option>
                <option value="name">Name</option>
            </select>

            <select name="sort_dir" id="sortDirFilter" class="form-select">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>

            <button type="button" class="btn btn-danger" id="resetFiltersBtn">Reset</button>
            <button type="button" class="btn btn-success" id="exportBtn">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </form>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;"><i class="bi bi-grip-vertical text-muted"></i></th>
                        <th style="width: 50px;">#</th>
                        <th style="width: 60px;">ID</th>
                        <th>Category Name</th>
                        <th style="width: 100px;">Videos</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 160px;">Created At</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="main-table">
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: var(--gray-500);">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryId">
                    <input type="hidden" id="editOriginalName">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control category-name-input" id="editCategoryName"
                            minlength="<?= CATEGORY_NAME_MIN_LENGTH ?>" maxlength="<?= CATEGORY_NAME_MAX_LENGTH ?>"
                            pattern="<?= get_category_name_pattern_html() ?>"
                            title="<?= get_validation_message('category_name') ?>"
                            required>
                        <small class="text-muted"><?= get_validation_message('category_name') ?></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveEditBtn" class="btn btn-primary">
                    <i class="bi bi-check"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Category validation config from PHP helper
    window.CategoryValidation = {
        minLength: <?= CATEGORY_NAME_MIN_LENGTH ?>,
        maxLength: <?= CATEGORY_NAME_MAX_LENGTH ?>,
        sanitizeRegex: /<?= get_category_name_sanitize_js() ?>/g
    };

    // Apply sanitization to all category name inputs
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.category-name-input').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(window.CategoryValidation.sanitizeRegex, '');
            });
        });
    });
</script>
<script src="/assets/js/time-utils.js"></script>
<script src="/assets/js/sortable.js"></script>
<script type="module" src="/assets/js/admin/category-management.js"></script>
<?= $this->endSection() ?>