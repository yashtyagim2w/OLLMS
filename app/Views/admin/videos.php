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
            <select name="category" class="form-select" id="categoryFilter">
                <option value="">All Categories</option>
                <!-- Categories loaded dynamically -->
            </select>

            <select name="active_status" class="form-select">
                <option value="">All Statuses</option>
                <option value="true" selected>Active</option>
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
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Duration (mm:ss)</th>
                        <th>Status</th>
                        <th>Actions</th>
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
    <div class="card-footer">
        <div id="paginationContainer" class="d-flex justify-content-center"></div>
    </div>
</div>

<!-- Add/Edit Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1"
    data-max-file-size="<?= $maxFileSize ?>"
    data-max-duration="<?= $maxDurationSeconds ?>"
    data-max-file-size-mb="<?= $maxFileSizeMB ?>"
    data-allowed-types="video/mp4">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Add New Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Upload Guidelines:</strong> Only MP4 format supported, maximum file size: <?= $maxFileSizeMB ?>MB
                </div>

                <form id="videoUploadForm" data-no-protect="true">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">

                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category_id" id="categoryId" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <!-- Categories loaded via JavaScript -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Video Title <span class="required">*</span></label>
                        <input type="text" name="title" id="videoTitle" class="form-control"
                            placeholder="e.g., Introduction to Traffic Signs"
                            minlength="3"
                            maxlength="255"
                            required>
                        <div class="form-text">3-255 characters</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="videoDescription" class="form-control" rows="3"
                            placeholder="Brief description of what this video covers (optional)"
                            maxlength="1000"></textarea>
                        <div class="form-text">Maximum 1000 characters (optional)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration (seconds)</label>
                        <input type="number" name="duration_seconds" id="videoDuration" class="form-control"
                            placeholder="Auto-detected from video"
                            min="0"
                            max="<?= $maxDurationSeconds ?>"
                            readonly>
                        <div class="form-text">Auto-detected when video is selected (max <?= $maxDurationSeconds ?> seconds / <?= $maxDurationFormatted ?>)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Video <span class="required">*</span></label>
                        <input type="file" name="video" id="videoFile" accept=".mp4,video/mp4" hidden required>
                        <div class="file-upload" id="fileUploadArea">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p class="mb-1">Click or drag video file to upload</p>
                            <span>MP4 format only (Max: <?= $maxFileSizeMB ?>MB)</span>
                        </div>
                        <div id="filePreview" class="mt-3 d-none">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-play text-primary" style="font-size: 32px;" id="fileIcon"></i>
                                    <div>
                                        <p class="mb-0 fw-bold" id="fileName">video.mp4</p>
                                        <small class="text-muted" id="fileSize">25.5 MB</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                    <i class="bi bi-x"></i> Remove
                                </button>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="mt-3 d-none">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span id="uploadStatusText">Uploading...</span>
                                <span id="uploadPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="uploadVideoBtn">
                    <i class="bi bi-cloud-upload"></i> Upload Video
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Video Modal -->
<div class="modal fade" id="editVideoModal" tabindex="-1" aria-labelledby="editVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="editVideoModalLabel">Edit Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editVideoForm">
                    <input type="hidden" id="editVideoId">
                    <div class="mb-3">
                        <label for="editCategoryId" class="form-label required">Category</label>
                        <select class="form-select" id="editCategoryId" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editVideoTitle" class="form-label required">Title</label>
                        <input type="text" class="form-control" id="editVideoTitle" minlength="3" maxlength="255" required>
                        <div class="form-text">3-255 characters</div>
                    </div>
                    <div class="mb-3">
                        <label for="editVideoDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editVideoDescription" rows="3" maxlength="1000"></textarea>
                        <div class="form-text">Maximum 1000 characters (optional)</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">
                    <i class="bi bi-check"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module" src="/assets/js/admin/video-list.js"></script>
<script src="/assets/js/admin/video-upload.js"></script>
<?= $this->endSection() ?>