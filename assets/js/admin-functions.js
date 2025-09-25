/**
 * STANDARDIZED ADMIN FUNCTIONS
 * Drop-in component for all PHP websites
 * Handles text editing, image uploads, FAQ management
 *
 * USAGE: Include this in footer.php when isAdminMode() is true
 * REQUIRES: admin-upload.php and admin-save.php endpoints
 */

// Global variable to track edited fields
let editedFields = {};

console.log('Admin functions script loaded');

// Initialize admin functions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing admin functions');
    initTextEditing();
    initImageEditing();
    initHeroImageEditing();
    initFAQManagement();
});

/**
 * Initialize text content editing
 */
function initTextEditing() {
    const editableElements = document.querySelectorAll('.editable-content');
    console.log('Found editable elements:', editableElements.length);

    editableElements.forEach(element => {
        element.contentEditable = true;
        console.log('Made editable:', element.getAttribute('data-field'));

        element.addEventListener('blur', function() {
            const field = this.getAttribute('data-field');
            const value = this.innerHTML;
            editedFields[field] = value;
            console.log('Field edited:', field, value);
        });
    });
}

/**
 * Initialize image upload functionality
 */
function initImageEditing() {
    const editableImages = document.querySelectorAll('.editable-image');

    editableImages.forEach(img => {
        img.style.cursor = 'pointer';
        img.style.border = '2px solid transparent';
        img.style.transition = 'border 0.3s ease';

        // Add hover effect
        img.addEventListener('mouseenter', function() {
            this.style.border = '2px dashed #2563eb';
        });

        img.addEventListener('mouseleave', function() {
            this.style.border = '2px solid transparent';
        });

        // Handle click to upload new image
        img.addEventListener('click', function() {
            const field = this.getAttribute('data-field');
            const placeholder = this.getAttribute('data-placeholder');
            const imgElement = this;

            // Create file input
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';

            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Create FormData for upload
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('field', field);

                    // Show loading state
                    imgElement.style.opacity = '0.5';

                    // Upload to server
                    fetch('/admin-upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Check if response is ok
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text(); // Get as text first to debug
                    })
                    .then(text => {
                        try {
                            return JSON.parse(text); // Try to parse as JSON
                        } catch (e) {
                            console.error('Response was not JSON:', text);
                            throw new Error('Invalid JSON response: ' + text);
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            // Update image src with uploaded file URL
                            imgElement.src = data.file.url;
                            imgElement.style.opacity = '1';

                            // Store URL in editedFields for saving
                            editedFields[field] = data.file.url;

                            alert('Image uploaded! Click "Save Changes" to save permanently.');
                        } else {
                            alert('Upload failed: ' + data.message);
                            imgElement.style.opacity = '1';
                        }
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

/**
 * Initialize hero background image editing
 */
function initHeroImageEditing() {
    const heroSections = document.querySelectorAll('.page-hero');
    console.log('Found hero sections:', heroSections.length);

    heroSections.forEach(section => {
        const heroImage = section.querySelector('.editable-hero-bg');
        const editOverlay = section.querySelector('.hero-edit-overlay');
        const heroContent = section.querySelector('.hero-content-single');

        if (heroImage && editOverlay) {
            console.log('Found editable hero image and overlay');

            // Remove the inline onclick first and re-add it
            editOverlay.removeAttribute('onclick');

            // Move to the section level, not inside hero-image
            section.appendChild(editOverlay);

            // Move the edit button to top-right corner
            editOverlay.style.position = 'absolute';
            editOverlay.style.top = '20px';
            editOverlay.style.right = '20px';
            editOverlay.style.left = 'auto';
            editOverlay.style.transform = 'none';
            editOverlay.style.zIndex = '999';  // Very high z-index
            editOverlay.style.display = 'block';
            editOverlay.style.opacity = '0';
            editOverlay.style.transition = 'opacity 0.3s ease';
            editOverlay.style.cursor = 'pointer';
            editOverlay.style.pointerEvents = 'none';  // Start with no pointer events

            // Make the hero content (H1) have higher z-index so it's clickable
            if (heroContent) {
                heroContent.style.zIndex = '60';
                heroContent.style.pointerEvents = 'auto';
            }

            // Show edit button on hero section hover
            section.addEventListener('mouseenter', function(e) {
                console.log('Mouse entered hero section');
                editOverlay.style.opacity = '1';
                editOverlay.style.pointerEvents = 'auto';  // Enable clicks when visible
            });

            section.addEventListener('mouseleave', function(e) {
                console.log('Mouse left hero section');
                editOverlay.style.opacity = '0';
                editOverlay.style.pointerEvents = 'none';  // Disable clicks when hidden
            });

            // Make the edit button clickable
            editOverlay.addEventListener('click', function(e) {
                console.log('Edit button clicked - event listener');
                e.preventDefault();
                e.stopPropagation();
                window.editHeroImage(editOverlay);
            }, true);  // Use capture phase

            // Test if element is actually clickable
            editOverlay.addEventListener('mousedown', function(e) {
                console.log('Edit button mousedown');
            });

            editOverlay.addEventListener('mouseup', function(e) {
                console.log('Edit button mouseup');
            });

            console.log('Edit overlay setup complete:', editOverlay);
        } else {
            console.log('No editable hero image or overlay found in section');
        }
    });
}

/**
 * Handle hero image upload
 */
window.editHeroImage = function(overlayElement) {
    // Find the actual hero image div with the data attributes
    const heroSection = overlayElement.closest('.page-hero');
    const heroDiv = heroSection ? heroSection.querySelector('.editable-hero-bg') : null;

    if (!heroDiv) {
        console.error('Could not find hero image div');
        alert('Error: Could not find hero image element');
        return;
    }

    const field = heroDiv.getAttribute('data-field');
    const page = heroDiv.getAttribute('data-page');

    console.log('Hero image div found:', heroDiv);
    console.log('Field:', field, 'Page:', page);

    // Create file input
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';

    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            // Create FormData for upload
            const formData = new FormData();
            formData.append('image', file);
            formData.append('field', field);

            // Show loading state
            overlayElement.innerHTML = '⏳ Uploading...';

            // Upload to server
            fetch('/admin-upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Response was not JSON:', text);
                    throw new Error('Invalid JSON response');
                }
            })
            .then(data => {
                if (data.success) {
                    // Update background image
                    heroDiv.style.backgroundImage = `url('${data.file.url}')`;

                    // Store URL in editedFields for saving
                    editedFields[field] = data.file.url;

                    // Restore overlay text
                    overlayElement.innerHTML = '📷 Click to Change Hero Image';

                    alert('Hero image uploaded! Click "Save Changes" to save permanently.');
                } else {
                    alert('Upload failed: ' + data.message);
                    overlayElement.innerHTML = '📷 Click to Change Hero Image';
                }
            })
            .catch(error => {
                alert('Upload error: ' + error);
                overlayElement.innerHTML = '📷 Click to Change Hero Image';
            });
        }
    };

    input.click();
}

/**
 * Initialize FAQ management functionality
 */
function initFAQManagement() {
    // Add remove buttons to existing FAQs
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach((item, index) => {
        if (!item.querySelector('.faq-remove-btn')) {
            const removeBtn = document.createElement('button');
            removeBtn.className = 'faq-remove-btn';
            removeBtn.innerHTML = '× Remove';
            removeBtn.style.cssText = 'background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; float: right; margin: 5px;';
            removeBtn.onclick = function() {
                removeFAQ(index);
            };
            item.insertBefore(removeBtn, item.firstChild);
        }
    });
}

/**
 * Add new FAQ
 */
function addNewFAQ() {
    const question = prompt('Enter FAQ question:');
    if (!question) return;

    const answer = prompt('Enter FAQ answer:');
    if (!answer) return;

    // Get current FAQs from page
    const faqContainer = document.querySelector('.faq-accordion') || document.querySelector('.faq-container');
    if (!faqContainer) {
        alert('FAQ section not found on this page');
        return;
    }

    // Create new FAQ item
    const newFAQ = document.createElement('div');
    newFAQ.className = 'faq-item';
    newFAQ.innerHTML = `
        <button class="faq-remove-btn" style="background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; float: right; margin: 5px;">× Remove</button>
        <div class="faq-question">${question}</div>
        <div class="faq-answer">
            <p>${answer}</p>
        </div>
    `;

    // Add to container
    faqContainer.appendChild(newFAQ);

    // Get current FAQs count to create the field path
    const faqCount = faqContainer.querySelectorAll('.faq-item').length - 1;

    // Store in editedFields
    editedFields[`faqs.${faqCount}.question`] = question;
    editedFields[`faqs.${faqCount}.answer`] = answer;

    // Re-initialize FAQ management
    initFAQManagement();

    alert('FAQ added! Remember to save changes.');
}

/**
 * Remove FAQ
 */
function removeFAQ(index) {
    if (!confirm('Are you sure you want to remove this FAQ?')) return;

    const faqContainer = document.querySelector('.faq-accordion') || document.querySelector('.faq-container');
    const faqItems = faqContainer.querySelectorAll('.faq-item');

    if (faqItems[index]) {
        faqItems[index].remove();

        // Mark for deletion in editedFields
        editedFields[`faqs.${index}`] = null;

        alert('FAQ removed! Remember to save changes.');
    }
}

/**
 * Edit Page SEO
 */
function editPageSEO() {
    const currentPage = window.location.pathname.replace('/', '').replace('.php', '') || 'index';

    // Get current meta values from the page
    const currentTitle = document.querySelector('title')?.textContent || '';
    const currentDescription = document.querySelector('meta[name="description"]')?.content || '';
    const currentKeywords = document.querySelector('meta[name="keywords"]')?.content || '';

    // Create modal HTML
    const modalHTML = `
        <div id="seo-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10001; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <h2 style="margin-top: 0; color: #333;">Edit Page SEO</h2>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Page Title</label>
                    <input type="text" id="seo-title" value="${currentTitle.replace(/"/g, '&quot;')}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <small style="color: #666;">Recommended: 50-60 characters</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Meta Description</label>
                    <textarea id="seo-description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;">${currentDescription.replace(/"/g, '&quot;')}</textarea>
                    <small style="color: #666;">Recommended: 150-160 characters</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Keywords</label>
                    <input type="text" id="seo-keywords" value="${currentKeywords.replace(/"/g, '&quot;')}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <small style="color: #666;">Separate keywords with commas</small>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button onclick="closeSEOModal()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Cancel</button>
                    <button onclick="saveSEOChanges()" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Save SEO</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

/**
 * Close SEO Modal
 */
function closeSEOModal() {
    const modal = document.getElementById('seo-modal');
    if (modal) {
        modal.remove();
    }
}

/**
 * Save SEO Changes
 */
function saveSEOChanges() {
    const title = document.getElementById('seo-title').value;
    const description = document.getElementById('seo-description').value;
    const keywords = document.getElementById('seo-keywords').value;

    // Add to editedFields
    editedFields['meta.title'] = title;
    editedFields['meta.description'] = description;
    editedFields['meta.keywords'] = keywords;

    // Update the page's actual meta tags for immediate preview
    document.title = title;
    const metaDescription = document.querySelector('meta[name="description"]');
    if (metaDescription) metaDescription.content = description;
    const metaKeywords = document.querySelector('meta[name="keywords"]');
    if (metaKeywords) metaKeywords.content = keywords;

    // Also update OG tags
    const ogTitle = document.querySelector('meta[property="og:title"]');
    if (ogTitle) ogTitle.content = title;
    const ogDescription = document.querySelector('meta[property="og:description"]');
    if (ogDescription) ogDescription.content = description;

    // Close modal
    closeSEOModal();

    alert('SEO settings updated! Remember to click "Save Changes" to save permanently.');
}

/**
 * Save all changes
 */
function saveAllChanges() {
    if (Object.keys(editedFields).length === 0) {
        alert('No changes to save');
        return;
    }

    const currentPage = window.location.pathname.replace('/', '').replace('.php', '') || 'index';

    fetch('/admin-save.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            page: currentPage,
            fields: editedFields,
            csrf_token: document.querySelector('meta[name="csrf-token"]').content || ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Changes saved successfully!');
            editedFields = {};
            location.reload(); // Reload to show saved changes
        } else {
            alert('Error saving changes: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error saving changes: ' + error);
    });
}