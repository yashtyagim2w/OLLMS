/**
 * OLLMS - Admin Video List Management
 * Handles video list display, edit, delete, and restore functionality
 */

import initializeListPage from '/assets/js/list-page.js';

// Use global escapeHtml
const escapeHtml = window.escapeHtml;

/**
 * Render a video row in the table
 */
function renderVideoRow({ row, rowNumber }) {
    const activeBadge = row.is_active == 1 ?
        '<span class="badge badge-success">Active</span>' :
        '<span class="badge badge-secondary">Inactive</span>';

    // Display thumbnail if available, otherwise show play icon
    const thumbnailHtml = row.thumbnail_presigned_url ?
        `<img src="${row.thumbnail_presigned_url}" class="video-thumbnail-preview" alt="Thumbnail" loading="lazy">` :
        `<div class="video-thumbnail d-flex align-items-center justify-content-center">
            <i class="bi bi-play-circle"></i>
        </div>`;

    // Escape and truncate description
    const safeDesc = escapeHtml(row.description || '');
    const description = safeDesc ?
        (safeDesc.length > 80 ? safeDesc.substring(0, 80) + '...' : safeDesc) :
        '<span class="text-muted">-</span>';

    // Escape title for safe display
    const safeTitle = escapeHtml(row.title);

    // Use data attributes to pass data safely (no inline string escaping needed)
    const isActive = row.is_active == 1;
    const actionButtons = isActive ? `
        <button class="btn btn-sm btn-outline-success btn-play" title="Play Video" data-video-url="${escapeHtml(row.video_url)}">
            <i class="bi bi-play-fill"></i>
        </button>
        <button class="btn btn-sm btn-outline-primary btn-edit" title="Edit" data-id="${row.id}" data-category="${row.category_id}">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete" data-id="${row.id}">
            <i class="bi bi-trash"></i>
        </button>
    ` : `
        <button class="btn btn-sm btn-outline-success btn-play" title="Play Video" data-video-url="${escapeHtml(row.video_url)}">
            <i class="bi bi-play-fill"></i>
        </button>
        <button class="btn btn-sm btn-success btn-restore" title="Restore" data-id="${row.id}">
            <i class="bi bi-arrow-counterclockwise"></i> Restore
        </button>
    `;

    return `
        <tr data-title="${escapeHtml(row.title)}" data-description="${escapeHtml(row.description || '')}">
            <td>${rowNumber}</td>
            <td>${thumbnailHtml}</td>
            <td><strong>${safeTitle}</strong></td>
            <td class="text-wrap" style="max-width: 200px;">${description}</td>
            <td><span class="badge badge-primary">${escapeHtml(row.category_name)}</span></td>
            <td>${row.duration}</td>
            <td>${activeBadge}</td>
            <td class="actions">${actionButtons}</td>
        </tr>
    `;
}

/**
 * Open video in new tab with presigned URL
 */
async function openVideo(videoKey) {
    try {
        const response = await axios.get(`/admin/api/video/view-url?key=${encodeURIComponent(videoKey)}`);
        if (response.data.success && response.data.data.url) {
            window.open(response.data.data.url, '_blank');
        } else {
            SwalHelper.error('Error', 'Failed to load video.');
        }
    } catch (error) {
        console.error('Failed to get video URL:', error);
        SwalHelper.error('Error', 'Failed to load video.');
    }
}

/**
 * Load categories into filter dropdown
 */
