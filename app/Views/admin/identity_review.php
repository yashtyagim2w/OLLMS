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
<script src="/assets/js/time-utils.js"></script>
<script type="module" src="/assets/js/admin/identity-review.js"></script>
<?= $this->endSection() ?>