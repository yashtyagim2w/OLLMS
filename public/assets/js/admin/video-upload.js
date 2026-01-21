/**
 * Admin Video Upload Handler (Modal Version)
 * Handles direct S3 upload with presigned URLs for training videos
 * Max size: 30MB, Format: MP4 only
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('videoUploadForm');
    if (!form) return;

    const modal = document.getElementById('videoModal');
    const fileInput = document.getElementById('videoFile');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadStatusText = document.getElementById('uploadStatusText');
    const uploadBtn = document.getElementById('uploadVideoBtn');
    const categorySelect = document.getElementById('categoryId');
    const csrfToken = document.getElementById('csrfToken').value;
    const csrfName = document.getElementById('csrfToken').name;

    // Read config from modal data attributes
    const MAX_FILE_SIZE = parseInt(modal.dataset.maxFileSize) || (30 * 1024 * 1024);
    const MAX_DURATION = parseInt(modal.dataset.maxDuration) || 600;
    const MAX_FILE_SIZE_MB = parseInt(modal.dataset.maxFileSizeMb) || 30;
    const ALLOWED_TYPES = modal.dataset.allowedTypes ? modal.dataset.allowedTypes.split(',') : ['video/mp4'];
    const ALLOWED_TYPE = ALLOWED_TYPES[0]; // Primary type for validation

    // Store generated thumbnail blob for upload
    let thumbnailBlob = null;

    // Load categories when modal is opened
    modal.addEventListener('show.bs.modal', async function () {
        try {
            const response = await axios.get('/admin/api/categories');
            if (response.data.success && response.data.data) {
                categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
                response.data.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    });

    // Reset form when modal is closed
    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        filePreview.classList.add('d-none');
        fileUploadArea.style.display = 'block';
        uploadProgress.classList.add('d-none');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Upload Video';
        thumbnailBlob = null; // Reset thumbnail
    });

    // File selection via click
    fileUploadArea.addEventListener('click', () => fileInput.click());

    // Drag and drop handlers
    fileUploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = 'var(--primary-color)';
        this.style.background = 'rgba(26, 58, 92, 0.02)';
    });

    fileUploadArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '';
        this.style.background = '';
    });

    fileUploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '';
        this.style.background = '';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            fileInput.files = dataTransfer.files;
            handleFileSelection(files[0]);
        }
    });

    // File input change handler
    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handleFileSelection(this.files[0]);
        }
    });

    // Remove file button
    document.getElementById('removeFile').addEventListener('click', function () {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        fileUploadArea.style.display = 'block';
        thumbnailBlob = null; // Reset thumbnail
    });

    /**
     * Handle file selection and validation
     */
    function handleFileSelection(file) {
        // Validate file type
        if (file.type !== ALLOWED_TYPE) {
            SwalHelper.error('Invalid File Type', 'Only MP4 video files are allowed.');
            fileInput.value = '';
            return;
        }

        // Validate file size
        if (file.size > MAX_FILE_SIZE) {
            SwalHelper.error(
                'File Too Large',
                `File size must be less than ${MAX_FILE_SIZE_MB}MB. Your file is ${(file.size / 1024 / 1024).toFixed(2)}MB.`
            );
            fileInput.value = '';
            return;
        }

        // Display file preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        filePreview.classList.remove('d-none');
        fileUploadArea.style.display = 'none';

        // Auto-detect video duration
        detectVideoDuration(file);
    }

    /**
     * Detect video duration and generate thumbnail using HTML5 video API
     */
    function detectVideoDuration(file) {
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.muted = true;

        video.onloadedmetadata = function () {
            const duration = Math.round(video.duration);

            // Set duration field
            document.getElementById('videoDuration').value = duration;

            // Validate duration
            if (duration > MAX_DURATION) {
                const minutes = Math.floor(MAX_DURATION / 60);
                SwalHelper.warning(
                    'Video Too Long',
                    `Video duration is ${duration} seconds. Maximum allowed is ${MAX_DURATION} seconds (${minutes} minutes). Please trim the video.`
                );
            }

            // Seek to 1 second (or 10% of duration) to generate thumbnail
            video.currentTime = Math.min(1, duration * 0.1);
        };

        video.onseeked = function () {
            // Generate thumbnail using canvas
            const canvas = document.createElement('canvas');
            canvas.width = 320;
            canvas.height = 180;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to blob for upload
            canvas.toBlob(function (blob) {
                thumbnailBlob = blob;
                console.log('Thumbnail generated:', blob.size, 'bytes');
            }, 'image/jpeg', 0.8);

            window.URL.revokeObjectURL(video.src);
        };

        video.onerror = function () {
            window.URL.revokeObjectURL(video.src);
            console.error('Failed to load video metadata');
            document.getElementById('videoDuration').value = 0;
            thumbnailBlob = null;
        };

        video.src = URL.createObjectURL(file);
    }

    /**
     * Upload button click handler
     */
    uploadBtn.addEventListener('click', async function (e) {
        e.preventDefault();

        const categoryId = categorySelect.value;
        const title = document.getElementById('videoTitle').value.trim();
        const description = document.getElementById('videoDescription').value.trim();
        const duration = parseInt(document.getElementById('videoDuration').value) || 0;
        const file = fileInput.files[0];

        // Validations
        if (!categoryId) {
            SwalHelper.error('Category Required', 'Please select a category for the video.');
            return;
        }

        if (!title) {
            SwalHelper.error('Title Required', 'Please enter a video title.');
            return;
        }

        if (!file) {
            SwalHelper.error('No File', 'Please select a video file to upload.');
            return;
        }

        // Validate duration
        if (duration < 0) {
            SwalHelper.error('Invalid Duration', 'Duration cannot be negative.');
            return;
        }

        if (duration > MAX_DURATION) {
            const minutes = Math.floor(MAX_DURATION / 60);
            SwalHelper.error('Duration Too Long', `Maximum video duration is ${minutes} minutes (${MAX_DURATION} seconds).`);
            return;
        }

        // Double-check file validations
        if (file.type !== ALLOWED_TYPE) {
            SwalHelper.error('Invalid File Type', 'Only MP4 video files are allowed.');
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            SwalHelper.error('File Too Large', 'File size must be less than 30MB.');
            return;
        }

        // Disable button and show progress
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        uploadProgress.classList.remove('d-none');

        try {
            // Step 1: Get presigned upload URL
            updateProgress(10, 'Preparing upload...');

            const formData = new FormData();
            formData.append(csrfName, csrfToken);
            formData.append('category_id', categoryId);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('duration_seconds', duration);
            formData.append('filename', file.name);
            formData.append('content_type', file.type);
            formData.append('file_size', file.size);

            const urlResponse = await axios.post('/admin/api/video/get-upload-url', formData);

            if (!urlResponse.data.success) {
                throw new Error(urlResponse.data.message || 'Failed to get upload URL');
            }

            const { uploadUrl, key, thumbnailUploadUrl } = urlResponse.data.data;

            // Step 2: Upload video directly to S3
            updateProgress(15, 'Uploading video to cloud storage...');

            await axios.put(uploadUrl, file, {
                headers: {
                    'Content-Type': file.type,
                },
                onUploadProgress: (progressEvent) => {
                    // Map video upload progress from 15% to 75%
                    const percent = Math.round((progressEvent.loaded * 60 / progressEvent.total) + 15);
                    updateProgress(percent, 'Uploading video to cloud storage...');
                },
            });

            // Step 3: Upload thumbnail if available
            if (thumbnailBlob && thumbnailUploadUrl) {
                updateProgress(80, 'Uploading thumbnail...');
                try {
                    await axios.put(thumbnailUploadUrl, thumbnailBlob, {
                        headers: {
                            'Content-Type': 'image/jpeg',
                        },
                    });
                } catch (thumbError) {
                    console.warn('Thumbnail upload failed:', thumbError);
                    // Continue even if thumbnail fails
                }
            }

            // Step 4: Confirm upload with server
            updateProgress(90, 'Finalizing upload...');

            const confirmData = new FormData();
            confirmData.append(csrfName, csrfToken);

            const confirmResponse = await axios.post('/admin/api/video/confirm-upload', confirmData);

            if (!confirmResponse.data.success) {
                throw new Error(confirmResponse.data.message || 'Failed to confirm upload');
            }

            updateProgress(100, 'Complete!');

            // Success - close modal and reload list
            SwalHelper.success('Success', confirmResponse.data.message).then(() => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                bsModal.hide();
                // Trigger list refresh via custom event
                window.dispatchEvent(new Event('videoListRefresh'));
            });

        } catch (error) {
            console.error('Upload error:', error);
            const message = error.response?.data?.message || error.message || 'Upload failed. Please try again.';
            SwalHelper.error('Upload Failed', message);
            resetForm();
        }
    });

    /**
     * Update progress bar
     */
    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        uploadPercent.textContent = percent + '%';
        uploadStatusText.textContent = text;
    }

    /**
     * Reset form to initial state
     */
    function resetForm() {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Upload Video';
        uploadProgress.classList.add('d-none');
        progressBar.style.width = '0%';
    }
});

