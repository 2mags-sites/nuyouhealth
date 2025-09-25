<?php
// Test timing issue
echo "<h1>🕐 Timing Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;}</style>";

echo "<h2>Step 1: Before setting GET parameter</h2>";
echo "GET admin parameter exists: " . (isset($_GET['admin']) ? 'YES' : 'NO') . "<br>";

// Load admin config BEFORE setting $_GET
require_once 'includes/admin-config.php';

echo "<h2>Step 2: After loading admin config (before GET)</h2>";
echo "IS_ADMIN constant: " . (defined('IS_ADMIN') ? (IS_ADMIN ? 'TRUE' : 'FALSE') : 'UNDEFINED') . "<br>";

echo "<h2>Step 3: Now setting GET parameter</h2>";
$_GET['admin'] = 'nuyou_admin_2025_asjhuisa12312';
echo "GET admin parameter set to: " . $_GET['admin'] . "<br>";

echo "<h2>Step 4: After setting GET parameter</h2>";
echo "IS_ADMIN constant: " . (IS_ADMIN ? 'TRUE' : 'FALSE') . "<br>";
echo "isAdminMode() function: " . (isAdminMode() ? 'TRUE' : 'FALSE') . "<br>";
echo "Manual check: " . (isset($_GET['admin']) && $_GET['admin'] === ADMIN_SECRET_KEY ? 'TRUE' : 'FALSE') . "<br>";

echo "<h2>Conclusion</h2>";
echo "The IS_ADMIN constant is defined when the admin-config.php is first loaded, before $_GET is set by the test script.";
?>