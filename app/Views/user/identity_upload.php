<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => false, 'pageTitle' => 'Upload Identity']) ?>

<?= $this->section('content') ?>
<div class="container" style="max-width: 700px;">
    <div class="page-header text-center">
        <h1 class="page-title">Identity Verification</h1>
        <p class="page-subtitle">Upload your Aadhar card for verification</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Important Instructions</strong>
                    <ul class="mb-0 mt-2">
                        <li>Upload a clear scanned copy of your Aadhar card</li>
                        <li>PDF format is preferred, max file size: 5MB</li>
                        <li>Ensure all details are clearly visible</li>
                        <li>Both front and back sides should be included</li>
                    </ul>
                </div>
            </div>

            <form id="identityUploadForm" data-no-protect="true">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">

                <div class="form-group">
                    <label class="form-label">Aadhar Number <span class="required">*</span></label>
                    <input type="text" name="aadhar_number" id="aadharNumber" class="form-control"
                        placeholder="Enter 12-digit Aadhar number"
                        pattern="[0-9]{12}"
                        maxlength="12"
                        required>
                    <div class="form-text">Enter your 12-digit Aadhar number without spaces or dashes</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Aadhar Card <span class="required">*</span></label>
                    <input type="file" name="document" id="documentFile" accept=".pdf,.jpg,.jpeg,.png" hidden required>
                    <div class="file-upload" id="fileUploadArea">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p class="mb-1">Click or drag file to upload</p>
                        <span>Supported formats: PDF, JPG, PNG (Max: 5MB)</span>
                    </div>
                    <div id="filePreview" class="mt-3 d-none">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 32px;" id="fileIcon"></i>
                                <div>
                                    <p class="mb-0 fw-bold" id="fileName">document.pdf</p>
                                    <small class="text-muted" id="fileSize">2.5 MB</small>
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

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="declaration" required>
                        <label class="form-check-label" for="declaration">
                            I declare that the information provided is true and accurate. I understand that providing false information may result in legal action.
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="bi bi-upload"></i> Submit for Verification
                    </button>
                    <a href="/dashboard" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/js/identity-upload.js"></script>
<?= $this->endSection() ?>