<?php

namespace App\Controllers\Admin;

use App\Traits\ResponseTrait;
use App\Services\CategoryService;
use App\Models\CategoryModel;

/**
 * Category Management Controller
 * 
 * Handles admin category CRUD and reordering
 */
class CategoryManagementController extends \App\Controllers\BaseController
{
    use ResponseTrait;

    protected CategoryService $categoryService;
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Display category management page
     */
    public function index()
    {
        return view('admin/categories', [
            'pageTitle' => 'Category Management',
            'showSidebar' => true,
        ]);
    }

    /**
     * API: Get categories list with filters and sorting
     */
    public function apiGetList()
    {
        $status = $this->request->getGet('status') ?: null;
        $sortBy = $this->request->getGet('sort_by') ?: 'sort_order';
        $sortDir = $this->request->getGet('sort_dir') ?: 'ASC';

        // Get categories from model
        $categories = $this->categoryModel->getCategoriesWithVideoCount($status, $sortBy, $sortDir);

        return $this->jsonSuccess('Categories fetched.', $categories);
    }

    /**
     * API: Create new category
     */
    public function apiCreate()
    {
        $json = $this->request->getJSON(true);
        $name = trim($json['name'] ?? '');

        $result = $this->categoryService->createCategory(['name' => $name]);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Category created successfully.', ['id' => $result['id']]);
    }

    /**
     * API: Update category
     */
    public function apiUpdate($id)
    {
        $json = $this->request->getJSON(true);
        $name = trim($json['name'] ?? '');
        $originalName = trim($json['original_name'] ?? '');

        $result = $this->categoryService->updateCategory(
            (int) $id,
            ['name' => $name],
            ['name' => $originalName]
        );

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Category updated successfully.');
    }

    /**
     * API: Soft delete category
     */
    public function apiDelete($id)
    {
        $result = $this->categoryService->deleteCategory((int) $id);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Category deleted successfully.');
    }

    /**
     * API: Restore category
     */
    public function apiRestore($id)
    {
        $result = $this->categoryService->restoreCategory((int) $id);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Category restored successfully.');
    }

    /**
     * API: Reorder categories
     */
    public function apiReorder()
    {
        $json = $this->request->getJSON(true);
        $order = $json['order'] ?? [];

        if (!is_array($order) || empty($order)) {
            return $this->jsonError('Invalid order data.');
        }

        // Convert to integers
        $orderedIds = array_map('intval', $order);

        $result = $this->categoryService->reorderCategories($orderedIds);

        if (!$result['success']) {
            return $this->jsonError($result['error']);
        }

        return $this->jsonSuccess('Order updated successfully.');
    }

    /**
     * Export Categories to CSV
     */
    public function export()
    {
        $status = $this->request->getGet('status') ?: null;
        $sortBy = $this->request->getGet('sort_by') ?: 'sort_order';
        $sortDir = $this->request->getGet('sort_dir') ?: 'ASC';

        // Get all the data
        $categoriesList = $this->categoryModel->getCategoriesWithVideoCount($status, $sortBy, $sortDir);

        // Prepare CSV data
        $csvData = [];

        // CSV header
        $csvData[] = [
            'S.No',
            'ID',
            'Name',
            'Videos',
            'Status',
            'Created At',
        ];

        // Add data rows
        $i = 1;
        foreach ($categoriesList as $category) {
            $csvData[] = [
                $i++,
                $category['id'],
                $category['name'],
                $category['video_count'],
                $category['is_active'] == 1 ? 'Active' : 'Inactive',
                $category['created_at']
            ];
        }

        // Generate filename with current date
        $filename = 'category_export_' . date('Y-m-d') . '.csv';

        // Set headers for CSV download
        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->arrayToCsv($csvData));
    }
}
