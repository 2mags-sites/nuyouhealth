<?php
/**
 * Nu:You Health Admin Configuration
 * Includes admin mode functionality and editable content system
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load environment variables directly
require_once __DIR__ . '/env-loader.php';

// Admin secret keys
define('ADMIN_SECRET_KEY', EnvLoader::get('ADMIN_SECRET_KEY', 'dev_admin_key'));
define('CACHE_CLEAR_KEY', EnvLoader::get('CACHE_CLEAR_KEY', 'dev_cache_key'));

// Check for admin mode activation
if (isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY) {
    $_SESSION['admin_mode'] = true;
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Check for admin logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    $_SESSION['admin_mode'] = false;
    unset($_SESSION['admin_mode']);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Check for cache clear
if (isset($_GET['clearcache']) && $_GET['clearcache'] === CACHE_CLEAR_KEY) {
    // Clear any cached content if needed
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Admin mode status - only define if not already defined
if (!defined('IS_ADMIN')) {
    define('IS_ADMIN', isset($_SESSION['admin_mode']) && $_SESSION['admin_mode'] === true);
}
if (!defined('ADMIN_MODE')) {
    define('ADMIN_MODE', IS_ADMIN);
}

/**
 * Verify CSRF token - define if not already defined
 */
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Load content from JSON file
 */
function loadContent($page_name) {
    $content_file = __DIR__ . '/../content/' . $page_name . '.json';

    if (file_exists($content_file)) {
        $content = json_decode(file_get_contents($content_file), true);
        if ($content === null) {
            error_log("Failed to decode JSON for page: $page_name");
            return [];
        }
        return $content;
    }

    error_log("Content file not found: $content_file");
    return [];
}

/**
 * Save content to JSON file
 */
function saveContent($page_name, $content) {
    $content_file = __DIR__ . '/../content/' . $page_name . '.json';
    $json_string = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($json_string === false) {
        error_log("Failed to encode JSON for page: $page_name");
        return false;
    }

    $result = file_put_contents($content_file, $json_string);
    if ($result === false) {
        error_log("Failed to write content file: $content_file");
        return false;
    }

    return true;
}

/**
 * Make text content editable in admin mode
 */
function editable($value, $field_path, $type = 'text') {
    if (!IS_ADMIN) {
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
function editableImage($src, $field_path, $placeholder_text = '', $alt = '', $class = '') {
    $page = basename($_SERVER['PHP_SELF'], '.php');

    if (IS_ADMIN) {
        $upload_overlay = '<div class="image-edit-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(37, 99, 235, 0.9); color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 500; display: none;">📷 Click to Upload</div>';

        return '<div class="editable-image-container" data-field="' . $field_path . '" data-page="' . $page . '" style="position: relative; cursor: pointer;" data-placeholder="' . htmlspecialchars($placeholder_text) . '">
                    <img src="' . ($src ?: 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#e5e7eb"/><text x="200" y="150" text-anchor="middle" font-family="Arial" font-size="14" fill="#6b7280">' . ($placeholder_text ?: 'Click to upload image') . '</text></svg>')) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" style="width: 100%; height: 100%; object-fit: cover;">
                    ' . $upload_overlay . '
                </div>';
    }

    return '<img src="' . ($src ?: 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#e5e7eb"/><text x="200" y="150" text-anchor="middle" font-family="Arial" font-size="14" fill="#6b7280">' . ($placeholder_text ?: 'Image placeholder') . '</text></svg>')) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '">';
}

/**
 * Generate admin bar HTML if in admin mode
 */
function renderAdminBar() {
    if (!IS_ADMIN) {
        return '';
    }

    ob_start();
    ?>
    <div id="admin-bar" style="position: fixed; top: 0; left: 0; right: 0; background: #1f2937; color: white; padding: 10px 20px; z-index: 9999; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <strong>🔧 Admin Mode</strong>
            <span style="font-size: 0.9em; opacity: 0.8;">Click on text or images to edit</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button id="save-changes" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500;">Save Changes</button>
            <button id="add-faq" style="background: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500;">Add FAQ</button>
            <a href="?logout=true" style="background: #ef4444; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: 500;">Exit Admin</a>
        </div>
    </div>
    <div style="height: 60px;"></div>
    <?php
    return ob_get_clean();
}
?>