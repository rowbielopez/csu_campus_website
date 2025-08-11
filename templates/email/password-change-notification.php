<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed - CSU Campus Website</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .alert { background: white; border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .button { background: #007bff; color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Password Changed Successfully</h1>
        </div>
        
        <div class="content">
            <h2>Hello <?= htmlspecialchars($user_name) ?>,</h2>
            
            <p>This email confirms that your password was successfully changed on <?= htmlspecialchars($change_time) ?>.</p>
            
            <div class="alert" style="background: #d4edda; border-color: #c3e6cb;">
                <h4>✅ Security Update Completed</h4>
                <p>Your CSU Campus Website account password has been updated successfully.</p>
            </div>
            
            <p>You can now use your new password to log in to your account:</p>
            
            <p style="text-align: center;">
                <a href="<?= htmlspecialchars($login_url) ?>" class="button">Login to Your Account</a>
            </p>
            
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 5px;">
                <h4>⚠️ Didn't Change Your Password?</h4>
                <p>If you didn't change your password, please contact our support team immediately at 
                   <a href="mailto:<?= htmlspecialchars($support_email) ?>"><?= htmlspecialchars($support_email) ?></a></p>
                <p>Your account security may be compromised.</p>
            </div>
            
            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 15px 0; border-radius: 5px;">
                <h4>🔐 Security Tips:</h4>
                <ul>
                    <li>Keep your password confidential</li>
                    <li>Use a unique password for your CSU account</li>
                    <li>Log out from shared computers</li>
                    <li>Report any suspicious activity immediately</li>
                </ul>
            </div>
            
            <p>If you have any questions or need assistance, please contact our support team at 
               <a href="mailto:<?= htmlspecialchars($support_email) ?>"><?= htmlspecialchars($support_email) ?></a></p>
        </div>
        
        <div class="footer">
            <p>This email was sent by CSU Campus Website Management System</p>
            <p>This is an automated security notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
