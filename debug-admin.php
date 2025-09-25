<?php
// Debug admin mode specifically
echo "<h1>🔧 Admin Mode Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .info{color:blue;}</style>";

echo "<h2>1️⃣ GET Parameters</h2>";
echo "Full URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "GET parameters:<br>";
foreach ($_GET as $key => $value) {
    echo "- $key = " . htmlspecialchars($value) . "<br>";
}

echo "<h2>2️⃣ Environment Loading</h2>";
try {
    require_once 'includes/env-loader.php';
    $admin_key = EnvLoader::get('ADMIN_SECRET_KEY', 'NOT_FOUND');
    echo "Admin key from .env: " . htmlspecialchars($admin_key) . "<br>";
} catch (Exception $e) {
    echo "<span class='error'>Error loading env: " . $e->getMessage() . "</span><br>";
}

echo "<h2>3️⃣ Admin Config Test</h2>";
try {
    require_once 'includes/admin-config.php';

    echo "ADMIN_SECRET_KEY constant: " . (defined('ADMIN_SECRET_KEY') ? ADMIN_SECRET_KEY : 'NOT_DEFINED') . "<br>";

    if (function_exists('isAdminMode')) {
        $admin_function_result = isAdminMode();
        echo "isAdminMode() returns: " . ($admin_function_result ? 'TRUE' : 'FALSE') . "<br>";
    } else {
        echo "<span class='error'>isAdminMode() function not found</span><br>";
    }

    if (defined('IS_ADMIN')) {
        $admin_constant = IS_ADMIN;
        echo "IS_ADMIN constant: " . ($admin_constant ? 'TRUE' : 'FALSE') . "<br>";
    } else {
        echo "<span class='error'>IS_ADMIN constant not defined</span><br>";
    }

    // Test manual check
    $manual_check = isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY;
    echo "Manual admin check: " . ($manual_check ? 'TRUE' : 'FALSE') . "<br>";

} catch (Exception $e) {
    echo "<span class='error'>Error loading admin config: " . $e->getMessage() . "</span><br>";
}

echo "<h2>4️⃣ Simulate Admin HTML</h2>";
echo "This is what should appear if IS_ADMIN is true:<br>";
?>

<?php if (defined('IS_ADMIN') && IS_ADMIN): ?>
<div style="background: #2563eb; color: white; padding: 10px; margin: 10px 0;">
    <span class='ok'>✅ ADMIN BAR SHOULD BE HERE</span>
    <br>✏️ Admin Mode Active - This is the admin bar!
</div>
<?php else: ?>
<div style="background: #dc3545; color: white; padding: 10px; margin: 10px 0;">
    <span class='error'>❌ ADMIN BAR MISSING</span>
    <br>IS_ADMIN is false or undefined
</div>
<?php endif; ?>

<h2>5️⃣ Test Editable Function</h2>
<?php
if (function_exists('editable')) {
    echo "editable() function test: " . editable('Test Text', 'test.field') . "<br>";
} else {
    echo "<span class='error'>editable() function not found</span><br>";
}
?>

<h2>6️⃣ Expected vs Actual</h2>
<p><strong>Expected URL format:</strong><br>
https://nuyouuk.com/debug-admin.php?admin=nuyou_admin_2025_asjhuisa12312</p>

<p><strong>Current URL:</strong><br>
<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>
</p>

<hr>
<p><strong>🧹 DELETE this file after debugging!</strong></p>