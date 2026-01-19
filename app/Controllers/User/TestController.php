<?php

namespace App\Controllers\User;

class TestController extends BaseUserController
{
    public function instructions()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test_instructions', [
            'pageTitle' => 'Test Instructions',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'testDuration' => 30,
            'totalQuestions' => 20,
            'passingMarks' => 60
        ]);
    }

    public function index()
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test', [
            'pageTitle' => 'Online Test',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'remainingTime' => 1800,
            'currentQuestion' => 1
        ]);
    }

    public function result($testId = 1)
    {
        $user = $this->user();
        $profile = $this->getProfile();
        $document = $this->getDocument();

        return view('user/test_result', [
            'pageTitle' => 'Test Result',
            'profileVerificationStatus' => $profile['verification_status'] ?? 'PENDING',
            'documentStatus' => $document['status'] ?? 'NOT_UPLOADED',
            'testId' => $testId,
            'score' => 85,
            'totalQuestions' => 20,
            'correctAnswers' => 17,
            'result' => 'PASSED',
            'remarks' => 'Excellent performance!'
        ]);
    }
}
