# Email Notification System Setup Guide

This document provides instructions for setting up the email notification system in the Palliative Care Platform.

## Overview

The email notification system allows the platform to send automated emails for:

1. **Booking Confirmations**: When a transport booking is confirmed
2. **Booking Status Updates**: When a booking is completed or cancelled
3. **Booking Reminders**: Sent 24 hours before scheduled transport pickups
4. **Appointment Confirmations**: When a medical appointment is confirmed
5. **Appointment Reminders**: Sent 24 hours before scheduled appointments
6. **Payment Confirmations**: When a payment is successfully processed

## Installation Steps

### 1. Install Required Dependencies

The email system uses PHPMailer for sending emails. To install:

```bash
cd /path/to/palliative
php setup_email.php
```

This script will:
- Check if Composer is installed
- Create or update composer.json
- Install PHPMailer through Composer
- Create a logs directory if it doesn't exist
- Verify that email configuration is in the config file

### 2. Configure Email Settings

Edit the `config/config.php` file to set up your SMTP settings:

```php
// Email Configuration
define('MAIL_ENABLED', true); // Set to true to enable email sending
define('MAIL_HOST', 'smtp.gmail.com'); // SMTP server
define('MAIL_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('MAIL_USERNAME', 'your.email@gmail.com'); // SMTP username
define('MAIL_PASSWORD', 'ajlf iyny yijb xidr'); // SMTP password
define('MAIL_FROM', 'your.email@gmail.com'); // From email address
define('MAIL_FROM_NAME', 'Palliative Care System'); // From name
define('MAIL_REPLY_TO', 'support@yourcompany.com'); // Reply-to email address
```

#### Gmail SMTP Setup Note:
If using Gmail, you need to:
1. Enable 2-Step Verification for your Google Account
2. Create an App Password specifically for this application
3. Use that App Password instead of your regular Gmail password

### 3. Set Up Database Fields

The system requires additional fields in your database tables to track reminder status. These will be automatically added when needed, but you can also add them manually:

For cab_bookings table:
```sql
ALTER TABLE cab_bookings ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0;
ALTER TABLE cab_bookings ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN confirmed_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN completed_at DATETIME DEFAULT NULL;
ALTER TABLE cab_bookings ADD COLUMN cancelled_at DATETIME DEFAULT NULL;
```

For appointments table:
```sql
ALTER TABLE appointments ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0;
ALTER TABLE appointments ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL;
```

### 4. Set Up Cron Job for Reminders

To send automated reminders, set up a daily cron job:

```bash
# Run every day at 8:00 AM
0 8 * * * php /path/to/palliative/cron/send_reminders.php >> /path/to/palliative/logs/reminders.log 2>&1
```

To add this cron job:
1. Access your server's crontab: `crontab -e`
2. Add the line above, adjusting the paths
3. Save and exit

### 5. Testing the Email System

To test if emails are sending correctly:

1. Enable email in the config file
2. Confirm or update a booking status
3. Check the logs for email sending status
4. Verify the email was received by the recipient

## Troubleshooting

### Common Issues

1. **Emails not sending**:
   - Check if `MAIL_ENABLED` is set to `true`
   - Verify SMTP credentials are correct
   - Look for errors in the logs directory

2. **SMTP Connection Errors**:
   - Ensure your server allows outgoing connections on the SMTP port
   - Check if your hosting provider blocks SMTP traffic
   - Try using port 465 with SSL instead of port 587 with TLS

3. **Spam Issues**:
   - Set proper From and Reply-To headers
   - Configure SPF, DKIM, and DMARC records for your domain
   - Ensure email content is not flagged as spam

## Additional Customization

### Email Templates

Email templates are defined in the `EmailService` class. To customize the appearance or content:

1. Edit the `classes/EmailService.php` file
2. Modify the HTML and styling in the relevant email method
3. Add additional methods for new types of notifications

### Logging

Email sending logs are stored in the `logs` directory. You can adjust logging behavior in:
- `config/config.php` for general log settings
- `classes/EmailService.php` for email-specific logging 