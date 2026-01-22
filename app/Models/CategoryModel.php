<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['name', 'is_active', 'sort_order'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all active categories ordered by sort_order
     *
     * @return array
     */
    public function getActiveCategories(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get all categories ordered by sort_order
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Check if category exists
     *
     * @param int $id
     * @return bool
     */
    public function categoryExists(int $id): bool
    {
        return $this->where('id', $id)->countAllResults() > 0;
    }

    /**
     * Get next available sort_order value
     *
     * @return int
     */
    public function getNextSortOrder(): int
    {
        $result = $this->selectMax('sort_order', 'max_order')->first();
        return ($result['max_order'] ?? 0) + 1;
    }

    /**
     * Update sort_order for multiple categories
     * 
     * @param array $orderedIds Array of category IDs in desired order
     * @return bool
     */
    public function updateSortOrder(array $orderedIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($orderedIds as $index => $id) {
            $this->update($id, ['sort_order' => $index + 1]);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Soft delete category (set is_active = 0)
     *
     * @param int $id
     * @return bool
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Restore category (set is_active = 1)
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Get categories with video count for admin listing
     *
     * @param string|null $status Filter by status ('active', 'inactive', or null for all)
     * @param string $sortBy Column to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @return array
     */
    public function getCategoriesWithVideoCount(?string $status = null, string $sortBy = 'sort_order', string $sortDir = 'ASC'): array
    {
        // Validate sort direction
        $sortDir = strtoupper($sortDir);
        if (!in_array($sortDir, ['ASC', 'DESC'])) {
            $sortDir = 'ASC';
        }

        // Validate sort column
        $allowedSortColumns = ['sort_order', 'video_count', 'created_at', 'updated_at', 'name', 'id'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'sort_order';
        }

        $db = \Config\Database::connect();

        $builder = $db->table('categories c')
            ->select('c.*, COUNT(v.id) as video_count')
            ->join('training_videos v', 'v.category_id = c.id AND v.is_active = 1', 'left')
            ->groupBy('c.id');

        // Apply status filter
        if ($status === 'active') {
            $builder->where('c.is_active', 1);
        } elseif ($status === 'inactive') {
            $builder->where('c.is_active', 0);
        }

        // Apply sorting
        $sortColumn = $sortBy === 'video_count' ? 'video_count' : "c.{$sortBy}";
        $builder->orderBy($sortColumn, $sortDir);

        return $builder->get()->getResultArray();
    }
}
