/**
 * Category Management Page JavaScript
 * Handles CRUD operations and drag-drop reordering
 */

// Use global escapeHtml
const escapeHtml = window.escapeHtml;

// State
let allCategories = [];
let sortableInstance = null;
let previousOrder = []; // Cache for order comparison

/**
 * Render category row
 */
function renderCategoryRow(category, index) {
    const isActive = category.is_active == 1;
    const statusBadge = isActive
        ? '<span class="badge badge-success">Active</span>'
        : '<span class="badge badge-secondary">Inactive</span>';

    const actionButtons = isActive ? `
        <button class="btn btn-sm btn-outline-primary btn-edit" title="Edit" 
                data-id="${category.id}" data-name="${escapeHtml(category.name)}">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete" data-id="${category.id}">
            <i class="bi bi-trash"></i>
        </button>
    ` : `
        <button class="btn btn-sm btn-success btn-restore" title="Restore" data-id="${category.id}">
            <i class="bi bi-arrow-counterclockwise"></i> Restore
        </button>
    `;

    // Format created_at
    const createdAt = category.created_at
        ? window.TimeUtils?.formatDateTime(category.created_at) || category.created_at.substring(0, 16).replace('T', ' ')
        : '-';

    // Video count
    const videoCount = category.video_count || 0;

    return `
        <tr data-id="${category.id}">
            <td class="drag-handle"><i class="bi bi-grip-vertical text-muted"></i></td>
            <td>${index + 1}</td>
            <td>${category.id}</td>
            <td>${escapeHtml(category.name)}</td>
            <td><span class="badge badge-primary">${videoCount}</span></td>
            <td>${statusBadge}</td>
            <td class="text-muted small">${createdAt}</td>
            <td class="actions">${actionButtons}</td>
        </tr>
    `;
}

/**
 * Load and render categories
 */
