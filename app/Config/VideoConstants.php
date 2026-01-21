<?php

/**
 * Video Upload Configuration
 * 
 * Centralized configuration for video upload constraints.
 * Update these values here and they'll be used throughout the application.
 * 
 * This file uses constants instead of a class to avoid redeclaration issues.
 */

defined('VIDEO_MAX_FILE_SIZE') || define('VIDEO_MAX_FILE_SIZE', 30 * 1024 * 1024); // 30MB
defined('VIDEO_MAX_DURATION_SECONDS') || define('VIDEO_MAX_DURATION_SECONDS', 600); // 10 minutes
defined('VIDEO_ALLOWED_MIME_TYPES') || define('VIDEO_ALLOWED_MIME_TYPES', ['video/mp4']);
defined('VIDEO_ALLOWED_EXTENSIONS') || define('VIDEO_ALLOWED_EXTENSIONS', ['mp4']);

/**
 * Helper function to get max file size in MB
 */
if (!function_exists('getVideoMaxFileSizeMB')) {
    function getVideoMaxFileSizeMB(): int
    {
        return (int) (VIDEO_MAX_FILE_SIZE / 1024 / 1024);
    }
}

/**
 * Helper function to get max duration formatted as MM:SS
 */
if (!function_exists('getVideoMaxDurationFormatted')) {
    function getVideoMaxDurationFormatted(): string
    {
        $minutes = floor(VIDEO_MAX_DURATION_SECONDS / 60);
        $seconds = VIDEO_MAX_DURATION_SECONDS % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
