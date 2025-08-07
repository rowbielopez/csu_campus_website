# Email Setup Guide

## 📧 Email Configuration for CSU Campus Website

### Quick Setup

1. **Copy the credentials template:**
   ```bash
   copy config\email-credentials-sample.php config\email-credentials.php
   ```

2. **Edit `config/email-credentials.php`** with your email settings

3. **Test the configuration** by visiting `http://localhost/campus_website2/test-email.php`

---

## 🔧 Email Provider Settings

### Gmail Configuration
1. **Enable 2-Factor Authentication** on your Google account
2. **Generate an App Password:**
   - Go to Google Account → Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. **Update credentials file:**
   ```php
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-16-digit-app-password');
   define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
   ```

### Outlook/Hotmail Configuration
```php
define('SMTP_HOST', 'smtp.live.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'your-email@outlook.com');
```

### Yahoo Mail Configuration
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yahoo.com');
define('SMTP_PASSWORD', 'your-app-password'); // Generate in Yahoo account settings
define('SMTP_FROM_EMAIL', 'your-email@yahoo.com');
```

### Custom Domain/Hosting Provider
```php
define('SMTP_HOST', 'mail.yourdomain.com');
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_SECURE', 'tls'); // or 'ssl'
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'CSU Campus Website');
```

---

## 🧪 Testing Email Functionality

### 1. Basic Configuration Test
Visit: `http://localhost/campus_website2/test-email.php`

### 2. User Invitation Test
1. Go to Admin → Users → Create User
2. Fill out the form
3. Check "Send invitation email to user"
4. Create the user

### 3. Password Reset Test
1. Go to Admin → Users → View User
2. Click "Reset Password"
3. Enter new password
4. Check "Send new password to user via email"
5. Submit

---

## 📁 File Structure

```
config/
├── email.php                    # Email configuration
├── email-credentials-sample.php # Template file
└── email-credentials.php        # Your actual credentials (not in git)

core/classes/
└── EmailService.php             # Email service class

templates/email/
├── user-invitation.php          # User invitation template
└── password-reset.php           # Password reset template

test-email.php                   # Email testing utility
```

---

## 🔐 Security Notes

1. **Never commit `email-credentials.php`** to version control
2. **Use App Passwords** for Gmail/Yahoo instead of account passwords
3. **Use dedicated email addresses** like `noreply@yourdomain.com` for system emails
4. **Enable SMTP authentication** for security
5. **Use TLS encryption** when available

---

## 🐛 Troubleshooting

### Common Issues

**"Email service not configured"**
- Check if `config/email-credentials.php` exists
- Verify all required constants are defined

**"SMTP connection failed"**
- Check SMTP host and port settings
- Verify firewall/antivirus isn't blocking connection
- Test with different SMTP security settings (TLS/SSL)

**"Authentication failed"**
- Verify username and password
- For Gmail: Use App Password, not account password
- Check if 2FA is enabled and configured correctly

**"Mail not received"**
- Check spam/junk folders
- Verify recipient email address
- Check email server logs for delivery issues

### Testing SMTP Settings
You can test SMTP settings outside the application using tools like:
- **Telnet**: `telnet smtp.gmail.com 587`
- **Online SMTP testers**
- **Mail clients** like Thunderbird or Outlook

---

## 📈 Email Usage in the System

### User Invitation Emails
- Sent when creating users with "Send invitation email" checked
- Contains username and temporary password
- Includes login link and security instructions

### Password Reset Emails
- Sent when resetting user passwords via admin panel
- Contains new password
- Includes security recommendations

### Customization
- Email templates are in `templates/email/`
- Templates use PHP for dynamic content
- HTML emails with plain text fallbacks
- Responsive design for mobile devices

---

## 🔄 Email Queue (Future Enhancement)

For high-volume environments, consider implementing:
- Background job processing
- Email queue system
- Retry logic for failed emails
- Email delivery tracking
