<?php
/**
 * Download Content - Admin Only
 * Downloads all JSON content files as a ZIP archive
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin (using the same session variable as admin-config.php)
$isAdmin = isset($_SESSION['admin_mode']) && $_SESSION['admin_mode'] === true;

if (!$isAdmin) {
    http_response_code(403);
    die('Access denied. Admin authentication required.');
}

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    die('Error: ZipArchive extension is not installed on this server.');
}

// Create a unique temporary file name
$zipName = 'content-backup-' . date('Y-m-d-His') . '.zip';
$tempFile = sys_get_temp_dir() . '/' . $zipName;

// Create new zip archive
$zip = new ZipArchive();

if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Error: Cannot create ZIP file.');
}

// Get all JSON files from content directory
$contentDir = __DIR__ . '/content/';
$jsonFiles = glob($contentDir . '*.json');

if (empty($jsonFiles)) {
    $zip->close();
    unlink($tempFile);
    die('Error: No JSON files found in content directory.');
}

// Add each JSON file to the zip
$fileCount = 0;
foreach ($jsonFiles as $file) {
    if (is_readable($file)) {
        // Add file to zip with just the filename (no path)
        $zip->addFile($file, basename($file));
        $fileCount++;
    }
}

// Add a manifest file with metadata
$manifest = [
    'site' => $_SERVER['HTTP_HOST'],
    'backup_date' => date('Y-m-d H:i:s'),
    'file_count' => $fileCount,
    'files' => array_map('basename', $jsonFiles)
];
$zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

// Close the zip file
$zip->close();

// Check if file was created successfully
if (!file_exists($tempFile) || filesize($tempFile) == 0) {
    die('Error: Failed to create ZIP file.');
}

// Save a copy to content/backups directory on the server
$backupDir = __DIR__ . '/content/backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Copy the zip to backups folder with timestamp
$backupFile = $backupDir . $zipName;
if (copy($tempFile, $backupFile)) {
    // Keep only last 10 backups to prevent disk space issues
    $backups = glob($backupDir . 'content-backup-*.zip');
    if (count($backups) > 10) {
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        // Delete oldest backups
        $toDelete = array_slice($backups, 0, count($backups) - 10);
        foreach ($toDelete as $oldBackup) {
            unlink($oldBackup);
        }
    }
}

// Send headers for download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($tempFile));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');
header('Pragma: public');

// Output the file
readfile($tempFile);

// Clean up temporary file
unlink($tempFile);

// Exit to prevent any additional output
exit;