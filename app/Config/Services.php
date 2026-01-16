<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * S3 Storage Service
     * Usage: service('s3')->upload($file, 'path')
     */
    public static function s3(bool $getShared = true): \App\Services\S3Service
    {
        if ($getShared) {
            return static::getSharedInstance('s3');
        }

        return new \App\Services\S3Service();
    }

    /**
     * User Management Service
     * Usage: service('users')->getUsers($filters)
     */
    public static function users(bool $getShared = true): \App\Services\UserService
    {
        if ($getShared) {
            return static::getSharedInstance('users');
        }

        return new \App\Services\UserService();
    }

    /**
     * Document Verification Service
     * Usage: service('documents')->approveDocument($id, $adminId, $remarks)
     */
    public static function documents(bool $getShared = true): \App\Services\DocumentService
    {
        if ($getShared) {
            return static::getSharedInstance('documents');
        }

        return new \App\Services\DocumentService();
    }
}
