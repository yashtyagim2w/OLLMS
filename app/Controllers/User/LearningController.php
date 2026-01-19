<?php

namespace App\Controllers\User;

class LearningController extends BaseUserController
{
    public function index()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/videos', [
            'pageTitle' => 'Training Videos',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'completedVideos' => 0,
            'totalVideos' => 10,
        ]);
    }

    public function player($videoId = 1)
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/video_player', [
            'pageTitle' => 'Video Player',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'videoId' => $videoId,
            'videoTitle' => 'Traffic Signs Introduction',
            'duration' => '10:30',
            'videoUrl' => 'https://example.com/video.mp4',
            'currentProgress' => 45,
            'nextVideoId' => $videoId + 1,
        ]);
    }

    public function progress()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/video_progress', [
            'pageTitle' => 'My Progress',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'totalVideos' => 10,
            'completedVideos' => 5,
            'lastWatched' => 'Traffic Signs Introduction',
            'overallProgress' => 50
        ]);
    }
}
