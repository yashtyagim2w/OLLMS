<?php

/**
 * Header Component
 * Includes top navigation with profile dropdown
 * All data should be passed from controller - NO model access in views
 */
$currentUser = auth()->user();
$isAdmin = $currentUser ? $currentUser->inGroup('admin') : false;

// Display name and initial should be passed from controller
$displayName = $displayName ?? 'User';
$displayInitial = $displayInitial ?? 'U';
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
            <?php
            // Check if user email verification is pending (passed from controller/layout)
            $isPendingVerification = $isPendingVerification ?? false;
            ?>

            <?php if ($isPendingVerification): ?>
                <!-- Simple logout button for pending verification users -->
                <a href="/logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            <?php else: ?>
                <!-- Full profile dropdown for verified users -->
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
                            <?php
                            // Only show verification status if not fully verified
                            $isFullyVerified = $isFullyVerified ?? false;
                            if (!$isFullyVerified):
                            ?>
                                <a href="/verification-status" class="profile-menu-item">
                                    <i class="bi bi-shield-check"></i>
                                    Verification Status
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="profile-menu-divider"></div>
                        <a href="/logout" class="profile-menu-item danger">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>