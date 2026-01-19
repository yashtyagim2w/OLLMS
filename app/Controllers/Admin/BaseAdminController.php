<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Base Admin Controller
 * Shared functionality for all admin controllers
 */
abstract class BaseAdminController extends BaseController
{
    /**
     * Get current admin user
     */
    protected function adminUser()
    {
        return auth()->user();
    }
}
