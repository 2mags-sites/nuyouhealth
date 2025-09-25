<?php
/**
 * Site Configuration Template
 * Core configuration file for PHP websites
 */

// Load environment variables
require_once __DIR__ . '/env-loader.php';

// Site Configuration from Environment
define('SITE_NAME', EnvLoader::get('SITE_NAME', 'Your Company'));
define('SITE_TITLE', EnvLoader::get('SITE_TITLE', 'Your Company - Professional Services'));
define('SITE_URL', EnvLoader::get('APP_URL', 'https://example.com'));
define('SITE_EMAIL', EnvLoader::get('CONTACT_TO_EMAIL', 'info@example.com'));
define('SITE_PHONE', EnvLoader::get('SITE_PHONE', ''));

// Business Information
$business_info = [
    'name' => EnvLoader::get('BUSINESS_NAME', SITE_NAME),
    'phone' => EnvLoader::get('BUSINESS_PHONE', SITE_PHONE),
    'email' => EnvLoader::get('BUSINESS_EMAIL', SITE_EMAIL),
    'address' => EnvLoader::get('BUSINESS_ADDRESS', ''),
    'hours' => EnvLoader::get('BUSINESS_HOURS', 'Monday-Friday 9am-5pm'),
    'company_number' => EnvLoader::get('COMPANY_NUMBER', '')
];

// Contact Form Settings
define('CONTACT_EMAIL_TO', EnvLoader::get('CONTACT_TO_EMAIL', 'info@example.com'));
define('CONTACT_EMAIL_FROM', EnvLoader::get('CONTACT_FROM_EMAIL', 'noreply@example.com'));
define('CONTACT_EMAIL_FROM_NAME', EnvLoader::get('CONTACT_FROM_NAME', 'Website Contact'));

// Admin Settings
define('ADMIN_MODE', isset($_SESSION['admin_mode']) && $_SESSION['admin_mode'] === true);
define('IS_ADMIN', ADMIN_MODE); // Alias for compatibility

// Development/Production Mode
define('DEV_MODE', EnvLoader::get('APP_ENV', 'production') === 'development');
define('SHOW_ERRORS', EnvLoader::get('APP_DEBUG', 'false') === 'true');

if (SHOW_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Start session for admin mode and CSRF tokens
// IMPORTANT: Use proper session_status() check to avoid conflicts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to get CSRF token
function getCSRFToken() {
    return $_SESSION['csrf_token'] ?? '';
}

// Helper function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper function for safe output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Helper function to check if running on localhost
function isLocalhost() {
    $whitelist = ['127.0.0.1', '::1', 'localhost'];
    return in_array($_SERVER['SERVER_NAME'] ?? '', $whitelist) ||
           in_array($_SERVER['REMOTE_ADDR'] ?? '', $whitelist);
}

// Set timezone (can be overridden in .env)
date_default_timezone_set(EnvLoader::get('TIMEZONE', 'Europe/London'));
?>