<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVideoProgress extends Migration
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
            'video_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'watched_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'last_position_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED'],
                'default'    => 'NOT_STARTED',
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
        $this->forge->addKey('user_id', false, false, 'idx_video_progress_user_id');
        $this->forge->addKey('video_id', false, false, 'idx_video_progress_video_id');
        $this->forge->addKey(['user_id', 'video_id'], false, true, 'uq_video_progress_user_video');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_video_progress_user_id');
        $this->forge->addForeignKey('video_id', 'training_videos', 'id', 'CASCADE', 'CASCADE', 'fk_video_progress_video_id');
        $this->forge->createTable('video_progress');
    }

    public function down()
    {
        $this->forge->dropTable('video_progress');
    }
}
