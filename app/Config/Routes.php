<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Landing page - redirects based on auth status
$routes->get('/', 'Home::index');

// Custom registration with profile creation (overrides Shield)
$routes->post('register', 'RegisterController::registerAction');

// Override Shield's action routes - redirect to our custom OTP page
$routes->get('auth/a/show', static function () {
    return redirect()->to('/verify-otp');
});
$routes->get('auth/a/verify', static function () {
    return redirect()->to('/verify-otp');
});
$routes->post('auth/a/verify', static function () {
    return redirect()->to('/verify-otp');
});
$routes->post('auth/a/handle', static function () {
    return redirect()->to('/verify-otp');
});

// Shield auth routes (handles login, logout, magic-link, etc.)
service('auth')->routes($routes);

// PUBLIC ROUTES (No authentication required)

// Password Reset (custom implementation)
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:any)', 'AuthController::showResetForm/$1');
$routes->post('reset-password', 'AuthController::resetPassword');

// USER ROUTES - Authentication Steps (session + user group, no status check)
$routes->group('', ['filter' => ['session', 'group:user']], static function ($routes) {
    // Email Verification
    $routes->get('verify-otp', 'User\VerificationController::otp');

    // API endpoints for OTP (JSON responses)
    $routes->post('api/send-otp', 'User\VerificationController::apiSend');
    $routes->post('api/verify-otp', 'User\VerificationController::apiVerify');
    $routes->post('api/resend-otp', 'User\VerificationController::apiResend');

    // Identity Upload (accessible during auth flow)
    $routes->get('identity-upload', 'User\DocumentController::upload');
    $routes->post('identity-upload', 'User\DocumentController::processUpload');

    // API endpoints for S3 presigned upload
    $routes->post('api/get-upload-url', 'User\DocumentController::apiGetUploadUrl');
    $routes->post('api/confirm-upload', 'User\DocumentController::apiConfirmUpload');

    // Verification Status (accessible during auth flow)
    $routes->get('verification-status', 'User\DocumentController::status');
});

// USER ROUTES - Protected (requires session + user group + approved status)
$routes->group('', ['filter' => ['session', 'group:user', 'userstatus']], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'User\DashboardController::index');

    // Profile
    $routes->get('profile', 'User\ProfileController::index');

    // Videos
    $routes->get('videos', 'User\LearningController::index');
    $routes->get('video/(:num)', 'User\LearningController::player/$1');
    $routes->get('video-progress', 'User\LearningController::progress');

    // Test
    $routes->get('test-instructions', 'User\TestController::instructions');
    $routes->get('test', 'User\TestController::index');
    $routes->get('test-result/(:num)', 'User\TestController::result/$1');
    $routes->get('test-result', 'User\TestController::result');

    // Certificate
    $routes->get('certificate', 'User\CertificateController::index');
});

// ADMIN ROUTES (session + admin group)
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('profile', 'Admin\ProfileController::index');
    $routes->get('identity-review', 'Admin\IdentityReviewController::index');
    $routes->get('users', 'Admin\UserManagementController::index');
    $routes->get('videos', 'Admin\VideoManagementController::index');
    $routes->get('questions', 'Admin\QuestionBankController::index');
    $routes->get('instructions', 'Admin\InstructionController::index');
    $routes->get('progress', 'Admin\ProgressController::index');
    $routes->get('reports', 'Admin\ReportController::index');

    // API Routes for List Pages
    $routes->get('api/identity-reviews', 'Admin\IdentityReviewController::getList');
    $routes->get('api/identity-reviews/(:num)', 'Admin\IdentityReviewController::getDetail/$1');
    $routes->post('api/identity-reviews/(:num)/approve', 'Admin\IdentityReviewController::approve/$1');
    $routes->post('api/identity-reviews/(:num)/reject', 'Admin\IdentityReviewController::reject/$1');

    // User Management APIs
    $routes->get('api/users/export', 'Admin\UserManagementController::export');
    $routes->get('api/users', 'Admin\UserManagementController::getList');
    $routes->get('api/users/(:num)', 'Admin\UserManagementController::getDetail/$1');
    $routes->post('api/users/(:num)', 'Admin\UserManagementController::update/$1');
    $routes->post('api/users/(:num)/ban', 'Admin\UserManagementController::ban/$1');
    $routes->post('api/users/(:num)/activate', 'Admin\UserManagementController::activate/$1');
    $routes->post('api/users/(:num)/set-password', 'Admin\UserManagementController::setPassword/$1');

    // Video Management Routes
    $routes->get('api/categories', 'Admin\VideoManagementController::apiGetCategories');
    $routes->get('api/videos', 'Admin\VideoManagementController::getList');
    $routes->get('api/video/view-url', 'Admin\VideoManagementController::apiGetViewUrl');
    $routes->post('api/video/get-upload-url', 'Admin\VideoManagementController::apiGetUploadUrl');
    $routes->post('api/video/confirm-upload', 'Admin\VideoManagementController::apiConfirmUpload');
    $routes->post('api/videos/(:num)/toggle-active', 'Admin\VideoManagementController::apiToggleActive/$1');
    $routes->delete('api/videos/(:num)', 'Admin\VideoManagementController::apiDelete/$1');
    $routes->put('api/videos/(:num)', 'Admin\VideoManagementController::apiUpdate/$1');

    $routes->get('api/questions', 'Admin\QuestionBankController::getList');
    $routes->get('api/instructions', 'Admin\InstructionController::getList');
    $routes->get('api/progress', 'Admin\ProgressController::getUserProgress');
});
