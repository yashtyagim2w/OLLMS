<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrainingVideos extends Migration
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
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'video_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'duration_seconds' => [
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
        $this->forge->addKey('category_id', false, false, 'idx_training_videos_category_id');
        $this->forge->addKey('is_active', false, false, 'idx_training_videos_is_active');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE', 'fk_training_videos_category_id');
        $this->forge->createTable('training_videos');
    }

    public function down()
    {
        $this->forge->dropTable('training_videos');
    }
}
