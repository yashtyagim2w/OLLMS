<?php

namespace App\Services;

use App\Models\CategoryModel;

/**
 * Category Service
 * 
 * Handles business logic for category management
 */
class CategoryService
{
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Create a new category
     *
     * @param array $data ['name']
     * @return array ['success' => bool, 'id' => int|null, 'error' => string|null]
     */
    public function createCategory(array $data): array
    {
        helper('validation');

        $name = trim($data['name'] ?? '');

        // Sanitize using validation helper
        $name = sanitize_category_name($name);
        $name = trim($name);

        // Validation
        if (empty($name)) {
            return ['success' => false, 'error' => 'Category name is required.'];
        }

        if (strlen($name) < CATEGORY_NAME_MIN_LENGTH || strlen($name) > CATEGORY_NAME_MAX_LENGTH) {
            return ['success' => false, 'error' => 'Category name must be ' . CATEGORY_NAME_MIN_LENGTH . '-' . CATEGORY_NAME_MAX_LENGTH . ' characters.'];
        }

        // Check for duplicate name (case-insensitive)
        $existing = $this->categoryModel
            ->where('LOWER(name)', strtolower($name))
            ->first();
        if ($existing) {
            return ['success' => false, 'error' => 'A category with this name already exists.'];
        }

        // Get next sort order
        $sortOrder = $this->categoryModel->getNextSortOrder();

        // Insert
        $id = $this->categoryModel->insert([
            'name' => $name,
            'is_active' => 1,
            'sort_order' => $sortOrder,
        ]);

        if (!$id) {
            return ['success' => false, 'error' => 'Failed to create category.'];
        }

        return ['success' => true, 'id' => $id];
    }

    /**
     * Update category
     *
     * @param int $id Category ID
     * @param array $data ['name']
     * @param array $original ['name'] for change detection
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updateCategory(int $id, array $data, array $original = []): array
    {
        helper('validation');

        // Check category exists
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return ['success' => false, 'error' => 'Category not found.'];
        }

        $name = trim($data['name'] ?? '');

        // Sanitize using validation helper
        $name = sanitize_category_name($name);
        $name = trim($name);

        // Validation
        if (empty($name)) {
            return ['success' => false, 'error' => 'Category name is required.'];
        }

        if (strlen($name) < CATEGORY_NAME_MIN_LENGTH || strlen($name) > CATEGORY_NAME_MAX_LENGTH) {
            return ['success' => false, 'error' => 'Category name must be ' . CATEGORY_NAME_MIN_LENGTH . '-' . CATEGORY_NAME_MAX_LENGTH . ' characters.'];
        }

        // Change detection
        $originalName = $original['name'] ?? $category['name'];
        if ($name === $originalName) {
            return ['success' => false, 'error' => 'No changes detected.'];
        }

        // Check for duplicate name (excluding current, case-insensitive)
        $existing = $this->categoryModel
            ->where('LOWER(name)', strtolower($name))
            ->where('id !=', $id)
            ->first();
        if ($existing) {
            return ['success' => false, 'error' => 'A category with this name already exists.'];
        }

        // Update
        $result = $this->categoryModel->update($id, ['name' => $name]);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to update category.'];
        }

        return ['success' => true];
    }

    /**
     * Soft delete category
     *
     * @param int $id Category ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function deleteCategory(int $id): array
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return ['success' => false, 'error' => 'Category not found.'];
        }

        if ($category['is_active'] == 0) {
            return ['success' => false, 'error' => 'Category is already inactive.'];
        }

        $result = $this->categoryModel->softDelete($id);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to delete category.'];
        }

        return ['success' => true];
    }

    /**
     * Restore category
     *
     * @param int $id Category ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function restoreCategory(int $id): array
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return ['success' => false, 'error' => 'Category not found.'];
        }

        if ($category['is_active'] == 1) {
            return ['success' => false, 'error' => 'Category is already active.'];
        }

        $result = $this->categoryModel->restore($id);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to restore category.'];
        }

        return ['success' => true];
    }

    /**
     * Reorder categories
     *
     * @param array $orderedIds Array of category IDs in new order
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function reorderCategories(array $orderedIds): array
    {
        if (empty($orderedIds)) {
            return ['success' => false, 'error' => 'No order provided.'];
        }

        // Validate all IDs exist
        foreach ($orderedIds as $id) {
            if (!is_numeric($id) || !$this->categoryModel->categoryExists((int) $id)) {
                return ['success' => false, 'error' => 'Invalid category ID in order.'];
            }
        }

        $result = $this->categoryModel->updateSortOrder($orderedIds);

        if (!$result) {
            return ['success' => false, 'error' => 'Failed to update order.'];
        }

        return ['success' => true];
    }
}
