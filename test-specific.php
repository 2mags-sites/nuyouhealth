<?php
// Specific functionality test
echo "<h1>🧪 Function-Specific Tests</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Test 1: Admin Mode Function
echo "<h2>1️⃣ Admin Mode Test</h2>";
try {
    require_once 'includes/admin-config.php';

    echo "Testing with ?admin parameter...<br>";
    $_GET['admin'] = 'nuyou_admin_2025_asjhuisa12312'; // Simulate the parameter

    if (function_exists('isAdminMode')) {
        $admin_result = isAdminMode();
        echo ($admin_result ? "<span class='ok'>✅ isAdminMode() returns TRUE</span><br>" : "<span class='error'>❌ isAdminMode() returns FALSE</span><br>");
    } else {
        echo "<span class='error'>❌ isAdminMode() function missing</span><br>";
    }

    if (defined('ADMIN_SECRET_KEY')) {
        echo "<span class='ok'>✅ ADMIN_SECRET_KEY: " . ADMIN_SECRET_KEY . "</span><br>";
    } else {
        echo "<span class='error'>❌ ADMIN_SECRET_KEY not defined</span><br>";
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ Admin test error: " . $e->getMessage() . "</span><br>";
}

// Test 2: Contact Form Simulation
echo "<h2>2️⃣ Contact Form Test</h2>";
try {
    // Simulate POST data
    $_POST = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'mobile' => '1234567890',
        'message' => 'Test message',
        'privacy' => 'on',
        'honeypot' => '' // Empty honeypot = not spam
    ];

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    echo "<span class='info'>📤 Simulating contact form submission...</span><br>";

    // Test field validation
    $required_fields = ['name', 'email', 'message'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (empty($missing_fields)) {
        echo "<span class='ok'>✅ Required fields validation passed</span><br>";
    } else {
        echo "<span class='error'>❌ Missing fields: " . implode(', ', $missing_fields) . "</span><br>";
    }

    // Test privacy checkbox
    if (isset($_POST['privacy']) && $_POST['privacy'] === 'on') {
        echo "<span class='ok'>✅ Privacy checkbox validation passed</span><br>";
    } else {
        echo "<span class='error'>❌ Privacy checkbox validation failed</span><br>";
    }

    // Test honeypot
    if (empty($_POST['honeypot'])) {
        echo "<span class='ok'>✅ Honeypot validation passed (not spam)</span><br>";
    } else {
        echo "<span class='error'>❌ Honeypot validation failed (detected as spam)</span><br>";
    }

    // Test email service loading
    require_once 'includes/email-service.php';
    if (class_exists('EmailService')) {
        echo "<span class='ok'>✅ EmailService class exists</span><br>";

        $emailService = new EmailService();
        echo "<span class='ok'>✅ EmailService instantiated</span><br>";

        // Check if sendContactFormEmail method exists
        if (method_exists($emailService, 'sendContactFormEmail')) {
            echo "<span class='ok'>✅ sendContactFormEmail method exists</span><br>";
        } else {
            echo "<span class='error'>❌ sendContactFormEmail method missing</span><br>";
        }
    } else {
        echo "<span class='error'>❌ EmailService class missing</span><br>";
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ Contact form test error: " . $e->getMessage() . "</span><br>";
}

// Test 3: File Permissions Test
echo "<h2>3️⃣ File Write Test</h2>";
try {
    $test_file = __DIR__ . '/test_write.tmp';

    if (file_put_contents($test_file, 'test')) {
        echo "<span class='ok'>✅ Can write to directory</span><br>";
        unlink($test_file);
    } else {
        echo "<span class='error'>❌ Cannot write to directory</span><br>";
    }

    // Test rate limit file creation
    $rate_limit_file = __DIR__ . '/rate_limit_test.json';
    $test_data = ['test' => time()];

    if (file_put_contents($rate_limit_file, json_encode($test_data))) {
        echo "<span class='ok'>✅ Can create rate limit file</span><br>";
        unlink($rate_limit_file);
    } else {
        echo "<span class='error'>❌ Cannot create rate limit file</span><br>";
    }

} catch (Exception $e) {
    echo "<span class='error'>❌ File write test error: " . $e->getMessage() . "</span><br>";
}

// Test 4: Actual Contact Handler Test
echo "<h2>4️⃣ Contact Handler Direct Test</h2>";
try {
    // Capture output from contact handler
    ob_start();

    // Set up proper POST environment
    $_POST = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'mobile' => '1234567890',
        'message' => 'Test message from debug script',
        'privacy' => 'on'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    // Include and run contact handler
    include 'contact-handler.php';

    $contact_output = ob_get_clean();

    echo "<span class='info'>📧 Contact handler output:</span><br>";
    echo "<pre>" . htmlspecialchars($contact_output) . "</pre>";

    // Try to parse as JSON
    $json_result = json_decode($contact_output, true);
    if ($json_result) {
        if (isset($json_result['success']) && $json_result['success']) {
            echo "<span class='ok'>✅ Contact form SUCCESS: " . $json_result['message'] . "</span><br>";
        } else {
            echo "<span class='error'>❌ Contact form FAILED: " . $json_result['message'] . "</span><br>";
        }
    } else {
        echo "<span class='error'>❌ Contact handler returned invalid JSON</span><br>";
    }

} catch (Exception $e) {
    ob_end_clean();
    echo "<span class='error'>❌ Contact handler direct test error: " . $e->getMessage() . "</span><br>";
}

echo "<hr><p><strong>🧹 DELETE this file after debugging!</strong></p>";
?>