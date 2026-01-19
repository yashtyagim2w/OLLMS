<?php

namespace App\Controllers\Admin;

/**
 * Admin Profile Controller
 */
class ProfileController extends BaseAdminController
{
    /**
     * Admin Profile Page
     */
    public function index()
    {
        $user = $this->adminUser();
        $profileModel = model('App\Models\UserProfileModel');
        $profile = null;

        if ($profileModel) {
            $profile = $profileModel->where('user_id', $user->id)->first();
        }

        return view('admin/profile', [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'profile' => $profile
        ]);
    }
}
