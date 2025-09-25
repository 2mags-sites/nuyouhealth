<?php
// Ensure session is started properly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/env-loader.php';
require_once 'includes/email-service.php';

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

// Verify CSRF token (skip in development for testing)
$skip_csrf = (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost');

if (!$skip_csrf) {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) ||
        $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        $response['message'] = 'Security validation failed. Please refresh and try again.';
        echo json_encode($response);
        exit;
    }
} else {
    // In development, generate token if missing
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

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
$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = filter_var($_POST['mobile'] ?? $_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
$service = filter_var($_POST['service'] ?? '', FILTER_SANITIZE_STRING);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please provide a valid email address.';
    echo json_encode($response);
    exit;
}

// Rate limiting
$max_submissions = (int)EnvLoader::get('MAX_SUBMISSIONS_PER_HOUR', 20);
if (!isset($_SESSION['form_submissions'])) {
    $_SESSION['form_submissions'] = [];
}

// Clean old submissions (older than 1 hour)
$_SESSION['form_submissions'] = array_filter($_SESSION['form_submissions'], function($time) {
    return $time > (time() - 3600);
});

if (count($_SESSION['form_submissions']) >= $max_submissions) {
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

// Try to send email using EmailService
try {
    $emailService = new EmailService();
    $result = $emailService->sendContactFormEmail($formData);

    // Log submission time for rate limiting
    $_SESSION['form_submissions'][] = time();

    $response['success'] = true;
    $response['message'] = EnvLoader::get('FORM_SUCCESS_MESSAGE',
        'Thank you for your message. We will be in touch soon.');

} catch (Exception $e) {
    // Log error
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
            $_SESSION['form_submissions'][] = time();
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

// Generate new CSRF token for next submission
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo json_encode($response);
?>