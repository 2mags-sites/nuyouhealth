<?php
/**
 * Admin Save Handler for Nu:You Health
 * Saves edited content from admin mode
 */

// Set up error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];

    // Log to our custom logger
    if (class_exists('ErrorLogger')) {
        ErrorLogger::log('PHP Error in admin-save.php', $error);
    }

    // Return JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

// Enable error logging
require_once 'includes/error-logger.php';

// Log the start of the request
ErrorLogger::logRequest('admin-save.php');

try {
    require_once 'includes/admin-config.php';
    ErrorLogger::log('admin-config.php loaded successfully');
} catch (Exception $e) {
    ErrorLogger::logError('Failed to load admin-config.php', $e);
    die('Configuration error');
}

// Set JSON response header
header('Content-Type: application/json');

// Check if admin mode is active
ErrorLogger::log('Checking admin mode', ['IS_ADMIN' => IS_ADMIN, 'session' => $_SESSION]);
if (!IS_ADMIN) {
    ErrorLogger::log('Admin mode check failed - unauthorized');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
ErrorLogger::log('Admin mode verified');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Parse JSON input
$raw_input = file_get_contents('php://input');
ErrorLogger::log('Raw input received', ['raw' => substr($raw_input, 0, 500)]);

$input = json_decode($raw_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    ErrorLogger::log('JSON decode failed', ['error' => json_last_error_msg(), 'raw' => $raw_input]);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}
ErrorLogger::log('JSON parsed successfully', ['input' => $input]);

// Validate CSRF token - allow localhost to bypass for development
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';

if (!$is_localhost && (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token']))) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Validate required fields
if (!isset($input['page']) || !isset($input['fields'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$page = $input['page'];
$fields = $input['fields'];

// Load existing content
ErrorLogger::log('Loading content for page', ['page' => $page]);
$content = loadContent($page);
ErrorLogger::log('Content loaded', ['content' => $content]);

// Update content with new values
ErrorLogger::log('Updating fields', ['fields' => $fields]);
foreach ($fields as $path => $value) {
    ErrorLogger::log('Updating field', ['path' => $path, 'value' => $value]);
    updateNestedValue($content, $path, $value);
}
ErrorLogger::log('Fields updated', ['updated_content' => $content]);

// Save updated content
ErrorLogger::log('Attempting to save content');
if (saveContent($page, $content)) {
    ErrorLogger::log('Content saved successfully');
    echo json_encode(['success' => true, 'message' => 'Content saved successfully']);
} else {
    ErrorLogger::log('Failed to save content');
    echo json_encode(['success' => false, 'message' => 'Failed to save content']);
}

/**
 * Update nested array value using dot notation path
 * Example: "about.image" or "faqs.0.question"
 */
function updateNestedValue(&$array, $path, $value) {
    $keys = explode('.', $path);
    $current = &$array;

    foreach ($keys as $i => $key) {
        // Check if this is a numeric index
        if (is_numeric($key)) {
            $key = (int)$key;
        }

        if ($i === count($keys) - 1) {
            // Last key, set the value
            $current[$key] = $value;
        } else {
            // Not the last key, traverse deeper
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
    }
}
?>