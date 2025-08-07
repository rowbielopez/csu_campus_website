<?php
/**
 * Email Configuration
 * Configure email settings for the application
 */

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP server
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls or ssl
define('SMTP_AUTH', true);
define('SMTP_USERNAME', ''); // Your email address - set in environment
define('SMTP_PASSWORD', ''); // Your email password/app password - set in environment
define('SMTP_FROM_EMAIL', ''); // Default from email - set in environment
define('SMTP_FROM_NAME', 'CSU Campus Website'); // Default from name

// Email Templates Directory
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../templates/email/');

// Load SMTP credentials from environment or config
if (file_exists(__DIR__ . '/email-credentials.php')) {
    require_once __DIR__ . '/email-credentials.php';
}
?>
