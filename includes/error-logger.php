<?php
/**
 * ERROR LOGGER FOR DEBUGGING
 * Drop-in component for debugging production issues
 *
 * USAGE:
 * 1. Include this file: require_once 'includes/error-logger.php';
 * 2. Log messages: ErrorLogger::log('Something happened', ['context' => 'data']);
 * 3. View logs at: /view-logs.php (admin mode required)
 */

class ErrorLogger {
    private static $logFile = __DIR__ . '/../logs/debug.log';
    private static $maxFileSize = 5242880; // 5MB
    private static $maxBackups = 3;

    /**
     * Log a message with optional context
     */
    public static function log($message, $context = []) {
        $logDir = dirname(self::$logFile);

        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Rotate log if too large
        self::rotateLogIfNeeded();

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}";

        if (!empty($context)) {
            $logEntry .= " | Context: " . json_encode($context, JSON_PRETTY_PRINT);
        }

        $logEntry .= "\n" . str_repeat('-', 80) . "\n";

        // Write to log file
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log detailed request information
     */
    public static function logRequest($script) {
        $data = [
            'script' => $script,
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'not set',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'not set',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'session' => isset($_SESSION) ? array_keys($_SESSION) : [],
            'post_data' => $_POST,
            'raw_input' => substr(file_get_contents('php://input'), 0, 1000)
        ];

        self::log("Request to {$script}", $data);
    }

    /**
     * Log an error with backtrace
     */
    public static function logError($message, $exception = null) {
        $context = [
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];

        if ($exception instanceof Exception) {
            $context['exception'] = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        self::log("ERROR: {$message}", $context);
    }

    /**
     * Clear the log file
     */
    public static function clear() {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
            self::log('Log cleared');
            return true;
        }
        return false;
    }

    /**
     * Get log contents
     */
    public static function getLog($lines = 100) {
        if (!file_exists(self::$logFile)) {
            return "Log file does not exist yet.";
        }

        $content = file_get_contents(self::$logFile);

        if ($lines > 0) {
            $allLines = explode("\n", $content);
            if (count($allLines) > $lines) {
                $allLines = array_slice($allLines, -$lines);
            }
            return implode("\n", $allLines);
        }

        return $content;
    }

    /**
     * Rotate log file if it's too large
     */
    private static function rotateLogIfNeeded() {
        if (!file_exists(self::$logFile)) {
            return;
        }

        if (filesize(self::$logFile) > self::$maxFileSize) {
            // Rotate existing backups
            for ($i = self::$maxBackups - 1; $i > 0; $i--) {
                $oldFile = self::$logFile . '.' . $i;
                $newFile = self::$logFile . '.' . ($i + 1);
                if (file_exists($oldFile)) {
                    if ($i === self::$maxBackups - 1) {
                        unlink($oldFile); // Delete oldest backup
                    } else {
                        rename($oldFile, $newFile);
                    }
                }
            }

            // Move current log to .1
            rename(self::$logFile, self::$logFile . '.1');

            // Start fresh log
            self::log('Log rotated (exceeded ' . round(self::$maxFileSize / 1048576, 2) . 'MB)');
        }
    }

    /**
     * Get log file size
     */
    public static function getLogSize() {
        if (!file_exists(self::$logFile)) {
            return 0;
        }
        return filesize(self::$logFile);
    }

    /**
     * Get human-readable log size
     */
    public static function getLogSizeFormatted() {
        $size = self::getLogSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
?>