<?php
// Simple Production Debug - No Security Check
// DELETE after use

echo "<h1>🔧 Nu:You Health - Production Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Basic Info
echo "<h2>📋 Basic Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// 2. File Check
echo "<h2>📁 Critical Files</h2>";
$files = ['.env', 'includes/env-loader.php', 'includes/admin-config.php', 'contact-handler.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "<span class='ok'>✅ $file (perms: $perms)</span><br>";
    } else {
        echo "<span class='error'>❌ $file MISSING</span><br>";
    }
}

// 3. Environment Variables
echo "<h2>🌍 Environment Test</h2>";
if (file_exists('.env')) {
    try {
        require_once 'includes/env-loader.php';
        $admin_key = EnvLoader::get('ADMIN_SECRET_KEY', 'NOT_FOUND');
        $sendgrid_key = EnvLoader::get('SENDGRID_API_KEY', 'NOT_FOUND');

        echo "Admin Key: " . ($admin_key !== 'NOT_FOUND' ? 'FOUND' : 'NOT_FOUND') . "<br>";
        echo "SendGrid Key: " . ($sendgrid_key !== 'NOT_FOUND' ? 'FOUND' : 'NOT_FOUND') . "<br>";

        if ($admin_key !== 'NOT_FOUND') {
            echo "<strong>Admin URL:</strong> https://nuyouuk.com/?admin=" . htmlspecialchars($admin_key) . "<br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span class='error'>❌ .env file missing</span><br>";
}

// 4. Session Test
echo "<h2>🔒 Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session started<br>";
}
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

// 5. Email Test
echo "<h2>📧 Email Test</h2>";
try {
    require_once 'includes/email-service.php';
    echo "<span class='ok'>✅ EmailService loaded</span><br>";
} catch (Exception $e) {
    echo "<span class='error'>❌ EmailService error: " . $e->getMessage() . "</span><br>";
}

// 6. Check GET parameters
echo "<h2>🔍 Current Request</h2>";
echo "Full URL: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";
if (!empty($_GET)) {
    echo "GET Parameters: ";
    foreach ($_GET as $key => $value) {
        echo htmlspecialchars($key) . "=" . htmlspecialchars($value) . " ";
    }
    echo "<br>";
} else {
    echo "No GET parameters<br>";
}

echo "<hr><p><strong>DELETE this file after debugging!</strong></p>";
?>