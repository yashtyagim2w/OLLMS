<?php

/**
 * Header Component
 * Includes top navigation with profile dropdown
 */
$currentUser = auth()->user();
$isAdmin = $currentUser ? $currentUser->inGroup('admin') : false;

// Get display name - use email if no username
$displayName = 'User';
$displayInitial = 'U';

if ($currentUser) {
    // Try to get name from user_profiles if exists
    $profileModel = model('App\Models\UserProfileModel');
    if ($profileModel && method_exists($profileModel, 'find')) {
        $profile = $profileModel->where('user_id', $currentUser->id)->first();
        if ($profile && !empty($profile['first_name'])) {
            $displayName = $profile['first_name'] . ' ' . $profile['last_name'];
            $displayInitial = strtoupper(substr($profile['first_name'], 0, 1));
        } else {
            // Fall back to email
            $displayName = $currentUser->email ?? 'User';
            $displayInitial = strtoupper(substr($displayName, 0, 1));
        }
    } else {
        // Fall back to email
        $displayName = $currentUser->email ?? 'User';
        $displayInitial = strtoupper(substr($displayName, 0, 1));
    }
}
?>
<header class="main-header <?= !isset($showSidebar) || !$showSidebar ? 'full-width' : '' ?>">
    <div class="header-left">
        <button class="btn btn-icon sidebar-toggle d-lg-none" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="header-title"><?= $pageTitle ?? 'OLLMS' ?></h1>
    </div>

    <div class="header-right">
        <?php if ($currentUser): ?>
            <div class="profile-dropdown">
                <div class="profile-toggle">
                    <div class="profile-avatar">
                        <?= $displayInitial ?>
                    </div>
                    <div class="profile-info d-none d-md-block">
                        <div class="profile-name"><?= esc($displayName) ?></div>
                        <div class="profile-role"><?= $isAdmin ? 'RTO Admin' : 'Applicant' ?></div>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="profile-menu">
                    <a href="<?= $isAdmin ? '/admin/profile' : '/profile' ?>" class="profile-menu-item">
                        <i class="bi bi-person"></i>
                        My Profile
                    </a>
                    <?php if (!$isAdmin): ?>
                        <a href="/verification-status" class="profile-menu-item">
                            <i class="bi bi-shield-check"></i>
                            Verification Status
                        </a>
                    <?php endif; ?>
                    <div class="profile-menu-divider"></div>
                    <a href="/logout" class="profile-menu-item danger">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>