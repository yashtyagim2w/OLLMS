<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSortOrderToCategories extends Migration
{
    public function up()
    {
        // Add sort_order column
        $this->forge->addColumn('categories', [
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'is_active',
            ],
        ]);

        // Add index for sort_order
        $this->db->query('CREATE INDEX idx_categories_sort_order ON categories(sort_order)');

        // Set initial sort_order based on existing IDs
        $this->db->query('UPDATE categories SET sort_order = id');
    }

    public function down()
    {
        // Drop index first
        $this->db->query('DROP INDEX idx_categories_sort_order ON categories');

        // Remove column
        $this->forge->dropColumn('categories', 'sort_order');
    }
}
