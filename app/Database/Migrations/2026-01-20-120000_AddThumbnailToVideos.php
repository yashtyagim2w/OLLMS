<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThumbnailToVideos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('training_videos', [
            'thumbnail_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'video_url',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('training_videos', 'thumbnail_url');
    }
}
