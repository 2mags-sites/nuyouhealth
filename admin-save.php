<?php
/**
 * STANDARDIZED ADMIN SAVE HANDLER
 * Drop-in component for all PHP websites
 * Saves edited content from admin mode
 *
 * USAGE: Copy this file to your project root
 * REQUIRES: includes/admin-config.php with isAdminMode(), loadContent(), saveContent(), validateCSRFToken()
 */

require_once 'includes/admin-config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if admin mode is active
if (!isAdminMode()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate CSRF token
if (!isset($input['csrf_token']) || !validateCSRFToken($input['csrf_token'])) {
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
$content = loadContent($page);

// Update content with new values
foreach ($fields as $path => $value) {
    $content = updateNestedValue($content, $path, $value);
}

// Save updated content
if (saveContent($page, $content)) {
    echo json_encode(['success' => true, 'message' => 'Content saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save content']);
}

/**
 * Update nested array value using dot notation path
 * Example: "about.image" or "faqs.0.question"
 */
function updateNestedValue($array, $path, $value) {
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

    return $array;
}
?>