<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Road Signs & Symbols', 'is_active' => 1],
            ['name' => 'Traffic Rules & Regulations', 'is_active' => 1],
            ['name' => 'Driving Basics', 'is_active' => 1],
            ['name' => 'Safety & Defensive Driving', 'is_active' => 1],
            ['name' => 'Parking & Maneuvering', 'is_active' => 1],
            ['name' => 'Road Awareness', 'is_active' => 1],
        ];

        $insertedCount = 0;

        foreach ($categories as $category) {
            // Check if category already exists
            $exists = $this->db->table('categories')
                ->where('name', $category['name'])
                ->countAllResults() > 0;

            if (!$exists) {
                $this->db->table('categories')->insert($category);
                $insertedCount++;
                echo "✓ Added: {$category['name']}\n";
            } else {
                echo "- Skipped: {$category['name']} (already exists)\n";
            }
        }

        echo "\nSuccessfully seeded {$insertedCount} new categories.\n";
    }
}
