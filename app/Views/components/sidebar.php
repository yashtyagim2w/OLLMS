<?php

/**
 * Sidebar Component
 * Navigation for User/Admin based on role
 * Uses arrays and loops for menu items
 */
$currentUser = auth()->user();
$isAdmin = $currentUser ? $currentUser->inGroup('admin') : false;
$currentPath = uri_string();

// Get both verification statuses (passed from layout)
$profileVerificationStatus = $profileVerificationStatus ?? 'PENDING';  // email verification
$documentStatus = $documentStatus ?? 'NOT_UPLOADED';  // document approval status (NOT_UPLOADED, PENDING, REJECTED, APPROVED)

// User can access full features only if BOTH conditions are met:
// 1. Profile verification is COMPLETED (email verified)
// 2. Document status is APPROVED (admin approved identity)
$isFullyVerified = ($profileVerificationStatus === 'COMPLETED' && $documentStatus === 'APPROVED');

// Get pending verification count (passed from layout or default to 0)
$pendingVerificationCount = $pendingVerificationCount ?? 0;
// Format count for display (show 100+ if > 100)
$pendingBadge = $pendingVerificationCount > 100 ? '100+' : ($pendingVerificationCount > 0 ? $pendingVerificationCount : null);

// Admin Menu Structure
$adminMenu = [
    [
        'title' => 'Main',
        'items' => [
            ['path' => '/admin/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ]
    ],
    [
        'title' => 'Verification',
        'items' => [
            ['path' => '/admin/identity-review', 'icon' => 'bi-person-check', 'label' => 'Identity Review', 'badge' => $pendingBadge],
            ['path' => '/admin/users', 'icon' => 'bi-people', 'label' => 'User Management'],
        ]
    ],
    [
        'title' => 'Content',
        'items' => [
            ['path' => '/admin/videos', 'icon' => 'bi-play-circle', 'label' => 'Video Management'],
            ['path' => '/admin/questions', 'icon' => 'bi-question-circle', 'label' => 'Question Bank'],
            ['path' => '/admin/instructions', 'icon' => 'bi-list-check', 'label' => 'Test Instructions'],
        ]
    ],
    [
        'title' => 'Reports',
        'items' => [
            ['path' => '/admin/progress', 'icon' => 'bi-graph-up', 'label' => 'Progress Monitoring'],
            ['path' => '/admin/reports', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Reports & Analytics'],
        ]
    ]
];

// User Menu Structure (conditional based on verification)
// Dashboard is ALWAYS visible to all users
$userMenu = [
    [
        'title' => 'Main',
        'items' => [
            ['path' => '/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
        ]
    ],
];

// Add more menu items only if user is fully verified
// (both email verified AND documents approved by admin)
if ($isFullyVerified) {
    $userMenu[] = [
        'title' => 'Learning',
        'items' => [
            ['path' => '/videos', 'icon' => 'bi-play-circle', 'label' => 'Training Videos'],
            ['path' => '/video-progress', 'icon' => 'bi-bar-chart', 'label' => 'My Progress'],
        ]
    ];
    $userMenu[] = [
        'title' => 'Test',
        'items' => [
            ['path' => '/test-instructions', 'icon' => 'bi-info-circle', 'label' => 'Test Instructions'],
            ['path' => '/test', 'icon' => 'bi-pencil-square', 'label' => 'Take Test'],
        ]
    ];
    $userMenu[] = [
        'title' => 'Certificate',
        'items' => [
            ['path' => '/certificate', 'icon' => 'bi-award', 'label' => 'My Certificate'],
        ]
    ];
}

// Select menu based on role
$menu = $isAdmin ? $adminMenu : $userMenu;
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <div class="sidebar-brand">
            <h1>OLLMS</h1>
            <span>Learner's License Portal</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $section): ?>
            <div class="nav-section">
                <div class="nav-section-title"><?= $section['title'] ?></div>
                <?php foreach ($section['items'] as $item): ?>
                    <?php
                    $isActive = '/' . $currentPath === $item['path'] || $currentPath === ltrim($item['path'], '/');
                    ?>
                    <a href="<?= $item['path'] ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
                        <i class="bi <?= $item['icon'] ?>"></i>
                        <?= $item['label'] ?>
                        <?php if (isset($item['badge'])): ?>
                            <span class="nav-badge"><?= $item['badge'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>
</aside>