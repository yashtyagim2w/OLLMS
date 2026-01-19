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
    $routes->get('verify-otp', 'UserController::verifyOtp');

    // API endpoints for OTP (JSON responses)
    $routes->post('api/send-otp', 'UserController::apiSendOtp');
    $routes->post('api/verify-otp', 'UserController::apiVerifyOtp');
    $routes->post('api/resend-otp', 'UserController::apiResendOtp');

    // Identity Upload (accessible during auth flow)
    $routes->get('identity-upload', 'UserController::identityUpload');
    $routes->post('identity-upload', 'UserController::processIdentityUpload');

    // API endpoints for S3 presigned upload
    $routes->post('api/get-upload-url', 'UserController::apiGetUploadUrl');
    $routes->post('api/confirm-upload', 'UserController::apiConfirmUpload');

    // Verification Status (accessible during auth flow)
    $routes->get('verification-status', 'UserController::verificationStatus');
});

// USER ROUTES - Protected (requires session + user group + approved status)
$routes->group('', ['filter' => ['session', 'group:user', 'userstatus']], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'UserController::dashboard');

    // Profile
    $routes->get('profile', 'UserController::profile');

    // Videos
    $routes->get('videos', 'UserController::videos');
    $routes->get('video/(:num)', 'UserController::videoPlayer/$1');
    $routes->get('video-progress', 'UserController::videoProgress');

    // Test
    $routes->get('test-instructions', 'UserController::testInstructions');
    $routes->get('test', 'UserController::test');
    $routes->get('test-result/(:num)', 'UserController::testResult/$1');
    $routes->get('test-result', 'UserController::testResult');

    // Certificate
    $routes->get('certificate', 'UserController::certificate');
});

// ADMIN ROUTES (session + admin group)
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('profile', 'AdminController::profile');
    $routes->get('identity-review', 'AdminController::identityReview');
    $routes->get('users', 'AdminController::users');
    $routes->get('videos', 'AdminController::videos');
    $routes->get('questions', 'AdminController::questions');
    $routes->get('instructions', 'AdminController::instructions');
    $routes->get('progress', 'AdminController::progress');
    $routes->get('reports', 'AdminController::reports');

    // API Routes for List Pages
    $routes->get('api/identity-reviews', 'AdminController::getIdentityReviews');
    $routes->get('api/identity-reviews/(:num)', 'AdminController::getIdentityReviewDetail/$1');
    $routes->post('api/identity-reviews/(:num)/approve', 'AdminController::approveIdentity/$1');
    $routes->post('api/identity-reviews/(:num)/reject', 'AdminController::rejectIdentity/$1');

    // User Management APIs
    $routes->get('api/users/export', 'AdminController::exportUsers');
    $routes->get('api/users', 'AdminController::getUsers');
    $routes->get('api/users/(:num)', 'AdminController::getUser/$1');
    $routes->post('api/users/(:num)', 'AdminController::updateUser/$1');
    $routes->post('api/users/(:num)/ban', 'AdminController::banUser/$1');
    $routes->post('api/users/(:num)/activate', 'AdminController::activateUser/$1');
    $routes->post('api/users/(:num)/set-password', 'AdminController::setUserPassword/$1');

    $routes->get('api/videos', 'AdminController::getVideos');
    $routes->get('api/questions', 'AdminController::getQuestions');
    $routes->get('api/instructions', 'AdminController::getInstructions');
    $routes->get('api/progress', 'AdminController::getUserProgress');
});
