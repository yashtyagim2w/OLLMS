<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTests extends Migration
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
            'total_questions' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'score' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'result' => [
                'type'       => 'ENUM',
                'constraint' => ['PASS', 'FAIL'],
                'default'    => 'FAIL',
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
        $this->forge->addKey('user_id', false, false, 'idx_tests_user_id');
        $this->forge->addKey('result', false, false, 'idx_tests_result');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_tests_user_id');
        $this->forge->createTable('tests');
    }

    public function down()
    {
        $this->forge->dropTable('tests');
    }
}
