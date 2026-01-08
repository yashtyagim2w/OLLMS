<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificates extends Migration
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
            'certificate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'certificate_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
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
        $this->forge->addKey('user_id', false, false, 'idx_certificates_user_id');
        $this->forge->addKey('certificate_number', false, true, 'uq_certificates_certificate_number');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_certificates_user_id');
        $this->forge->createTable('certificates');
    }

    public function down()
    {
        $this->forge->dropTable('certificates');
    }
}
