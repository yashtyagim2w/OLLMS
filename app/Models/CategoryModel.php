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
    protected $allowedFields = ['name', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all active categories
     *
     * @return array
     */
    public function getActiveCategories(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->orderBy('name', 'ASC')
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
}
