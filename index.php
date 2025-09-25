<?php
// Include admin configuration
require_once 'includes/admin-config.php';

// Load content from JSON
$content = loadContent('index');

// Set page meta from JSON
$page_title = $content['meta']['title'] ?? 'NU: YOU HEALTH - Coming Soon';
$page_description = $content['meta']['description'] ?? 'Your personalized approach to wellness is coming soon.';
$page_keywords = $content['meta']['keywords'] ?? 'personalized health, wellness, nu you health';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">

    <!-- CSRF Token -->
    <?php if (IS_ADMIN): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Nu:You Health",
        "description": "<?php echo htmlspecialchars($page_description); ?>",
        "url": "<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
    }
    </script>
</head>
<body>
    <?php if (IS_ADMIN): ?>
    <!-- Admin Bar -->
    <div id="admin-bar" style="position: fixed; top: 0; left: 0; right: 0; background: #2563eb; color: white; padding: 10px 20px; z-index: 9999; font-family: Arial, sans-serif; font-size: 14px;">
        <span>✏️ Admin Mode Active</span>
        <button onclick="saveAllChanges()" style="background: #10b981; color: white; border: none; padding: 5px 15px; margin-left: 15px; border-radius: 3px; cursor: pointer;">Save Changes</button>
        <a href="?logout=true" style="color: #fca5a5; text-decoration: none; margin-left: 15px;">Logout</a>
    </div>
    <div style="height: 50px;"></div> <!-- Admin bar spacer -->
    <?php endif; ?>

    <div class="background-decoration"></div>

    <div class="container">
        <div class="logo-section">
            <h1 class="logo-title"><?php echo editable($content['hero']['title'] ?? 'NU: YOU HEALTH', 'hero.title', 'span'); ?></h1>
            <h2 class="logo-subtitle"><?php echo editable($content['hero']['subtitle'] ?? 'HALE', 'hero.subtitle', 'span'); ?></h2>
        </div>

        <div class="decorative-line"></div>

        <p class="coming-soon"><?php echo editable($content['coming_soon']['text'] ?? 'Coming Soon', 'coming_soon.text', 'span'); ?></p>

        <p class="description">
            <?php echo editable($content['description']['text'] ?? 'Your personalized approach to wellness is on its way. We\'re crafting a comprehensive health experience that puts you at the center of your care journey.', 'description.text', 'span'); ?>
        </p>

        <div class="button-container">
            <button class="btn btn-primary" onclick="showContactForm()">
                <?php echo editable($content['buttons']['primary']['text'] ?? 'Get In Touch', 'buttons.primary.text', 'span'); ?>
            </button>
            <a href="<?php echo htmlspecialchars($content['buttons']['secondary']['link'] ?? 'https://practitioner.nuyouuk.com'); ?>" class="btn btn-secondary" target="_blank">
                <?php echo editable($content['buttons']['secondary']['text'] ?? 'Practitioner Portal', 'buttons.secondary.text', 'span'); ?>
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
                    <h3 class="form-title"><?php echo editable($content['contact_form']['title'] ?? 'Get In Touch', 'contact_form.title', 'span'); ?></h3>
                    <p class="form-subtitle"><?php echo editable($content['contact_form']['subtitle'] ?? 'We\'d love to hear from you', 'contact_form.subtitle', 'span'); ?></p>
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

    <!-- JavaScript -->
    <script>
        // Contact Form Functions
        function showContactForm() {
            document.getElementById('contactOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideContactForm() {
            document.getElementById('contactOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('contactForm').reset();
            hideStatusMessage();
        }

        function showStatusMessage(message, type) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.textContent = message;
            statusDiv.className = `status-message ${type}`;
            statusDiv.style.display = 'block';
        }

        function hideStatusMessage() {
            const statusDiv = document.getElementById('status-message');
            statusDiv.style.display = 'none';
        }

        // Privacy Policy Functions
        function showPrivacyPolicy() {
            document.getElementById('contactFormContent').style.display = 'none';
            document.getElementById('privacyPolicyContent').style.display = 'block';
        }

        function hidePrivacyPolicy() {
            document.getElementById('privacyPolicyContent').style.display = 'none';
            document.getElementById('contactFormContent').style.display = 'block';
        }

        // Contact form submission
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitBtn = document.getElementById('submitBtn');
                    const originalText = submitBtn.textContent;

                    // Disable submit button
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Sending...';
                    hideStatusMessage();

                    // Prepare form data
                    const formData = new FormData(this);
                    <?php if (isset($_SESSION['csrf_token'])): ?>
                    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
                    <?php endif; ?>

                    // Submit form
                    fetch('contact-handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showStatusMessage('Thank you for your message! We\'ll be in touch soon.', 'success');
                            setTimeout(() => {
                                hideContactForm();
                            }, 2000);
                        } else {
                            showStatusMessage(data.message || 'There was an error sending your message. Please try again.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showStatusMessage('There was an error sending your message. Please try again.', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
            }

            // Close overlay when clicking outside the form
            const overlay = document.getElementById('contactOverlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        hideContactForm();
                    }
                });
            }

            // Close overlay with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideContactForm();
                }
            });

            // Add smooth hover effects
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    if (!this.disabled) {
                        this.style.transform = 'translateY(-2px)';
                    }
                });

                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        <?php if (IS_ADMIN): ?>
        // Admin functions
        function saveAllChanges() {
            const editableElements = document.querySelectorAll('.editable-text');
            const changes = {};
            const currentPage = '<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>';

            editableElements.forEach(element => {
                const field = element.dataset.field;
                const value = element.textContent;
                changes[field] = value;
            });

            if (Object.keys(changes).length === 0) {
                alert('No changes to save.');
                return;
            }

            const formData = new FormData();
            formData.append('page', currentPage);
            formData.append('changes', JSON.stringify(changes));
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('admin-save.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Changes saved successfully!');
                    location.reload();
                } else {
                    alert('Error saving changes: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving changes. Please try again.');
            });
        }

        // Make text editable on click
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.editable-text').forEach(element => {
                element.style.cursor = 'pointer';
                element.style.border = '1px dashed rgba(37, 99, 235, 0.3)';
                element.style.padding = '2px 4px';
                element.style.borderRadius = '3px';

                element.addEventListener('click', function() {
                    if (this.contentEditable === 'true') return;

                    this.contentEditable = true;
                    this.style.backgroundColor = '#eff6ff';
                    this.style.border = '1px solid #2563eb';
                    this.focus();

                    // Select all text
                    const range = document.createRange();
                    range.selectNodeContents(this);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                });

                element.addEventListener('blur', function() {
                    this.contentEditable = false;
                    this.style.backgroundColor = 'transparent';
                    this.style.border = '1px dashed rgba(37, 99, 235, 0.3)';
                });

                element.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === 'Escape') {
                        this.blur();
                        e.preventDefault();
                    }
                });
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>