<?php
// Admin Configuration for Nu:You Health
require_once __DIR__ . '/env-loader.php';
require_once __DIR__ . '/config.php';

// Load secrets from .env file
define('ADMIN_SECRET_KEY', EnvLoader::get('ADMIN_SECRET_KEY', 'nuyou_admin_default'));
define('CACHE_CLEAR_KEY', EnvLoader::get('CACHE_CLEAR_KEY', 'nuyou_cache_default'));

// Admin mode activation
$admin_mode = false;
if (isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY) {
    // Sessions disabled - use direct parameter check
    $admin_mode = true;
} elseif (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Logout - redirect to clean URL
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}
// Note: Without sessions, admin mode only works with ?admin=key parameter

/**
 * Check if admin mode is active (for template compatibility)
 */
function isAdminMode() {
    return isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY;
}

/**
 * Check if admin mode is active (for template compatibility)
 * Use this instead of IS_ADMIN constant to ensure dynamic checking
 */
function IS_ADMIN() {
    return isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY;
}

/**
 * Validate CSRF token (simplified - no sessions available)
 */
function validateCSRFToken($token) {
    // Since sessions are disabled, skip CSRF validation
    // Security is provided by admin key and honeypot
    return true;
}

// Cache clearing
if (isset($_GET['clearcache']) && $_GET['clearcache'] === CACHE_CLEAR_KEY) {
    // Clear any cache files if needed
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

/**
 * Load content from JSON file
 */
function loadContent($page) {
    $content_file = __DIR__ . '/../content/' . $page . '.json';
    if (file_exists($content_file)) {
        return json_decode(file_get_contents($content_file), true);
    }
    return [];
}

/**
 * Save content to JSON file
 */
function saveContent($page, $content) {
    $content_file = __DIR__ . '/../content/' . $page . '.json';
    return file_put_contents($content_file, json_encode($content, JSON_PRETTY_PRINT));
}

/**
 * Make text editable in admin mode
 */
function editable($value, $field_path, $type = 'text') {
    if (!IS_ADMIN()) {
        return $value;
    }
    $data_field = htmlspecialchars($field_path);
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    if ($current_page === '') $current_page = 'index';
    return '<span class="editable-content" data-field="' . $data_field . '" data-page="' . $current_page . '">' . $value . '</span>';
}

/**
 * Make image editable in admin mode
 */
function editableImage($src, $field, $placeholder, $alt = '') {
    $page = basename($_SERVER['PHP_SELF'], '.php');

    if (empty($src)) {
        $src = placeholderImage($placeholder);
    }

    if (IS_ADMIN()) {
        return "<img src=\"{$src}\" alt=\"{$alt}\" class=\"editable-image\" data-field=\"{$field}\" data-page=\"{$page}\" data-placeholder=\"{$placeholder}\" style=\"cursor: pointer;\" />";
    }

    return "<img src=\"{$src}\" alt=\"{$alt}\" />";
}

/**
 * Generate a placeholder image with descriptive text
 */
function placeholderImage($text, $width = 600, $height = 400) {
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
    <svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">
        <rect width="100%" height="100%" fill="#e5e7eb"/>
        <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="16" fill="#6b7280" text-anchor="middle" dy="0.3em">' . htmlspecialchars($text) . '</text>
    </svg>';

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Update nested array value using dot notation
 */
function updateNestedValue(&$array, $path, $value) {
    $keys = explode('.', $path);
    $current = &$array;

    foreach ($keys as $key) {
        if (!isset($current[$key]) || !is_array($current[$key])) {
            $current[$key] = [];
        }
        $current = &$current[$key];
    }

    $current = $value;
}

// Business information
$business_info = [
    'name' => 'Nu:You Health',
    'tagline' => 'Your personalized approach to wellness',
    'phone' => '',
    'email' => 'info@nuyouhealth.com',
    'address' => '',
    'hours' => ''
];
?>