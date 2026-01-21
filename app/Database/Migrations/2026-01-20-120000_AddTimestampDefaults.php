<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimestampDefaults extends Migration
{
    public function up()
    {
        $tables = [
            'categories',
            'training_videos',
            'video_progress',
            'questions',
            'tests',
            'test_questions',
            'certificates',
            'instructions',
            'user_profiles',
            'user_documents',
        ];

        foreach ($tables as $table) {
            // Check if table exists
            if (!$this->db->tableExists($table)) {
                continue;
            }

            // Check if created_at column exists
            if ($this->db->fieldExists('created_at', $table)) {
                // Modify created_at to have default CURRENT_TIMESTAMP
                $this->forge->modifyColumn($table, [
                    'created_at' => [
                        'type' => 'TIMESTAMP',
                        'null' => false,
                        'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
                    ],
                ]);
            }

            // Check if updated_at column exists
            if ($this->db->fieldExists('updated_at', $table)) {
                // Modify updated_at to have default CURRENT_TIMESTAMP and ON UPDATE CURRENT_TIMESTAMP
                $sql = "ALTER TABLE `{$table}` 
                        MODIFY `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                $this->db->query($sql);
            }
        }
    }

    public function down()
    {
        $tables = [
            'categories',
            'training_videos',
            'video_progress',
            'questions',
            'tests',
            'test_questions',
            'certificates',
            'instructions',
            'user_profiles',
            'user_documents',
        ];

        foreach ($tables as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            // Revert created_at if exists
            if ($this->db->fieldExists('created_at', $table)) {
                $this->forge->modifyColumn($table, [
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
            }

            // Revert updated_at if exists
            if ($this->db->fieldExists('updated_at', $table)) {
                $this->forge->modifyColumn($table, [
                    'updated_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
            }
        }
    }
}
