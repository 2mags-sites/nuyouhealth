# Nu:You Health Website

A production-ready PHP website for Nu:You Health's coming soon landing page.

## Features

- **Landing Page**: Elegant coming soon page with contact form
- **Contact Form**: AJAX-powered contact form with spam protection
- **Admin Mode**: Editable content management system
- **Responsive Design**: Mobile-first responsive layout
- **Security**: Production-ready security configurations
- **SEO Optimized**: Schema.org markup, meta tags, and clean URLs

## Getting Started

### Local Development

1. Navigate to the website directory:
   ```bash
   cd website-rebuilder/project-nuyouhealth/php-website
   ```

2. Start the PHP development server:
   ```bash
   php -S localhost:8000
   ```

3. Open your browser to: `http://localhost:8000`

### Admin Mode

Access admin mode by adding `?admin=nuyou_admin_2025_secure_key` to the URL:
- `http://localhost:8000?admin=nuyou_admin_2025_secure_key`

In admin mode you can:
- Edit any text content by clicking on it
- Upload and change images
- Save all changes with the "Save Changes" button
- Log out with the "Logout" link

### Contact Form Setup

1. Update `.env` with your email settings:
   ```
   CONTACT_TO_EMAIL=your-email@domain.com
   SENDGRID_API_KEY=your-sendgrid-key-here
   ```

2. The contact form includes:
   - CSRF protection
   - Honeypot spam filtering
   - Rate limiting
   - Email delivery via SendGrid (with PHP mail() fallback)

### File Structure

```
php-website/
├── index.php              # Main landing page
├── contact-handler.php    # Contact form processor
├── admin-save.php         # Content save handler
├── admin-upload.php       # Image upload handler
├── .env                   # Environment variables
├── .htaccess              # Security and clean URLs
├── assets/
│   ├── css/
│   │   └── styles.css     # Main stylesheet
│   └── images/
│       └── uploads/       # User uploaded images
├── includes/
│   ├── admin-config.php   # Admin system configuration
│   ├── config.php         # Site configuration
│   ├── email-service.php  # Email sending service
│   └── env-loader.php     # Environment loader
└── content/
    └── index.json         # Editable content data
```

## Security Features

- Environment variables for sensitive data
- CSRF token protection
- Honeypot spam filtering
- Rate limiting on forms
- Protected sensitive files and directories
- Secure file upload handling
- Admin access restrictions

## Production Deployment

1. Upload files to your web server
2. Update `.env` with production values
3. Ensure proper file permissions:
   - `.env`: 600 (read/write for owner only)
   - `uploads/`: 777 (writable for uploads)
   - Other files: 644 (readable)

## Content Management

All content is stored in JSON files in the `content/` directory. Use admin mode to edit content through the web interface, or edit the JSON files directly.

## Brand Colors

- Primary: `#2c3e35` (Dark green)
- Secondary: `#6b8a7a` (Medium green)
- Accent: `#4a6b5a` (Forest green)
- Light: `#a0bfae` (Light green)
- Background: `#f8fffe` (Off white)

## Typography

- Font Family: Playfair Display (serif)
- Elegant, sophisticated styling with proper letter spacing
- Responsive font sizes for mobile devices

## Support

For technical support or modifications, refer to the PHPSITE_COMMAND.md documentation in the website-rebuilder directory.