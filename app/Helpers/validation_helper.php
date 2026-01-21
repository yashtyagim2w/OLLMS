<?php

/**
 * Validation Helper
 * 
 * Centralized validation patterns and functions for reuse across the application.
 * Load this helper using: helper('validation');
 */

// ============================================================================
// VALIDATION CONSTANTS (Based on Database Schema)
// ============================================================================

// Age validation
const MIN_AGE = 18;
const MAX_AGE = 120;

// Name fields (VARCHAR 100 in user_profiles table)
const NAME_MIN_LENGTH = 2;
const NAME_MAX_LENGTH = 100;

// Email (VARCHAR 255 in auth_identities table - CodeIgniter Shield)
const EMAIL_MAX_LENGTH = 255;

// Aadhaar (VARCHAR 12 in user_documents table)
const AADHAR_LENGTH = 12;

// Password
const PASSWORD_MIN_LENGTH = 8;
const PASSWORD_MAX_LENGTH = 128;

// OTP
const OTP_LENGTH = 6;

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
 * Get the name validation regex pattern (alphabets only)
 * 
 * @return string The regex pattern for valid names
 */
function get_name_pattern(): string
{
    return '/^[A-Za-z]+$/';
}

/**
 * Get the name pattern for HTML (without delimiters)
 * 
 * @return string The pattern for HTML pattern attribute
 */
function get_name_pattern_html(): string
{
    return "[A-Za-z]+";
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

/**
 * Get the password validation regex pattern (at least 1 uppercase, 1 lowercase, 1 digit)
 * 
 * @return string The regex pattern for valid passwords
 */
function get_password_pattern(): string
{
    return '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{' . PASSWORD_MIN_LENGTH . ',' . PASSWORD_MAX_LENGTH . '}$/';
}

/**
 * Get the password pattern for JavaScript (without delimiters)
 * 
 * @return string The pattern for JS validation
 */
function get_password_pattern_js(): string
{
    return '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{' . PASSWORD_MIN_LENGTH . ',' . PASSWORD_MAX_LENGTH . '}$';
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
 * Validate name format (alphabets only)
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
 * Validate password format (min 8 chars, at least 1 uppercase, 1 lowercase, 1 digit)
 * 
 * @param string $password The password to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_password(string $password): bool
{
    return (bool) preg_match(get_password_pattern(), $password);
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
 * Sanitize name input (remove non-alphabetic characters)
 * 
 * @param string $name The name to sanitize
 * @return string The sanitized name
 */
function sanitize_name(string $name): string
{
    return preg_replace("/[^A-Za-z]/", '', $name);
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
        'name'  => 'Only letters allowed',
        'aadhaar' => 'Aadhaar must be exactly 12 digits',
        'password' => 'Password must be ' . PASSWORD_MIN_LENGTH . '-' . PASSWORD_MAX_LENGTH . ' characters with uppercase, lowercase, and number',
        'age' => 'Age must be between ' . MIN_AGE . ' and ' . MAX_AGE . ' years',
        'dob' => 'You must be at least ' . MIN_AGE . ' years old',
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
