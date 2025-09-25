<?php
// Production debug - check what's failing
require_once 'includes/admin-config.php';
$content = loadContent('index');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Production Debug - Nu:You Health</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .debug-section { background: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .ok { color: #059669; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .editable-content {
            outline: 1px dashed #2563eb !important;
            outline-offset: 2px !important;
            min-height: 20px !important;
            display: inline-block !important;
            min-width: 50px !important;
            cursor: pointer !important;
            padding: 3px;
        }
        .console-output {
            background: #1f2937;
            color: #f9fafb;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 Production Debug</h1>

    <div class="debug-section">
        <h2>Admin Status</h2>
        <p><strong>IS_ADMIN():</strong> <?php echo IS_ADMIN() ? '<span class="ok">TRUE</span>' : '<span class="error">FALSE</span>'; ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id() ?: '<span class="error">No Session</span>'; ?></p>
        <p><strong>CSRF Token:</strong> <?php echo isset($_SESSION['csrf_token']) ? '<span class="ok">Present</span>' : '<span class="error">Missing</span>'; ?></p>
        <p><strong>Content Dir Writable:</strong> <?php echo is_writable(__DIR__ . '/content') ? '<span class="ok">YES</span>' : '<span class="error">NO</span>'; ?></p>
    </div>

    <?php if (IS_ADMIN()): ?>
    <div class="debug-section">
        <h2>✅ Testing Elements</h2>
        <button onclick="testJS()" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 4px;">🧪 Test JS</button>

        <h3>Editable Elements</h3>
        <p><strong>Title:</strong> <?php echo editable($content['hero']['title'] ?? 'Default', 'hero.title'); ?></p>
        <p><strong>Subtitle:</strong> <?php echo editable($content['hero']['subtitle'] ?? 'Default', 'hero.subtitle'); ?></p>
    </div>

    <div class="debug-section">
        <h2>Console Output</h2>
        <div id="console" class="console-output">Waiting for JavaScript...</div>
    </div>

    <script src="assets/js/admin-functions.js" defer></script>
    <script>
        const consoleDiv = document.getElementById('console');

        function addLog(type, msg) {
            const div = document.createElement('div');
            div.style.color = type === 'error' ? '#fca5a5' : '#93c5fd';
            div.textContent = `[${type}] ${new Date().toLocaleTimeString()}: ${msg}`;
            consoleDiv.appendChild(div);
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }

        const origLog = console.log;
        const origError = console.error;
        
        console.log = function(...args) {
            addLog('log', args.join(' '));
            origLog.apply(console, args);
        };

        console.error = function(...args) {
            addLog('error', args.join(' '));
            origError.apply(console, args);
        };

        function testJS() {
            console.log('=== TESTING JAVASCRIPT ===');
            console.log('editedFields exists: ' + (typeof editedFields !== 'undefined'));
            console.log('saveAllChanges exists: ' + (typeof saveAllChanges === 'function'));
            console.log('initTextEditing exists: ' + (typeof initTextEditing === 'function'));

            const elements = document.querySelectorAll('.editable-content');
            console.log('Found editable elements: ' + elements.length);

            elements.forEach((el, i) => {
                console.log('Element ' + i + ': field=' + el.getAttribute('data-field') + ', editable=' + el.contentEditable);
            });

            // Test CSRF
            const csrf = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF token: ' + (csrf ? 'Present (' + csrf.content.length + ' chars)' : 'Missing'));

            if (elements.length > 0) {
                console.log('Clicking first element...');
                elements[0].click();
                setTimeout(() => {
                    console.log('After click - contentEditable: ' + elements[0].contentEditable);
                    console.log('After click - focused: ' + (document.activeElement === elements[0]));
                }, 200);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, waiting for admin-functions.js...');
            setTimeout(testJS, 1500);
        });

        window.onerror = function(msg, url, line, col, error) {
            console.error('JS Error: ' + msg + ' at ' + url + ':' + line);
        };
    </script>
    <?php else: ?>
    <div class="debug-section" style="background: #fef2f2;">
        <h2>❌ Admin Mode Not Active</h2>
        <p><a href="?admin=<?php echo ADMIN_SECRET_KEY; ?>">Click here to activate admin mode</a></p>
    </div>
    <?php endif; ?>
</body>
</html>
