# Campus Event Hub Configuration Template
# Copy this to config/database.php and update with your values

<?php
/**
 * Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_NAME', 'campus_event_hub');

// Optional: Database port
define('DB_PORT', 3306);

/**
 * Email Configuration (for reminders)
 */

// Email service (options: 'php_mail', 'smtp', 'sendgrid', 'mailgun')
define('EMAIL_SERVICE', 'php_mail');

// SMTP Configuration (if using SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// SendGrid Configuration (if using SendGrid)
define('SENDGRID_API_KEY', 'your-sendgrid-api-key');

// Default "From" email
define('FROM_EMAIL', 'noreply@campuseventhub.edu');
define('FROM_NAME', 'Campus Event Hub');

/**
 * SMS Configuration (for SMS reminders)
 */

// SMS provider (options: 'twilio', 'vonage', 'aws_sns')
define('SMS_PROVIDER', 'twilio');

// Twilio Configuration
define('TWILIO_ACCOUNT_SID', 'your-account-sid');
define('TWILIO_AUTH_TOKEN', 'your-auth-token');
define('TWILIO_PHONE_NUMBER', '+1234567890');

/**
 * Application Settings
 */

// Timezone
date_default_timezone_set('UTC');

// Session timeout (in minutes)
define('SESSION_TIMEOUT', 30);

// Items per page
define('ITEMS_PER_PAGE', 12);

// Enable debug mode
define('DEBUG_MODE', false);

// Application URL
define('APP_URL', 'http://localhost/campus-event-hub/public/');

?>
