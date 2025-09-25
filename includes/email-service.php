<?php
/**
 * Email Service Handler
 * Supports SendGrid with fallback to PHP mail()
 * Template Version - Ready for drop-in use
 */

require_once __DIR__ . '/env-loader.php';

class EmailService {
    private $sendgridApiKey;
    private $emailService;
    private $fallbackEnabled;

    public function __construct() {
        $this->sendgridApiKey = EnvLoader::get('SENDGRID_API_KEY');
        $this->emailService = EnvLoader::get('EMAIL_SERVICE', 'mail');
        $this->fallbackEnabled = EnvLoader::get('EMAIL_FALLBACK', 'true') === 'true';
    }

    /**
     * Send email using configured service
     */
    public function send($to, $subject, $body, $from = null, $fromName = null, $replyTo = null, $bcc = null, $isHtml = false) {
        // Set defaults from environment
        if (!$from) {
            $from = EnvLoader::get('CONTACT_FROM_EMAIL', 'noreply@' . $_SERVER['HTTP_HOST']);
        }
        if (!$fromName) {
            $fromName = EnvLoader::get('CONTACT_FROM_NAME', 'Website Contact');
        }

        // Try SendGrid first if configured
        if ($this->emailService === 'sendgrid' && !empty($this->sendgridApiKey)) {
            $result = $this->sendWithSendGrid($to, $subject, $body, $from, $fromName, $replyTo, $bcc, $isHtml);

            if ($result['success']) {
                return $result;
            } elseif (!$this->fallbackEnabled) {
                return $result; // Return SendGrid error if fallback is disabled
            }

            // Fall through to PHP mail if SendGrid fails and fallback is enabled
            error_log("SendGrid failed, falling back to PHP mail: " . $result['message']);
        }

        // Use PHP mail() as default or fallback
        return $this->sendWithPhpMail($to, $subject, $body, $from, $fromName, $replyTo, $bcc, $isHtml);
    }

    /**
     * Send email using SendGrid API
     */
    private function sendWithSendGrid($to, $subject, $body, $from, $fromName, $replyTo, $bcc, $isHtml) {
        $url = 'https://api.sendgrid.com/v3/mail/send';

        // Build email data
        $emailData = [
            'personalizations' => [
                [
                    'to' => [['email' => $to]]
                ]
            ],
            'from' => [
                'email' => $from,
                'name' => $fromName
            ],
            'subject' => $subject,
            'content' => [
                [
                    'type' => $isHtml ? 'text/html' : 'text/plain',
                    'value' => $body
                ]
            ]
        ];

        // Add BCC if provided
        if ($bcc) {
            $bccEmails = is_array($bcc) ? $bcc : explode(',', $bcc);
            $emailData['personalizations'][0]['bcc'] = [];
            foreach ($bccEmails as $bccEmail) {
                $bccEmail = trim($bccEmail);
                if (!empty($bccEmail)) {
                    $emailData['personalizations'][0]['bcc'][] = ['email' => $bccEmail];
                }
            }
        }

        // Add Reply-To if provided
        if ($replyTo) {
            $emailData['reply_to'] = ['email' => $replyTo];
        }

        // Send request to SendGrid
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->sendgridApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'SendGrid connection error: ' . $error
            ];
        }

        // SendGrid returns 202 for successful queued emails
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'Email sent successfully via SendGrid'
            ];
        } else {
            $errorMsg = 'SendGrid API error (HTTP ' . $httpCode . ')';
            if ($response) {
                $responseData = json_decode($response, true);
                if (isset($responseData['errors'][0]['message'])) {
                    $errorMsg .= ': ' . $responseData['errors'][0]['message'];
                }
            }

            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
    }

    /**
     * Send email using PHP mail()
     */
    private function sendWithPhpMail($to, $subject, $body, $from, $fromName, $replyTo, $bcc, $isHtml) {
        $headers = "From: " . $fromName . " <" . $from . ">\r\n";

        if ($replyTo) {
            $headers .= "Reply-To: " . $replyTo . "\r\n";
        }

        if ($bcc) {
            $bccEmails = is_array($bcc) ? implode(', ', $bcc) : $bcc;
            $headers .= "Bcc: " . $bccEmails . "\r\n";
        }

        if ($isHtml) {
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        $headers .= "X-Mailer: PHP/" . phpversion();

        // Use error suppression and check result
        $success = @mail($to, $subject, $body, $headers);

        return [
            'success' => $success,
            'message' => $success ? 'Email sent successfully via PHP mail' : 'Failed to send email via PHP mail'
        ];
    }

    /**
     * Send templated contact form email
     */
    public function sendContactFormEmail($formData) {
        // Extract form data
        $name = $formData['name'];
        $email = $formData['email'];
        $phone = $formData['phone'] ?? '';
        $message = $formData['message'];

        // Get email settings from environment
        $to = EnvLoader::get('CONTACT_TO_EMAIL', 'info@example.com');
        $bcc = EnvLoader::get('CONTACT_BCC_EMAIL');

        // Build email subject and body
        $subject = 'New Contact Form Submission - ' . EnvLoader::get('SITE_NAME', 'Website');

        $body = "New contact form submission:\n\n";
        $body .= "Name: " . $name . "\n";
        $body .= "Email: " . $email . "\n";
        $body .= "Phone: " . ($phone ?: 'Not provided') . "\n";
        $body .= "Message:\n" . $message . "\n";
        $body .= "\n---\n";
        $body .= "Submitted on: " . date('Y-m-d H:i:s') . "\n";
        $body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

        // Send main email
        $result = $this->send($to, $subject, $body, null, null, $email, $bcc);

        // Send confirmation email to user if main email was successful
        if ($result['success']) {
            $this->sendContactFormConfirmation($name, $email);
        }

        return $result;
    }

    /**
     * Send confirmation email to user
     */
    private function sendContactFormConfirmation($name, $email) {
        $siteName = EnvLoader::get('SITE_NAME', 'Our Company');
        $sitePhone = EnvLoader::get('SITE_PHONE', '');

        $subject = "Thank you for contacting " . $siteName;

        $body = "Dear " . $name . ",\n\n";
        $body .= "Thank you for contacting " . $siteName . ". We have received your message and will respond as soon as possible.\n\n";

        if ($sitePhone) {
            $body .= "If you need immediate assistance, please call us on " . $sitePhone . ".\n\n";
        }

        $body .= "Kind regards,\n";
        $body .= "The " . $siteName . " Team\n\n";
        $body .= "---\n";
        $body .= "This is an automated response to confirm we have received your message.\n";

        // Send confirmation (don't worry if this fails)
        $this->send($email, $subject, $body);
    }

    /**
     * Send HTML email with template
     */
    public function sendHtmlEmail($to, $subject, $htmlContent, $textContent = null) {
        // If no text content provided, strip HTML tags
        if (!$textContent) {
            $textContent = strip_tags($htmlContent);
        }

        // Create multipart email
        $boundary = md5(time());

        $headers = "From: " . EnvLoader::get('CONTACT_FROM_NAME', 'Website') .
                   " <" . EnvLoader::get('CONTACT_FROM_EMAIL', 'noreply@example.com') . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $body .= $textContent . "\r\n\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $body .= $htmlContent . "\r\n\r\n";
        $body .= "--$boundary--";

        return $this->send($to, $subject, $body, null, null, null, null, true);
    }
}
?>