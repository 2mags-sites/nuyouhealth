<?php
// Test final admin mode fix
echo "<h1>🎯 Final Admin Mode Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;}</style>";

// Set admin parameter BEFORE loading admin config
$_GET['admin'] = 'nuyou_admin_2025_asjhuisa12312';

echo "<h2>Testing with admin parameter set</h2>";
echo "GET['admin'] = " . $_GET['admin'] . "<br><br>";

// Load admin config
require_once 'includes/admin-config.php';

echo "<h2>Results:</h2>";
echo "ADMIN_SECRET_KEY: " . ADMIN_SECRET_KEY . "<br>";
echo "isAdminMode(): " . (isAdminMode() ? 'TRUE' : 'FALSE') . "<br>";
echo "IS_ADMIN(): " . (IS_ADMIN() ? 'TRUE' : 'FALSE') . "<br>";

echo "<h2>Admin Bar Test:</h2>";
if (IS_ADMIN()) {
    echo "<div style='background: #2563eb; color: white; padding: 10px;'>✅ ADMIN BAR SHOULD BE VISIBLE</div>";
} else {
    echo "<div style='background: #dc3545; color: white; padding: 10px;'>❌ ADMIN BAR HIDDEN</div>";
}

echo "<h2>Editable Text Test:</h2>";
echo "Normal text: Test Text<br>";
echo "Editable text: " . editable('Test Text', 'test.field') . "<br>";

if (strpos(editable('Test Text', 'test.field'), 'editable-text') !== false) {
    echo "<div style='color: green; font-weight: bold;'>✅ SUCCESS: Editable text contains admin markup!</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ FAIL: Editable text does not contain admin markup</div>";
}
?>