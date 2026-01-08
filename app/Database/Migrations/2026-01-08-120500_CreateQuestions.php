<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestions extends Migration
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
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'difficulty_level' => [
                'type'       => 'ENUM',
                'constraint' => ['EASY', 'MEDIUM', 'HARD'],
                'default'    => 'EASY',
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'question' => [
                'type' => 'TEXT',
            ],
            'option1' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'option2' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'option3' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'option4' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'correct_option' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'comment'    => '1=option1, 2=option2, 3=option3, 4=option4',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('category_id', false, false, 'idx_questions_category_id');
        $this->forge->addKey('difficulty_level', false, false, 'idx_questions_difficulty_level');
        $this->forge->addKey('is_active', false, false, 'idx_questions_is_active');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE', 'fk_questions_category_id');
        $this->forge->createTable('questions');
    }

    public function down()
    {
        $this->forge->dropTable('questions');
    }
}
