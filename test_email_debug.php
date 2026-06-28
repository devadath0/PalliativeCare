<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config
require_once 'config/config.php';

// Check if PHPMailer is installed
echo "<h2>Checking PHPMailer Installation</h2>";
if (!file_exists('vendor/autoload.php')) {
    echo "<p style='color:red'>Error: Composer autoload file not found. PHPMailer might not be installed.</p>";
    echo "<p>Please install PHPMailer using Composer:</p>";
    echo "<pre>composer require phpmailer/phpmailer</pre>";
} else {
    echo "<p style='color:green'>Composer autoload file found.</p>";
    require_once 'vendor/autoload.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color:green'>PHPMailer class found.</p>";
    } else {
        echo "<p style='color:red'>Error: PHPMailer class not found even though autoload exists.</p>";
    }
}

// Check EmailService class
echo "<h2>Checking EmailService</h2>";
if (!file_exists('classes/EmailService.php')) {
    echo "<p style='color:red'>Error: EmailService.php file not found.</p>";
} else {
    echo "<p style='color:green'>EmailService.php file found.</p>";
    include_once 'classes/EmailService.php';
    
    if (class_exists('EmailService')) {
        echo "<p style='color:green'>EmailService class found.</p>";
    } else {
        echo "<p style='color:red'>Error: EmailService class not found even though the file exists.</p>";
    }
}

// Check SMTP settings
echo "<h2>Email Configuration</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

// Check email enabled
echo "<tr><td>MAIL_ENABLED</td><td>" . (MAIL_ENABLED ? 'true' : 'false') . "</td>";
if (MAIL_ENABLED) {
    echo "<td style='color:green'>Enabled</td>";
} else {
    echo "<td style='color:red'>Disabled - Emails won't be sent</td>";
}
echo "</tr>";

// Check SMTP host
echo "<tr><td>MAIL_HOST</td><td>" . MAIL_HOST . "</td>";
if (MAIL_HOST == 'smtp.gmail.com') {
    echo "<td style='color:green'>Correct for Gmail</td>";
} else {
    echo "<td style='color:red'>Not Gmail server</td>";
}
echo "</tr>";

// Check port
echo "<tr><td>MAIL_PORT</td><td>" . MAIL_PORT . "</td>";
if (MAIL_PORT == 587 || MAIL_PORT == 465) {
    echo "<td style='color:green'>Valid port</td>";
} else {
    echo "<td style='color:red'>Unusual port</td>";
}
echo "</tr>";

// Check encryption
echo "<tr><td>MAIL_ENCRYPTION</td><td>" . MAIL_ENCRYPTION . "</td>";
if (MAIL_ENCRYPTION == 'tls' || MAIL_ENCRYPTION == 'ssl') {
    echo "<td style='color:green'>Valid encryption</td>";
} else {
    echo "<td style='color:red'>Invalid encryption</td>";
}
echo "</tr>";

// Check username
echo "<tr><td>MAIL_USERNAME</td><td>" . MAIL_USERNAME . "</td>";
if (filter_var(MAIL_USERNAME, FILTER_VALIDATE_EMAIL)) {
    echo "<td style='color:green'>Valid email format</td>";
} else {
    echo "<td style='color:red'>Invalid email format</td>";
}
echo "</tr>";

// Check password (don't display the actual password)
echo "<tr><td>MAIL_PASSWORD</td><td>***********</td>";
if (!empty(MAIL_PASSWORD)) {
    echo "<td style='color:green'>Set</td>";
} else {
    echo "<td style='color:red'>Empty</td>";
}
echo "</tr>";

// Check from email
echo "<tr><td>MAIL_FROM</td><td>" . MAIL_FROM . "</td>";
if (filter_var(MAIL_FROM, FILTER_VALIDATE_EMAIL)) {
    echo "<td style='color:green'>Valid email format</td>";
} else {
    echo "<td style='color:red'>Invalid email format</td>";
}
echo "</tr>";

echo "</table>";

// Try to send a test email
echo "<h2>Test Email Connection</h2>";

try {
    // Create a new PHPMailer instance directly for testing
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port = MAIL_PORT;
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress('doffy074@gmail.com'); // Add a recipient (replace with your email)
    $mail->Subject = 'PHPMailer Test Email';
    $mail->Body = 'This is a test email to verify SMTP connection.';
    
    echo "<p>Attempting to send test email...</p>";
    echo "<pre>";
    $mail->send();
    echo "</pre>";
    
    echo "<p style='color:green'>Message has been sent!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    echo "<p>Exception details: " . $e->getMessage() . "</p>";
}

// Check if the email logs directory exists
echo "<h2>Email Logs Check</h2>";
if (!is_dir('logs')) {
    echo "<p style='color:red'>Logs directory doesn't exist.</p>";
} else {
    echo "<p style='color:green'>Logs directory exists.</p>";
}

// Check email for outbound connections to Gmail
echo "<h2>Network Connection Test</h2>";
echo "<p>Testing connection to Gmail SMTP server:</p>";
$connection = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5);
if ($connection) {
    echo "<p style='color:green'>Connection to smtp.gmail.com:587 successful!</p>";
    fclose($connection);
} else {
    echo "<p style='color:red'>Could not connect to smtp.gmail.com:587. Error: $errstr ($errno)</p>";
    echo "<p>This could indicate a firewall or network issue.</p>";
}
?> 