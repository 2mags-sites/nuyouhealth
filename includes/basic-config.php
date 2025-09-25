<?php
// Basic configuration for Nu:You Health
session_start();

require_once __DIR__ . '/env-loader.php';

// Generate CSRF token if not exists (needed for contact form)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Business information
$business_info = [
    'name' => 'Nu:You Health',
    'tagline' => 'Your personalized approach to wellness',
    'phone' => '',
    'email' => 'info@nuyouhealth.com',
    'address' => '',
    'hours' => ''
];
?>
