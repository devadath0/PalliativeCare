<?php
/**
 * Web-based Reminder Trigger
 * This script can be called via web request to send reminders
 * It includes basic security to prevent unauthorized access
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BookingReminder.php';

// Basic security check - you can enhance this based on your needs
$allowed_ips = ['127.0.0.1', '::1']; // Add your server's IP address
$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips)) {
    die('Access denied');
}

// Start the reminder job
$reminder = new BookingReminder();
$results = $reminder->runAllReminders();

// Output results
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results
]); 