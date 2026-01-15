<?php

/**
 * Validation Helper
 * 
 * Centralized validation patterns and functions for reuse across the application.
 * Load this helper using: helper('validation');
 */

// ============================================================================
// VALIDATION CONSTANTS
// ============================================================================

const MIN_AGE = 18;
const MAX_AGE = 120;

// ============================================================================
// VALIDATION PATTERNS (for use in both PHP and HTML)
// ============================================================================

/**
 * Get the email validation regex pattern
 * 
 * @return string The regex pattern for valid emails
 */
function get_email_pattern(): string
{
    return '/^[a-zA-Z0-9.+]+@[a-zA-Z0-9.]+\.[a-zA-Z]{2,}$/';
}

/**
 * Get the email pattern for HTML (without delimiters)
 * 
 * @return string The pattern for HTML pattern attribute
 */
function get_email_pattern_html(): string
{
    return '[a-zA-Z0-9.+]+@[a-zA-Z0-9.]+\.[a-zA-Z]{2,}';
}

/**
 * Get the name validation regex pattern (alphabets, spaces, hyphens, apostrophes)
 * 
 * @return string The regex pattern for valid names
 */
function get_name_pattern(): string
{
    return '/^[A-Za-z\s\-\']+$/';
}

/**
 * Get the name pattern for HTML (without delimiters)
 * 
 * @return string The pattern for HTML pattern attribute
 */
function get_name_pattern_html(): string
{
    return "[A-Za-z\\s\\-']+";
}

/**
 * Get the Aadhaar validation regex pattern (exactly 12 digits)
 * 
 * @return string The regex pattern for valid Aadhaar
 */
function get_aadhaar_pattern(): string
{
    return '/^[0-9]{12}$/';
}

/**
 * Get the Aadhaar pattern for HTML (without delimiters)
 * 
 * @return string The pattern for HTML pattern attribute
 */
function get_aadhaar_pattern_html(): string
{
    return '[0-9]{12}';
}

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate email format
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_email(string $email): bool
{
    return (bool) preg_match(get_email_pattern(), $email);
}

/**
 * Validate name format (alphabets, spaces, hyphens, apostrophes only)
 * 
 * @param string $name The name to validate
 * @param int $minLength Minimum length (default: 2)
 * @param int $maxLength Maximum length (default: 100)
 * @return bool True if valid, false otherwise
 */
function is_valid_name(string $name, int $minLength = 2, int $maxLength = 100): bool
{
    $length = strlen($name);
    if ($length < $minLength || $length > $maxLength) {
        return false;
    }
    return (bool) preg_match(get_name_pattern(), $name);
}

/**
 * Validate Aadhaar number format (exactly 12 digits)
 * 
 * @param string $aadhaar The Aadhaar number to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_aadhaar(string $aadhaar): bool
{
    return (bool) preg_match(get_aadhaar_pattern(), $aadhaar);
}

/**
 * Calculate age from date of birth
 * 
 * @param string $dob Date of birth (Y-m-d format)
 * @return int|null Age in years, or null if invalid date
 */
function get_age_from_dob(string $dob): ?int
{
    try {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        return $age;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Validate age (between MIN_AGE and MAX_AGE)
 * 
 * @param int $age The age to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_age(int $age): bool
{
    return $age >= MIN_AGE && $age <= MAX_AGE;
}

/**
 * Validate date of birth (results in age between MIN_AGE and MAX_AGE)
 * 
 * @param string $dob Date of birth (Y-m-d format)
 * @return bool True if valid, false otherwise
 */
function is_valid_dob(string $dob): bool
{
    $age = get_age_from_dob($dob);
    if ($age === null) {
        return false;
    }
    return is_valid_age($age);
}

/**
 * Get minimum date of birth allowed (for max age limit)
 * 
 * @return string Date in Y-m-d format
 */
function get_min_dob(): string
{
    return date('Y-m-d', strtotime('-' . MAX_AGE . ' years'));
}

/**
 * Get maximum date of birth allowed (for min age limit)
 * 
 * @return string Date in Y-m-d format
 */
function get_max_dob(): string
{
    return date('Y-m-d', strtotime('-' . MIN_AGE . ' years'));
}

// ============================================================================
// SANITIZATION FUNCTIONS
// ============================================================================

/**
 * Sanitize name input (remove non-alphabetic characters except spaces, hyphens, apostrophes)
 * 
 * @param string $name The name to sanitize
 * @return string The sanitized name
 */
function sanitize_name(string $name): string
{
    return preg_replace("/[^A-Za-z\s\-']/", '', $name);
}

/**
 * Sanitize Aadhaar input (remove non-digits)
 * 
 * @param string $aadhaar The Aadhaar to sanitize
 * @return string The sanitized Aadhaar (digits only)
 */
function sanitize_aadhaar(string $aadhaar): string
{
    return preg_replace('/[^0-9]/', '', $aadhaar);
}

// ============================================================================
// VALIDATION ERROR MESSAGES
// ============================================================================

/**
 * Get validation error messages
 * 
 * @return array Associative array of field => error message
 */
function get_validation_messages(): array
{
    return [
        'email' => 'Please enter a valid email address (e.g., user@example.com)',
        'name'  => 'Only letters, spaces, hyphens and apostrophes allowed',
        'aadhaar' => 'Aadhaar must be exactly 12 digits',
        'age' => 'Age must be between ' . MIN_AGE . ' and ' . MAX_AGE . ' years',
        'dob' => 'You must be between ' . MIN_AGE . ' and ' . MAX_AGE . ' years old',
    ];
}

/**
 * Get validation message for a specific field
 * 
 * @param string $field The field name
 * @return string The error message
 */
function get_validation_message(string $field): string
{
    $messages = get_validation_messages();
    return $messages[$field] ?? 'Invalid input';
}
