<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'aadhar_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
            ],
            'document_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDING', 'APPROVED', 'REJECTED'],
                'default'    => 'PENDING',
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'idx_user_documents_user_id');
        $this->forge->addKey('reviewed_by', false, false, 'idx_user_documents_reviewed_by');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_user_documents_user_id');
        $this->forge->addForeignKey('reviewed_by', 'users', 'id', 'SET NULL', 'CASCADE', 'fk_user_documents_reviewed_by');
        $this->forge->createTable('user_documents');
    }

    public function down()
    {
        $this->forge->dropTable('user_documents');
    }
}
