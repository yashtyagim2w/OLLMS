<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

/**
 * Add/Update Foreign Key Constraints with CASCADE DELETE
 * 
 * This migration ensures all user-related tables have proper
 * ON DELETE CASCADE constraints so deleting a user wipes all related data.
 */
class AddCascadeDeleteConstraints extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Get all existing foreign keys for user_profiles
        $this->dropForeignKeyIfExists('user_profiles', 'fk_user_profiles_user_id');
        $this->dropForeignKeyIfExists('user_profiles', 'user_profiles_user_id_foreign');

        // Get all existing foreign keys for user_documents
        $this->dropForeignKeyIfExists('user_documents', 'fk_user_documents_user_id');
        $this->dropForeignKeyIfExists('user_documents', 'user_documents_user_id_foreign');
        $this->dropForeignKeyIfExists('user_documents', 'fk_user_documents_reviewed_by');
        $this->dropForeignKeyIfExists('user_documents', 'user_documents_reviewed_by_foreign');

        // Get all existing foreign keys for video_progress
        $this->dropForeignKeyIfExists('video_progress', 'fk_video_progress_user_id');
        $this->dropForeignKeyIfExists('video_progress', 'video_progress_user_id_foreign');

        // Get all existing foreign keys for tests
        $this->dropForeignKeyIfExists('tests', 'fk_tests_user_id');
        $this->dropForeignKeyIfExists('tests', 'tests_user_id_foreign');

        // Get all existing foreign keys for certificates
        $this->dropForeignKeyIfExists('certificates', 'fk_certificates_user_id');
        $this->dropForeignKeyIfExists('certificates', 'certificates_user_id_foreign');

        // Now add the constraints with CASCADE DELETE

        // user_profiles.user_id -> users.id
        $db->query('ALTER TABLE user_profiles 
            ADD CONSTRAINT fk_user_profiles_user_id 
            FOREIGN KEY (user_id) REFERENCES users(id) 
            ON DELETE CASCADE ON UPDATE CASCADE');

        // user_documents.user_id -> users.id
        $db->query('ALTER TABLE user_documents 
            ADD CONSTRAINT fk_user_documents_user_id 
            FOREIGN KEY (user_id) REFERENCES users(id) 
            ON DELETE CASCADE ON UPDATE CASCADE');

        // user_documents.reviewed_by -> users.id (SET NULL on delete)
        $db->query('ALTER TABLE user_documents 
            ADD CONSTRAINT fk_user_documents_reviewed_by 
            FOREIGN KEY (reviewed_by) REFERENCES users(id) 
            ON DELETE SET NULL ON UPDATE CASCADE');

        // video_progress.user_id -> users.id
        $db->query('ALTER TABLE video_progress 
            ADD CONSTRAINT fk_video_progress_user_id 
            FOREIGN KEY (user_id) REFERENCES users(id) 
            ON DELETE CASCADE ON UPDATE CASCADE');

        // tests.user_id -> users.id
        $db->query('ALTER TABLE tests 
            ADD CONSTRAINT fk_tests_user_id 
            FOREIGN KEY (user_id) REFERENCES users(id) 
            ON DELETE CASCADE ON UPDATE CASCADE');

        // certificates.user_id -> users.id
        $db->query('ALTER TABLE certificates 
            ADD CONSTRAINT fk_certificates_user_id 
            FOREIGN KEY (user_id) REFERENCES users(id) 
            ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        // Remove the constraints (they'll be re-added by original migrations if needed)
        $this->dropForeignKeyIfExists('user_profiles', 'fk_user_profiles_user_id');
        $this->dropForeignKeyIfExists('user_documents', 'fk_user_documents_user_id');
        $this->dropForeignKeyIfExists('user_documents', 'fk_user_documents_reviewed_by');
        $this->dropForeignKeyIfExists('video_progress', 'fk_video_progress_user_id');
        $this->dropForeignKeyIfExists('tests', 'fk_tests_user_id');
        $this->dropForeignKeyIfExists('certificates', 'fk_certificates_user_id');
    }

    /**
     * Helper to drop foreign key if it exists (prevents errors)
     */
    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        $db = \Config\Database::connect();

        try {
            // Check if constraint exists
            $result = $db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ? 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$table, $constraintName]);

            if ($result->getNumRows() > 0) {
                $db->query("ALTER TABLE {$table} DROP FOREIGN KEY {$constraintName}");
            }
        } catch (\Exception $e) {
            // Silently ignore if constraint doesn't exist
            log_message('debug', "Could not drop constraint {$constraintName}: " . $e->getMessage());
        }
    }
}
