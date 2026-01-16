<?php

/**
 * Email Theme Configuration
 * 
 * Centralized theme variables for email templates
 * Mirrors the application's main.css theme for brand consistency
 */
return [
    // Primary colors
    'primary' => '#1a3a5c',        // --primary
    'primary_light' => '#2a5a8c',  // --primary-light
    'secondary' => '#ffd700',      // --secondary (gold)

    // Status colors  
    'success' => '#28a745',        // --success
    'danger' => '#dc3545',         // --danger
    'warning' => '#ffc107',        // --warning
    'info' => '#17a2b8',           // --info

    // Text colors
    'text_primary' => '#333333',   // --gray-800
    'text_secondary' => '#555555', // --gray-600
    'text_muted' => '#888888',     // --gray-500

    // Background colors
    'bg_light' => '#f5f7fa',       // --bg-light
    'bg_white' => '#ffffff',
    'border' => '#e0e0e0',         // --gray-300

    // Semantic backgrounds (for alerts)
    'bg_success' => '#d1e7dd',
    'bg_danger' => '#f8d7da',
    'bg_warning' => '#fff3cd',
    'bg_info' => '#e8f4fd',

    // Semantic alert text colors
    'text_success' => '#0f5132',   // Dark green for success alerts
    'text_danger' => '#721c24',    // Dark red for danger alerts
    'text_warning' => '#856404',   // Dark yellow/brown for warning alerts
    'text_info' => '#0c5460',      // Dark teal for info alerts
];
