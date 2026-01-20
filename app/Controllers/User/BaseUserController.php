<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserProfileModel;
use App\Models\UserDocumentModel;

/**
 * Base User Controller
 * Shared functionality for all user controllers
 */
abstract class BaseUserController extends BaseController
{
    protected UserProfileModel $profileModel;
    protected UserDocumentModel $documentModel;

    public function __construct()
    {
        $this->profileModel = new UserProfileModel();
        $this->documentModel = new UserDocumentModel();
    }

    /**
     * Get current user helper
     */
    protected function user()
    {
        return auth()->user();
    }

    /**
     * Get current user's profile
     */
    protected function getProfile(): ?array
    {
        $user = $this->user();
        return $user ? $this->profileModel->where('user_id', $user->id)->first() : null;
    }

    /**
     * Get current user's document (latest)
     */
    protected function getDocument(): ?array
    {
        $user = $this->user();
        return $user ? $this->documentModel->getLatestDocument($user->id) : null;
    }
}
