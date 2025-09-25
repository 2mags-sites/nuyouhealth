<?php
require_once 'includes/admin-config.php';

// Load content from JSON
$content = loadContent('index');

// Set page meta from JSON
$page_title = $content['meta']['title'] ?? 'NU: YOU HEALTH - Coming Soon';
$page_description = $content['meta']['description'] ?? 'Your personalized approach to wellness is coming soon.';
$page_keywords = $content['meta']['keywords'] ?? 'personalized health, wellness, nu you health';

require_once 'includes/header.php';
?>

<?php echo renderAdminBar(); ?>

    <div class="background-decoration"></div>

    <div class="container">
        <div class="logo-section">
            <h1 class="logo-title"><?php echo editable($content['hero']['title'] ?? 'NU: YOU HEALTH', 'hero.title'); ?></h1>
            <h2 class="logo-subtitle"><?php echo editable($content['hero']['subtitle'] ?? 'Your personalized approach to wellness', 'hero.subtitle'); ?></h2>
        </div>

        <div class="decorative-line"></div>

        <p class="coming-soon"><?php echo editable($content['coming_soon']['text'] ?? 'Coming Soon', 'coming_soon.text'); ?></p>

        <p class="description">
            <?php echo editable($content['description']['text'] ?? 'Your personalized approach to wellness is on its way. We\'re crafting a comprehensive health experience that puts you at the center of your care journey.', 'description.text'); ?>
        </p>

        <div class="button-container">
            <button class="btn btn-primary" onclick="showContactForm()">
                <?php echo editable($content['buttons']['primary']['text'] ?? 'Get In Touch', 'buttons.primary.text'); ?>
            </button>
            <a href="<?php echo htmlspecialchars($content['buttons']['secondary']['link'] ?? 'https://practitioner.nuyouuk.com'); ?>" class="btn btn-secondary" target="_blank">
                <?php echo editable($content['buttons']['secondary']['text'] ?? 'Practitioner Portal', 'buttons.secondary.text'); ?>
            </a>
        </div>
    </div>

    <!-- Contact Form Overlay -->
    <div class="overlay" id="contactOverlay">
        <div class="contact-form">
            <button class="close-btn" onclick="hideContactForm()">&times;</button>

            <!-- Contact Form Content -->
            <div class="contact-form-content" id="contactFormContent">
                <div class="form-header">
                    <h3 class="form-title"><?php echo editable($content['contact_form']['title'] ?? 'Get In Touch', 'contact_form.title'); ?></h3>
                    <p class="form-subtitle"><?php echo editable($content['contact_form']['subtitle'] ?? 'We\'d love to hear from you', 'contact_form.subtitle'); ?></p>
                </div>

                <div id="status-message" class="status-message" style="display: none;"></div>

                <form id="contactForm">
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Your Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mobile">Your Mobile</label>
                        <input type="tel" id="mobile" name="mobile" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message</label>
                        <textarea id="message" name="message" class="form-input form-textarea" required></textarea>
                    </div>

                    <!-- Honeypot field (hidden) -->
                    <div style="display: none;">
                        <input type="text" name="honeypot" id="honeypot">
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="privacy" name="privacy" class="checkbox-input" required>
                        <label for="privacy" class="checkbox-label">
                            I accept the <a href="#" onclick="showPrivacyPolicy(); return false;">Privacy Policy</a> and agree to receive communications from NU: YOU HEALTH.
                        </label>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn btn-cancel" onclick="hideContactForm()">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>

            <!-- Privacy Policy Content (hidden by default) -->
            <div class="privacy-policy-content" id="privacyPolicyContent" style="display: none;">
                <div class="form-header">
                    <h3 class="form-title">Privacy Policy</h3>
                    <p class="form-subtitle">Your privacy is important to us</p>
                </div>

                <div class="privacy-content" style="max-height: 400px; overflow-y: auto; text-align: left; padding: 20px; background: #f8f9fa; border-radius: 5px; margin-bottom: 20px;">
                    <h4>Information We Collect</h4>
                    <p>When you contact us through our website, we collect the following information:</p>
                    <ul>
                        <li>Your name</li>
                        <li>Email address</li>
                        <li>Phone number</li>
                        <li>Message content</li>
                    </ul>

                    <h4>How We Use Your Information</h4>
                    <p>We use the information you provide to:</p>
                    <ul>
                        <li>Respond to your inquiries</li>
                        <li>Provide information about our health and wellness services</li>
                        <li>Send you updates about NU: YOU HEALTH (with your consent)</li>
                    </ul>

                    <h4>Information Sharing</h4>
                    <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy. We may share information when required by law or to protect our rights.</p>

                    <h4>Data Security</h4>
                    <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>

                    <h4>Your Rights</h4>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access the personal information we hold about you</li>
                        <li>Request correction of inaccurate information</li>
                        <li>Request deletion of your information</li>
                        <li>Withdraw consent for communications</li>
                    </ul>

                    <h4>Contact Information</h4>
                    <p>For questions about this privacy policy or to exercise your rights, contact us at:</p>
                    <p><strong>NU: YOU HEALTH</strong><br>
                    Email: info@nuyouuk.com</p>

                    <h4>Policy Updates</h4>
                    <p>This privacy policy may be updated from time to time. The effective date of the current policy is displayed at the bottom of this document.</p>

                    <p style="margin-top: 30px; font-size: 0.9em; color: #666;">
                        <strong>Effective Date:</strong> September 2025
                    </p>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-primary" onclick="hidePrivacyPolicy()">Back to Contact Form</button>
                </div>
            </div>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>