async function loadCategories() {
    const tableBody = document.getElementById('main-table');
    const status = document.getElementById('statusFilter')?.value || '';
    const sortBy = document.getElementById('sortByFilter')?.value || 'sort_order';
    const sortDir = document.getElementById('sortDirFilter')?.value || 'asc';
    const search = document.getElementById('searchInput')?.value.toLowerCase() || '';

    try {
        const response = await axios.get(`/admin/api/category-list?status=${status}&sort_by=${sortBy}&sort_dir=${sortDir}`);
        if (response.data.success) {
            allCategories = response.data.data || [];

            // Update previousOrder cache
            previousOrder = allCategories.map(c => c.id.toString());

            // Filter by search (client-side for instant response)
            let filtered = allCategories;
            if (search) {
                filtered = allCategories.filter(c =>
                    c.name.toLowerCase().includes(search)
                );
            }

            if (filtered.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 48px;"></i>
                            <p class="mt-2">No categories found</p>
                        </td>
                    </tr>
                `;
            } else {
                tableBody.innerHTML = filtered.map((cat, i) => renderCategoryRow(cat, i)).join('');
            }

            // Update category count
            const countEl = document.getElementById('categoryCount');
            if (countEl) {
                countEl.textContent = allCategories.length;
            }

            // Refresh sortable
            if (sortableInstance) {
                sortableInstance.refresh();
            }
        }
    } catch (error) {
        console.error('Failed to load categories:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5 text-danger">
                    Failed to load categories
                </td>
            </tr>
        `;
    }
}

/**
 * Handle reorder callback - only sends request if order changed
 */
async function handleReorder(newOrder) {
    // Check if order actually changed
    const newOrderStr = newOrder.join(',');
    const prevOrderStr = previousOrder.join(',');

    if (newOrderStr === prevOrderStr) {
        console.log('Order unchanged, skipping API call');
        return;
    }

    try {
        const response = await axios.post('/admin/api/category/reorder', {
            order: newOrder.map(id => parseInt(id))
        });

        if (response.data.success) {
            // Update cache with new order
            previousOrder = [...newOrder];
            SwalHelper.success('Success', 'Order saved successfully.');
        } else {
            SwalHelper.error('Error', response.data.message || 'Failed to save order');
        }
    } catch (error) {
        console.error('Reorder failed:', error);
        SwalHelper.error('Error', 'Failed to save order');
    } finally {
        loadCategories();
    }
}

/**
 * Create category with validation
 */
async function createCategory(name) {
    const config = window.CategoryValidation || { minLength: 2, maxLength: 50, sanitizeRegex: /[^A-Za-z0-9\s\-&]/g };

    // Sanitize name using config regex
    const sanitized = name.replace(config.sanitizeRegex, '').trim();

    if (sanitized.length < config.minLength || sanitized.length > config.maxLength) {
        SwalHelper.error('Validation Error', `Category name must be ${config.minLength}-${config.maxLength} characters.`);
        return false;
    }

    try {
        const response = await axios.post('/admin/api/category', { name: sanitized });

        if (response.data.success) {
            SwalHelper.success('Success', 'Category created successfully.');
            document.getElementById('categoryName').value = '';
            loadCategories();
            return true;
        } else {
            SwalHelper.error('Error', response.data.message || 'Failed to create category');
            return false;
        }
    } catch (error) {
        console.error('Create failed:', error);
        SwalHelper.error('Error', error.response?.data?.message || 'Failed to create category');
        return false;
    }
}

/**
 * Update category with change detection
 */
async function updateCategory(id, name, originalName) {
    const config = window.CategoryValidation || { minLength: 2, maxLength: 50, sanitizeRegex: /[^A-Za-z0-9\s\-&]/g };

    // Sanitize name using config regex
    const sanitized = name.replace(config.sanitizeRegex, '').trim();

    // Check for changes before sending request
    if (sanitized === originalName.trim()) {
        SwalHelper.warning('No Changes', 'Category name has not been modified.');
        return false;
    }

    if (sanitized.length < config.minLength || sanitized.length > config.maxLength) {
        SwalHelper.error('Validation Error', `Category name must be ${config.minLength}-${config.maxLength} characters.`);
        return false;
    }

    try {
        const response = await axios.put(`/admin/api/category/${id}`, {
            name: sanitized,
            original_name: originalName
        });

        if (response.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
            SwalHelper.success('Success', 'Category updated successfully.');
            loadCategories();
            return true;
        } else {
            SwalHelper.error('Error', response.data.message || 'Failed to update category');
            return false;
        }
    } catch (error) {
        console.error('Update failed:', error);
        SwalHelper.error('Error', error.response?.data?.message || 'Failed to update category');
        return false;
    }
}

/**
 * Delete category
 */
async function deleteCategory(id) {
    const result = await SwalHelper.confirmDanger('Delete Category?',
        'This will deactivate the category. Videos in this category will remain but the category won\'t appear in dropdowns.');

    if (result.isConfirmed) {
        try {
            const response = await axios.delete(`/admin/api/category/${id}`);

            if (response.data.success) {
                SwalHelper.success('Deleted', 'Category has been deactivated.');
                loadCategories();
            } else {
                SwalHelper.error('Error', response.data.message || 'Failed to delete category');
            }
        } catch (error) {
            console.error('Delete failed:', error);
            SwalHelper.error('Error', 'Failed to delete category');
        }
    }
}

/**
 * Restore category
 */
async function restoreCategory(id) {
    const result = await SwalHelper.confirm('Restore Category?', 'This will reactivate the category.');

    if (result.isConfirmed) {
        try {
            const response = await axios.post(`/admin/api/category/${id}/restore`);

            if (response.data.success) {
                SwalHelper.success('Restored', 'Category has been restored.');
                loadCategories();
            } else {
                SwalHelper.error('Error', response.data.message || 'Failed to restore category');
            }
        } catch (error) {
            console.error('Restore failed:', error);
            SwalHelper.error('Error', 'Failed to restore category');
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Load initial data
    loadCategories();

    // Initialize sortable
    sortableInstance = window.initSortable({
        containerSelector: '#main-table',
        itemSelector: 'tr[data-id]',
        handleSelector: '.drag-handle',
        idAttribute: 'data-id',
        onReorder: handleReorder
    });

    // Create form handler
    document.getElementById('createCategoryForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nameInput = document.getElementById('categoryName');
        const name = nameInput.value.trim();

        if (!name || name.length < 2) {
            SwalHelper.warning('Validation', 'Category name must be at least 2 characters.');
            return;
        }

        const btn = document.getElementById('createBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

        await createCategory(name);

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Create Category';
    });

    // Filter handlers
    document.getElementById('statusFilter')?.addEventListener('change', loadCategories);
    document.getElementById('sortByFilter')?.addEventListener('change', loadCategories);
    document.getElementById('sortDirFilter')?.addEventListener('change', loadCategories);

    // Reset filters button
    document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('sortByFilter').value = 'sort_order';
        document.getElementById('sortDirFilter').value = 'asc';
        loadCategories();
    });

    // Export CSV button - uses backend API
    document.getElementById('exportBtn')?.addEventListener('click', () => {
        // Get current filter values
        const status = document.getElementById('statusFilter')?.value || '';
        const sortBy = document.getElementById('sortByFilter')?.value || 'sort_order';
        const sortDir = document.getElementById('sortDirFilter')?.value || 'asc';

        // Build export URL with current filters
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (sortBy) params.append('sort_by', sortBy);
        if (sortDir) params.append('sort_dir', sortDir);

        // Redirect to backend export endpoint
        window.location.href = `/admin/api/category/export?${params.toString()}`;
    });

    let searchTimeout;
    document.getElementById('searchInput')?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadCategories, 300);
    });

    // Event delegation for action buttons
    document.getElementById('main-table')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const id = btn.dataset.id;

        if (btn.classList.contains('btn-edit')) {
            // Open edit modal
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = btn.dataset.name;
            document.getElementById('editOriginalName').value = btn.dataset.name;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        } else if (btn.classList.contains('btn-delete')) {
            deleteCategory(id);
        } else if (btn.classList.contains('btn-restore')) {
            restoreCategory(id);
        }
    });

    // Save edit handler
    document.getElementById('saveEditBtn')?.addEventListener('click', async () => {
        const id = document.getElementById('editCategoryId').value;
        const name = document.getElementById('editCategoryName').value.trim();
        const originalName = document.getElementById('editOriginalName').value;

        if (!name || name.length < 2) {
            SwalHelper.warning('Validation', 'Category name must be at least 2 characters.');
            return;
        }

        const btn = document.getElementById('saveEditBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        await updateCategory(id, name, originalName);

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check"></i> Save Changes';
    });
});
