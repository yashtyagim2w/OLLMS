<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Add Soft Delete Support to User Profiles
 * 
 * Adds deleted_at column to user_profiles table only
 * Documents are never deleted - we keep full history
 */
class AddSoftDeleteColumns extends Migration
{
    public function up()
    {
        // Add deleted_at to user_profiles only
        $this->forge->addColumn('user_profiles', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ]);
    }

    public function down()
    {
        // Remove deleted_at from user_profiles
        $this->forge->dropColumn('user_profiles', 'deleted_at');
    }
}
