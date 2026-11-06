<?php
/**
 * STANDARDIZED IMAGE UPLOAD HANDLER
 * Drop-in component for all PHP websites
 * Handles secure image uploads with validation
 *
 * USAGE: Copy this file to your project root
 * REQUIRES: includes/admin-config.php with isAdminMode() function
 */

// Image upload handler for admin mode
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll handle them

// Set JSON header
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if includes file exists
if (!file_exists('includes/admin-config.php')) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit;
}

require_once 'includes/admin-config.php';

// Check if admin mode is active
if (!IS_ADMIN) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

// Validate by the file's real content. $_FILES type and name both come from the browser.
$allowed_types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
$image_info = @getimagesize($_FILES['image']['tmp_name']);

if ($image_info === false || !isset($allowed_types[$image_info[2]])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.']);
    exit;
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB
if ($_FILES['image']['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = __DIR__ . '/assets/images/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$file_extension = $allowed_types[$image_info[2]];
$new_filename = uniqid('img_') . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// Move uploaded file
if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
    list($width, $height) = $image_info;

    // Return success with file info
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'file' => [
            'url' => 'assets/images/uploads/' . $new_filename,
            'name' => $new_filename,
            'size' => $_FILES['image']['size'],
            'width' => $width,
            'height' => $height
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
}
?>