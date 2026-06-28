<?php
/**
 * Booking Reminders Cron Job
 * This script should be set up to run daily via cron
 * Example crontab entry: 0 8 * * * php /path/to/palliative/cron/send_reminders.php
 */

// Set script execution time limit
set_time_limit(300); // 5 minutes

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BookingReminder.php';

// Start the reminder job
$reminder = new BookingReminder();
$results = $reminder->runAllReminders();

// Output results for logging
echo "===== BOOKING REMINDER RESULTS =====\n";
echo "Timestamp: " . $results['timestamp'] . "\n";
echo "Execution Time: " . $results['execution_time'] . "\n\n";

echo "Transport Reminders:\n";
echo "  Success: " . $results['transport_reminders']['success'] . "\n";
echo "  Failure: " . $results['transport_reminders']['failure'] . "\n";
echo "  Skipped: " . $results['transport_reminders']['skipped'] . "\n";

echo "\nAppointment Reminders:\n";
echo "  Success: " . $results['appointment_reminders']['success'] . "\n";
echo "  Failure: " . $results['appointment_reminders']['failure'] . "\n";
echo "  Skipped: " . $results['appointment_reminders']['skipped'] . "\n";

echo "\nTotal Success: " . $results['total_success'] . "\n";
echo "Total Failure: " . $results['total_failure'] . "\n";

// Log errors if any
if (!empty($results['transport_reminders']['errors']) || !empty($results['appointment_reminders']['errors'])) {
    echo "\nErrors:\n";
    
    if (!empty($results['transport_reminders']['errors'])) {
        echo "Transport Errors:\n";
        foreach ($results['transport_reminders']['errors'] as $error) {
            echo "  - $error\n";
        }
    }
    
    if (!empty($results['appointment_reminders']['errors'])) {
        echo "Appointment Errors:\n";
        foreach ($results['appointment_reminders']['errors'] as $error) {
            echo "  - $error\n";
        }
    }
}

echo "\n===== REMINDER JOB COMPLETED =====\n";
?> 