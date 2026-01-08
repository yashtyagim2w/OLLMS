<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInstructions extends Migration
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
            'description' => [
                'type' => 'TEXT',
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addKey('display_order', false, false, 'idx_instructions_display_order');
        $this->forge->createTable('instructions');
    }

    public function down()
    {
        $this->forge->dropTable('instructions');
    }
}
