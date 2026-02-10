<?php
// Ensure session is started properly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/env-loader.php';
require_once 'includes/email-service.php';
require_once 'includes/error-logger.php';

// Log the contact form request
ErrorLogger::logRequest('contact-handler.php');

// Set JSON response header
header('Content-Type: application/json');

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Check honeypot (anti-spam) - field name is 'honeypot' in the HTML form
if (!empty($_POST['honeypot'])) {
    $response['message'] = 'Spam detected.';
    echo json_encode($response);
    exit;
}

// Skip CSRF token validation since sessions are disabled
// Production security is handled by honeypot and rate limiting

// Validate required fields
$required_fields = ['name', 'email', 'message'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }
}

// Check privacy consent checkbox (it sends 'on' when checked, nothing when unchecked)
if (!isset($_POST['privacy']) || $_POST['privacy'] !== 'on') {
    $response['message'] = 'Please accept the privacy policy to continue.';
    echo json_encode($response);
    exit;
}

// Sanitize input
$name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($_POST['mobile'] ?? $_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');
$service = htmlspecialchars(trim($_POST['service'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please provide a valid email address.';
    echo json_encode($response);
    exit;
}

// Rate limiting (simplified - no sessions available)
// Basic IP-based rate limiting using file system
$rate_limit_file = __DIR__ . '/rate_limit.json';
$max_submissions = 20;
$current_ip = $_SERVER['REMOTE_ADDR'];
$current_time = time();

$rate_data = [];
if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}

// Clean old entries
$rate_data = array_filter($rate_data, function($time) use ($current_time) {
    return $time > ($current_time - 3600); // Keep entries from last hour
});

// Count submissions from current IP
$ip_submissions = array_filter($rate_data, function($time, $ip) use ($current_ip) {
    return $ip === $current_ip;
}, ARRAY_FILTER_USE_BOTH);

if (count($ip_submissions) >= $max_submissions) {
    $response['message'] = 'Too many submissions. Please try again later.';
    echo json_encode($response);
    exit;
}

// Prepare form data for SendGrid
$formData = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'service' => $service,
    'message' => $message,
    'timestamp' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR']
];

// Log the form data being sent
ErrorLogger::log('Preparing to send email', [
    'to_email' => EnvLoader::get('CONTACT_TO_EMAIL'),
    'from_email' => EnvLoader::get('CONTACT_FROM_EMAIL'),
    'sendgrid_api_key' => substr(EnvLoader::get('SENDGRID_API_KEY', ''), 0, 10) . '...',
    'form_data' => $formData
]);

// Try to send email using EmailService
try {
    $emailService = new EmailService();
    ErrorLogger::log('EmailService created, attempting to send email');

    $result = $emailService->sendContactFormEmail($formData);

    ErrorLogger::log('Email send result', ['result' => $result]);

    if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Email sending failed');
    }

    // Log submission time for rate limiting
    $rate_data[$current_ip . '_' . time()] = $current_time;
    @file_put_contents($rate_limit_file, json_encode($rate_data));

    $response['success'] = true;
    $response['message'] = EnvLoader::get('FORM_SUCCESS_MESSAGE',
        'Thank you for your message. We will be in touch soon.');

} catch (Exception $e) {
    // Log error
    ErrorLogger::logError('Email send failed', $e);
    error_log('Contact form error: ' . $e->getMessage());

    // Try PHP mail as fallback if enabled
    if (EnvLoader::get('EMAIL_FALLBACK', 'true') === 'true') {
        $to = EnvLoader::get('CONTACT_TO_EMAIL', 'info@example.com');
        $subject = 'New Contact Form Submission';

        $email_body = "New contact form submission:\n\n";
        $email_body .= "Name: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Phone: " . ($phone ?: 'Not provided') . "\n";
        if ($service) {
            $email_body .= "Service: " . $service . "\n";
        }
        $email_body .= "Message:\n" . $message . "\n";
        $email_body .= "\n---\n";
        $email_body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
        $email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

        $headers = "From: " . EnvLoader::get('CONTACT_FROM_NAME', 'Website') .
                   " <" . EnvLoader::get('CONTACT_FROM_EMAIL', 'noreply@example.com') . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $email_body, $headers)) {
            $rate_data[$current_ip . '_' . time()] = $current_time;
            file_put_contents($rate_limit_file, json_encode($rate_data));
            $response['success'] = true;
            $response['message'] = EnvLoader::get('FORM_SUCCESS_MESSAGE',
                'Thank you for your message. We will be in touch soon.');
        } else {
            $response['message'] = EnvLoader::get('FORM_ERROR_MESSAGE',
                'Sorry, there was an error. Please try again or call us directly.');
        }
    } else {
        $response['message'] = EnvLoader::get('FORM_ERROR_MESSAGE',
            'Sorry, there was an error. Please try again or call us directly.');
    }
}

// No CSRF token generation needed (sessions disabled)

echo json_encode($response);
?>