<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfiles extends Migration
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
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'dob' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'verification_status' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDING', 'COMPLETED'],
                'default'    => 'PENDING',
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
        $this->forge->addKey('user_id', false, false, 'idx_user_profiles_user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_user_profiles_user_id');
        $this->forge->createTable('user_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('user_profiles');
    }
}
