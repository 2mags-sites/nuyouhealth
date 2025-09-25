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

    </script>

    <?php if (IS_ADMIN): ?>
    <script>
        // Admin functions for editable content
        let editedFields = {};

        document.addEventListener('DOMContentLoaded', function() {
            initTextEditing();
            initImageEditing();

            // Add save changes button handler
            const saveButton = document.getElementById('save-changes');
            if (saveButton) {
                saveButton.addEventListener('click', saveAllChanges);
            }

            // Add FAQ button handler
            const addFaqButton = document.getElementById('add-faq');
            if (addFaqButton) {
                addFaqButton.addEventListener('click', addNewFAQ);
            }
        });

        function initTextEditing() {
            const editableElements = document.querySelectorAll('.editable-content');

            editableElements.forEach(element => {
                element.contentEditable = true;
                element.style.cursor = 'text';
                element.style.minHeight = '1.2em';
                element.style.outline = 'none';
                element.style.border = '1px solid transparent';
                element.style.padding = '2px 4px';
                element.style.borderRadius = '3px';
                element.style.transition = 'border-color 0.3s ease';

                element.addEventListener('focus', function() {
                    this.style.border = '1px solid #3b82f6';
                    this.style.backgroundColor = '#f8fafc';
                });

                element.addEventListener('blur', function() {
                    this.style.border = '1px solid transparent';
                    this.style.backgroundColor = 'transparent';

                    const field = this.getAttribute('data-field');
                    const value = this.textContent;
                    editedFields[field] = value;
                });
            });
        }

        function initImageEditing() {
            const editableImages = document.querySelectorAll('.editable-image-container');

            editableImages.forEach(container => {
                const overlay = container.querySelector('.image-edit-overlay');

                container.addEventListener('mouseenter', function() {
                    if (overlay) overlay.style.display = 'block';
                });

                container.addEventListener('mouseleave', function() {
                    if (overlay) overlay.style.display = 'none';
                });

                container.addEventListener('click', function() {
                    const field = this.getAttribute('data-field');
                    const imgElement = this.querySelector('img');

                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';

                    input.onchange = function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const formData = new FormData();
                            formData.append('image', file);
                            formData.append('field', field);

                            imgElement.style.opacity = '0.5';

                            fetch('admin-upload.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    imgElement.src = data.file.url;
                                    editedFields[field] = data.file.url;
                                    alert('Image uploaded! Click "Save Changes" to save permanently.');
                                } else {
                                    alert('Upload failed: ' + data.message);
                                }
                                imgElement.style.opacity = '1';
                            })
                            .catch(error => {
                                alert('Upload error: ' + error);
                                imgElement.style.opacity = '1';
                            });
                        }
                    };

                    input.click();
                });
            });
        }

        function saveAllChanges() {
            if (Object.keys(editedFields).length === 0) {
                alert('No changes to save');
                return;
            }

            const currentPage = window.location.pathname.replace('/', '').replace('.php', '') || 'index';

            fetch('admin-save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    page: currentPage,
                    fields: editedFields,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Changes saved successfully!');
                    editedFields = {};
                    location.reload();
                } else {
                    alert('Error saving changes: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Error saving changes: ' + error.message || error);
            });
        }

        function addNewFAQ() {
            alert('FAQ functionality not implemented yet for this page structure');
        }
    </script>
    <?php endif; ?>

</body>
</html>