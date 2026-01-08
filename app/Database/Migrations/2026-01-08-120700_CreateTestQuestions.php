<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestQuestions extends Migration
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
            'test_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'question_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_answer' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'comment'    => '1=option1, 2=option2, 3=option3, 4=option4',
            ],
            'is_correct' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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
        $this->forge->addKey('test_id', false, false, 'idx_test_questions_test_id');
        $this->forge->addKey('question_id', false, false, 'idx_test_questions_question_id');
        $this->forge->addKey(['test_id', 'question_id'], false, true, 'uq_test_questions_test_question');
        $this->forge->addForeignKey('test_id', 'tests', 'id', 'CASCADE', 'CASCADE', 'fk_test_questions_test_id');
        $this->forge->addForeignKey('question_id', 'questions', 'id', 'CASCADE', 'CASCADE', 'fk_test_questions_question_id');
        $this->forge->createTable('test_questions');
    }

    public function down()
    {
        $this->forge->dropTable('test_questions');
    }
}
