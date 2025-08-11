<?php
/**
 * Email Configuration
 * Configure email settings for the application
 */

// Load SMTP credentials from environment or config first
if (file_exists(__DIR__ . '/email-credentials.php')) {
    require_once __DIR__ . '/email-credentials.php';
}

// Email Configuration - only define if not already defined
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP server
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls'); // tls or ssl
if (!defined('SMTP_AUTH')) define('SMTP_AUTH', true);

// Set defaults only if not already defined
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', ''); // Your email address - set in environment
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', ''); // Your email password/app password - set in environment
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', ''); // Default from email - set in environment
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'CSU Campus Website'); // Default from name

// Email Templates Directory
if (!defined('EMAIL_TEMPLATES_PATH')) define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../templates/email/');
?>
