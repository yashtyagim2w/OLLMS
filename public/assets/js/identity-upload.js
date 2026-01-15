/**
 * Identity Upload Handler
 * Handles direct S3 upload with presigned URLs
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('identityUploadForm');
    if (!form) return;

    const fileInput = document.getElementById('documentFile');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadStatusText = document.getElementById('uploadStatusText');
    const submitBtn = document.getElementById('submitBtn');
    const csrfToken = document.getElementById('csrfToken').value;
    const csrfName = document.getElementById('csrfToken').name;

    // File selection
    fileUploadArea.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            const file = this.files[0];
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

            // Update icon based on file type
            const fileIcon = document.getElementById('fileIcon');
            if (file.type === 'application/pdf') {
                fileIcon.className = 'bi bi-file-earmark-pdf text-danger';
            } else {
                fileIcon.className = 'bi bi-file-earmark-image text-primary';
            }

            filePreview.classList.remove('d-none');
            fileUploadArea.style.display = 'none';
        }
    });

    document.getElementById('removeFile').addEventListener('click', function () {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        fileUploadArea.style.display = 'block';
    });

    // Form submission with presigned URL upload
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const aadharNumber = document.getElementById('aadharNumber').value.replace(/\s/g, '');
        const file = fileInput.files[0];
        const declaration = document.getElementById('declaration').checked;

        // Validations
        if (!/^\d{12}$/.test(aadharNumber)) {
            SwalHelper.error('Invalid Aadhar', 'Please enter a valid 12-digit Aadhar number.');
            return;
        }

        if (!file) {
            SwalHelper.error('No File', 'Please select a document to upload.');
            return;
        }

        if (!declaration) {
            SwalHelper.error('Declaration Required', 'Please accept the declaration to proceed.');
            return;
        }

        // Disable button and show progress
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        uploadProgress.classList.remove('d-none');

        try {
            // Step 1: Get presigned upload URL
            updateProgress(10, 'Getting upload URL...');

            const formData = new FormData();
            formData.append(csrfName, csrfToken);
            formData.append('aadhar_number', aadharNumber);
            formData.append('filename', file.name);
            formData.append('content_type', file.type);
            formData.append('file_size', file.size);

            const urlResponse = await axios.post('/api/get-upload-url', formData);

            if (!urlResponse.data.success) {
                throw new Error(urlResponse.data.message || 'Failed to get upload URL');
            }

            const { uploadUrl, key } = urlResponse.data.data;

            // Step 2: Upload directly to S3
            updateProgress(20, 'Uploading to cloud...');

            await axios.put(uploadUrl, file, {
                headers: {
                    'Content-Type': file.type,
                },
                onUploadProgress: (progressEvent) => {
                    const percent = Math.round((progressEvent.loaded * 60 / progressEvent.total) + 20);
                    updateProgress(percent, 'Uploading to cloud...');
                }
            });

            // Step 3: Confirm upload with server
            updateProgress(85, 'Confirming upload...');

            const confirmData = new FormData();
            confirmData.append(csrfName, csrfToken);

            const confirmResponse = await axios.post('/api/confirm-upload', confirmData);

            if (!confirmResponse.data.success) {
                throw new Error(confirmResponse.data.message || 'Failed to confirm upload');
            }

            updateProgress(100, 'Complete!');

            // Success
            SwalHelper.success('Success', confirmResponse.data.message).then(() => {
                if (confirmResponse.data.data?.redirect) {
                    window.location.href = confirmResponse.data.data.redirect;
                }
            });

        } catch (error) {
            console.error('Upload error:', error);
            const message = error.response?.data?.message || error.message || 'Upload failed. Please try again.';
            SwalHelper.error('Upload Failed', message);
            resetForm();
        }
    });

    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        uploadPercent.textContent = percent + '%';
        uploadStatusText.textContent = text;
    }

    function resetForm() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-upload"></i> Submit for Verification';
        uploadProgress.classList.add('d-none');
        progressBar.style.width = '0%';
    }
});
