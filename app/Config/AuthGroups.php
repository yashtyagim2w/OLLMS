<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * Newly registered users will be added to this group.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * Only two groups are required for this system:
     * - admin (RTO officials)
     * - user (license applicants)
     */
    public array $groups = [
        'admin' => [
            'title'       => 'RTO Admin',
            'description' => 'RTO officials with full administrative access.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Learner license applicants.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * Define only the permissions actually needed by the system.
     */
    public array $permissions = [
        'admin.access' => 'Access admin dashboard',
        'users.verify' => 'Approve or reject user identity verification',
        'videos.manage' => 'Upload and manage training videos',
        'questions.manage' => 'Manage learner test questions',
        'reports.view' => 'View and export reports',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Map permissions to groups.
     */
    public array $matrix = [
        'admin' => [
            'admin.access',
            'users.verify',
            'videos.manage',
            'questions.manage',
            'reports.view',
        ],
        'user' => [
            // No special permissions
        ],
    ];
}
