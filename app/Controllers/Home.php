<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Landing page - redirects based on auth status and role
     */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        // Check if user is logged in
        if (auth()->loggedIn()) {
            $user = auth()->user();

            // Redirect admin to admin dashboard
            if ($user->inGroup('admin')) {
                return redirect()->to('/admin/dashboard');
            }

            // Redirect regular user to user dashboard
            return redirect()->to('/dashboard');
        }

        // Not logged in - show login page
        return redirect()->to('/login');
    }
}
