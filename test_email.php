<?php
require_once 'config/config.php';

// If the vendor directory and autoloader exist, use it
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    // Otherwise, manually include PHPMailer files
    echo "Autoloader not found. Make sure Composer has been installed.<br>";
}

// Include the EmailService class manually if it's not autoloaded
if (!class_exists('EmailService') && file_exists('classes/EmailService.php')) {
    require_once 'classes/EmailService.php';
}

// Check if the EmailService class exists
if (!class_exists('EmailService')) {
    die("EmailService class not found. Please check your class files.");
}

// Create a basic test email
$emailService = new EmailService();
$recipientEmail = 'ashishj.inmca2227@saintgits.org'; // Change to your email address
$recipientName = 'Test User';

$testData = [
    'id' => '12345',
    'amount' => '100.00',
    'date' => date('Y-m-d'),
    'service' => 'Test Service'
];

// Send test email
$result = $emailService->sendPaymentConfirmation($recipientEmail, $recipientName, $testData);

// Output result
if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed. Check logs for details.";
}
?> 