async function loadCategories() {
    try {
        const response = await axios.get('/admin/api/categories');
        if (response.data.success && response.data.data) {
            const categoryFilter = document.getElementById('categoryFilter');
            response.data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load categories:', error);
    }
}

// Store original video data for change detection
let originalVideoData = {};

/**
 * Open edit modal with video data
 */
async function editVideo(id, title, description, categoryId) {
    // Store original values for change detection
    originalVideoData = {
        title: title,
        description: description,
        category_id: categoryId
    };

    document.getElementById('editVideoId').value = id;
    document.getElementById('editVideoTitle').value = title;
    document.getElementById('editVideoDescription').value = description;

    // Load categories into edit dropdown
    const editCategorySelect = document.getElementById('editCategoryId');
    editCategorySelect.innerHTML = '<option value="">Select Category</option>';

    try {
        const response = await axios.get('/admin/api/categories');
        if (response.data.success && response.data.data) {
            response.data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                if (category.id == categoryId) option.selected = true;
                editCategorySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load categories:', error);
    }

    new bootstrap.Modal(document.getElementById('editVideoModal')).show();
}

/**
 * Delete video with confirmation (soft delete - sets is_active = 0)
 */
async function deleteVideo(id, title) {
    const result = await SwalHelper.confirmDanger('Delete Video?', `Are you sure you want to delete "${title}"?`);

    if (result.isConfirmed) {
        try {
            const response = await axios.delete(`/admin/api/videos/${id}`);
            if (response.data.success) {
                SwalHelper.success('Deleted!', 'Video has been deleted.');
                window.dispatchEvent(new CustomEvent('videoListRefresh'));
            } else {
                SwalHelper.error('Error', response.data.message || 'Failed to delete video.');
            }
        } catch (error) {
            console.error('Delete failed:', error);
            SwalHelper.error('Error', 'Failed to delete video.');
        }
    }
}

/**
 * Restore video (set is_active = 1)
 */
async function restoreVideo(id, title) {
    const result = await SwalHelper.confirm('Restore Video?', `Are you sure you want to restore "${title}"?`, 'Yes, restore it');

    if (result.isConfirmed) {
        try {
            const response = await axios.post(`/admin/api/videos/${id}/toggle-active`);
            if (response.data.success) {
                SwalHelper.success('Restored!', 'Video has been restored.');
                window.dispatchEvent(new CustomEvent('videoListRefresh'));
            } else {
                SwalHelper.error('Error', response.data.message || 'Failed to restore video.');
            }
        } catch (error) {
            console.error('Restore failed:', error);
            SwalHelper.error('Error', 'Failed to restore video.');
        }
    }
}

/**
 * Save edit button handler with validation
 */
function initSaveEditHandler() {
    document.getElementById('saveEditBtn')?.addEventListener('click', async function () {
        const form = document.getElementById('editVideoForm');
        const id = document.getElementById('editVideoId').value;
        const title = document.getElementById('editVideoTitle').value.trim();
        const description = document.getElementById('editVideoDescription').value.trim();
        const categoryId = document.getElementById('editCategoryId').value;

        // Use HTML5 form validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Validate category (select doesn't use standard HTML5 validation well)
        if (!categoryId) {
            SwalHelper.warning('Validation Error', 'Please select a category.');
            return;
        }

        // Check if any field has changed
        const hasChanges =
            title !== originalVideoData.title ||
            description !== originalVideoData.description ||
            categoryId != originalVideoData.category_id;

        if (!hasChanges) {
            SwalHelper.warning('No Changes', 'No changes detected. Please modify at least one field to update.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        try {
            const response = await axios.put(`/admin/api/videos/${id}`, {
                title,
                description,
                category_id: parseInt(categoryId) || 0,
                original_title: originalVideoData.title,
                original_description: originalVideoData.description,
                original_category_id: originalVideoData.category_id
            });

            if (response.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editVideoModal')).hide();
                SwalHelper.success('Success', 'Video updated successfully.');
                window.dispatchEvent(new CustomEvent('videoListRefresh'));
            } else {
                SwalHelper.error('Error', response.data.message || 'Failed to update video.');
            }
        } catch (error) {
            console.error('Update failed:', error);
            SwalHelper.error('Error', 'Failed to update video.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check"></i> Save Changes';
        }
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Load categories for filter
    loadCategories();

    // Initialize list page and store instance
    const listPage = initializeListPage({
        apiEndpoint: '/admin/api/videos',
        renderRow: renderVideoRow,
        columnCount: 8
    });

    // Listen for video upload completion to refresh list
    window.addEventListener('videoListRefresh', () => {
        if (listPage && listPage.reloadCurrentPage) {
            listPage.reloadCurrentPage();
        }
    });

    // Initialize save edit handler
    initSaveEditHandler();

    // Event delegation for action buttons (data attributes approach)
    document.querySelector('#main-table')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const row = btn.closest('tr');
        const id = btn.dataset.id;
        const title = row?.dataset.title || '';
        const description = row?.dataset.description || '';
        const categoryId = btn.dataset.category;

        if (btn.classList.contains('btn-play')) {
            openVideo(btn.dataset.videoUrl);
        } else if (btn.classList.contains('btn-edit')) {
            editVideo(id, title, description, categoryId);
        } else if (btn.classList.contains('btn-delete')) {
            deleteVideo(id, title);
        } else if (btn.classList.contains('btn-restore')) {
            restoreVideo(id, title);
        }
    });
});
