<?php
// Production Debug Script for Nu:You Health
// Access: https://nuyouuk.com/debug-production.php

// Basic security - remove this file after debugging
$debug_key = $_GET['key'] ?? '';
if ($debug_key !== 'debug_nuyou_2025') {
    die('Access denied');
}

echo "<h1>🔧 Nu:You Health - Production Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;}</style>";

// 1. PHP Environment Check
echo "<h2>📋 PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

// 2. File Structure Check
echo "<h2>📁 File Structure</h2>";
$required_files = [
    '.env',
    'includes/env-loader.php',
    'includes/config.php',
    'includes/admin-config.php',
    'includes/email-service.php',
    'contact-handler.php',
    'admin-save.php',
    'content/index.json'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span class='ok'>✅ $file exists</span><br>";
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "&nbsp;&nbsp;&nbsp;Permissions: $perms<br>";
    } else {
        echo "<span class='error'>❌ $file MISSING</span><br>";
    }
}

// 3. Environment Variables Check
echo "<h2>🌍 Environment Variables</h2>";
if (file_exists('.env')) {
    echo "<span class='ok'>✅ .env file exists</span><br>";
    $env_perms = substr(sprintf('%o', fileperms('.env')), -4);
    echo "Permissions: $env_perms<br>";

    // Try to load env
    try {
        require_once 'includes/env-loader.php';

        $env_vars = [
            'ADMIN_SECRET_KEY',
            'SENDGRID_API_KEY',
            'CONTACT_TO_EMAIL',
            'CONTACT_FROM_EMAIL',
            'DEBUG_MODE'
        ];

        foreach ($env_vars as $var) {
            $value = EnvLoader::get($var, 'NOT_SET');
            if ($value === 'NOT_SET') {
                echo "<span class='error'>❌ $var: NOT SET</span><br>";
            } else {
                // Mask sensitive values
                if (strpos($var, 'KEY') !== false) {
                    $display_value = substr($value, 0, 10) . '...' . substr($value, -4);
                } else {
                    $display_value = $value;
                }
                echo "<span class='ok'>✅ $var: $display_value</span><br>";
            }
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error loading environment: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file missing</span><br>";
}

// 4. Admin Mode Check
echo "<h2>🔑 Admin Mode Debug</h2>";
try {
    require_once 'includes/admin-config.php';

    // Check if admin constants are defined
    if (defined('ADMIN_SECRET_KEY')) {
        $admin_key = ADMIN_SECRET_KEY;
        echo "<span class='ok'>✅ ADMIN_SECRET_KEY defined</span><br>";
        echo "Admin URL: https://nuyouuk.com/?admin=" . htmlspecialchars($admin_key) . "<br>";
    } else {
        echo "<span class='error'>❌ ADMIN_SECRET_KEY not defined</span><br>";
    }

    // Check admin mode activation
    $current_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    echo "Current URL: " . htmlspecialchars($current_url) . "<br>";

    if (isset($_GET['admin'])) {
        echo "Admin parameter received: " . htmlspecialchars($_GET['admin']) . "<br>";
        if (defined('ADMIN_SECRET_KEY') && $_GET['admin'] === ADMIN_SECRET_KEY) {
            echo "<span class='ok'>✅ Admin key matches</span><br>";
        } else {
            echo "<span class='error'>❌ Admin key does not match</span><br>";
        }
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ Error checking admin mode: " . $e->getMessage() . "</span><br>";
}

// 5. Session Check
echo "<h2>🔒 Session Debug</h2>";
if (session_status() === PHP_SESSION_NONE) {
    echo "Starting session...<br>";
    session_start();
}
echo "Session Status: " . session_status() . " (1=disabled, 2=active)<br>";
echo "Session ID: " . session_id() . "<br>";
if (isset($_SESSION['csrf_token'])) {
    echo "<span class='ok'>✅ CSRF token exists</span><br>";
} else {
    echo "<span class='warning'>⚠️ CSRF token not set</span><br>";
}

// 6. Email Service Check
echo "<h2>📧 Email Service Debug</h2>";
try {
    require_once 'includes/email-service.php';
    echo "<span class='ok'>✅ EmailService class loaded</span><br>";

    // Check SendGrid vs PHP mail configuration
    $sendgrid_key = EnvLoader::get('SENDGRID_API_KEY', '');
    if (!empty($sendgrid_key)) {
        echo "<span class='ok'>✅ SendGrid API key configured</span><br>";
    } else {
        echo "<span class='warning'>⚠️ SendGrid API key missing - will use PHP mail()</span><br>";
    }

    // Test basic email configuration
    $to_email = EnvLoader::get('CONTACT_TO_EMAIL', '');
    $from_email = EnvLoader::get('CONTACT_FROM_EMAIL', '');
    echo "To Email: " . htmlspecialchars($to_email) . "<br>";
    echo "From Email: " . htmlspecialchars($from_email) . "<br>";

} catch (Exception $e) {
    echo "<span class='error'>❌ Error loading email service: " . $e->getMessage() . "</span><br>";
}

// 7. Writable Directories Check
echo "<h2>📝 Writable Directories</h2>";
$dirs_to_check = [
    'assets/images/uploads',
    'content'
];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='ok'>✅ $dir is writable</span><br>";
        } else {
            echo "<span class='error'>❌ $dir is NOT writable</span><br>";
        }
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "&nbsp;&nbsp;&nbsp;Permissions: $perms<br>";
    } else {
        echo "<span class='warning'>⚠️ $dir does not exist</span><br>";
    }
}

// 8. Error Log Check
echo "<h2>📋 Recent Errors</h2>";
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "<pre>";
        print_r($last_error);
        echo "</pre>";
    } else {
        echo "No recent PHP errors<br>";
    }
}

// Check for custom error log
if (file_exists('error_log')) {
    echo "<h3>Custom Error Log (last 10 lines):</h3>";
    $log_lines = file('error_log');
    $recent_lines = array_slice($log_lines, -10);
    echo "<pre>" . htmlspecialchars(implode('', $recent_lines)) . "</pre>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Check any red ❌ items above</li>";
echo "<li>Verify file permissions (especially .env should be 600)</li>";
echo "<li>Test admin URL provided above</li>";
echo "<li>Check server error logs for detailed email errors</li>";
echo "<li><strong>DELETE this debug file after use for security</strong></li>";
echo "</ol>";

echo "<p><em>Generated: " . date('Y-m-d H:i:s') . "</em></p>";
?>