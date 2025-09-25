<?php
session_start();
require_once 'includes/admin-config.php';
require_once 'includes/error-logger.php';

// Check if admin mode is active
if (!IS_ADMIN) {
    die('Unauthorized - Admin mode required');
}

// Handle clear log request
if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
    ErrorLogger::clear();
    header('Location: view-logs.php');
    exit;
}

// Handle test request
if (isset($_GET['test']) && $_GET['test'] === 'true') {
    ErrorLogger::log('Test log entry', ['timestamp' => time(), 'admin_mode' => IS_ADMIN]);
    header('Location: view-logs.php');
    exit;
}

// Get log contents
$logContent = ErrorLogger::getLog(200);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Logs</title>
    <style>
        body {
            font-family: monospace;
            background: #1a1a1a;
            color: #0f0;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            color: #0f0;
            border-bottom: 2px solid #0f0;
            padding-bottom: 10px;
        }
        .controls {
            margin: 20px 0;
        }
        .controls a {
            display: inline-block;
            padding: 10px 20px;
            background: #0f0;
            color: #000;
            text-decoration: none;
            margin-right: 10px;
            font-weight: bold;
        }
        .controls a:hover {
            background: #0a0;
        }
        .log-content {
            background: #000;
            padding: 20px;
            border: 1px solid #0f0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 400px;
            font-size: 12px;
            line-height: 1.5;
        }
        .info {
            background: #003300;
            padding: 10px;
            margin: 20px 0;
            border: 1px solid #0f0;
        }
        .back-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #333;
            padding: 10px;
            border: 1px solid #0f0;
        }
        .back-link a {
            color: #0f0;
            text-decoration: none;
        }
    </style>
    <meta http-equiv="refresh" content="5">
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Logs</h1>

        <div class="info">
            <strong>Current Status:</strong><br>
            Admin Mode: <?php echo IS_ADMIN ? 'YES ✓' : 'NO ✗'; ?><br>
            Session ID: <?php echo session_id(); ?><br>
            Server Time: <?php echo date('Y-m-d H:i:s'); ?><br>
            Request Method: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
            Auto-refresh: Every 5 seconds
        </div>

        <div class="controls">
            <a href="?clear=true" onclick="return confirm('Clear all logs?')">🗑️ Clear Logs</a>
            <a href="?test=true">📝 Add Test Entry</a>
            <a href="view-logs.php">🔄 Refresh Now</a>
            <a href="index.php">🏠 Back to Site</a>
        </div>

        <h2>Log Contents (Last 200 lines):</h2>
        <div class="log-content"><?php echo htmlspecialchars($logContent); ?></div>

        <div class="back-link">
            <a href="index.php">← Back to Site</a>
        </div>
    </div>

    <script>
    // Scroll to bottom of log
    window.onload = function() {
        const logDiv = document.querySelector('.log-content');
        logDiv.scrollTop = logDiv.scrollHeight;
    };
    </script>
</body>
</html>