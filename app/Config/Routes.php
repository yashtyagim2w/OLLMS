<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Landing page - redirects based on auth status
$routes->get('/', 'Home::index');

// Shield auth routes
service('auth')->routes($routes);

// Custom Auth Routes (for custom views)
$routes->group('auth', static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->get('register', 'AuthController::register');
    $routes->get('reset-password', 'AuthController::resetPassword');
});

// User Routes (protected by session filter)
$routes->group('', ['filter' => 'session'], static function ($routes) {
    // Email Verification (requires login)
    $routes->get('verify-otp', 'UserController::verifyOtp');

    // Dashboard
    $routes->get('dashboard', 'UserController::dashboard');

    // Profile
    $routes->get('profile', 'UserController::profile');

    // Identity & Verification
    $routes->get('identity-upload', 'UserController::identityUpload');
    $routes->get('verification-status', 'UserController::verificationStatus');

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

// Admin Routes (protected by session filter)
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
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
    $routes->get('api/users', 'AdminController::getUsers');
    $routes->get('api/videos', 'AdminController::getVideos');
    $routes->get('api/questions', 'AdminController::getQuestions');
    $routes->get('api/instructions', 'AdminController::getInstructions');
    $routes->get('api/progress', 'AdminController::getUserProgress');
});
