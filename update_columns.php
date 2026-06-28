<?php
// Database configuration
require_once 'config/database.php';

try {
    // First, make sure patient_response column exists
    $stmt = $db->query("SHOW COLUMNS FROM patient_issues LIKE 'patient_response'");
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, add it
        $db->exec("ALTER TABLE patient_issues ADD COLUMN patient_response TEXT NULL AFTER admin_response");
        echo "Added patient_response column<br>";
    } else {
        echo "patient_response column already exists<br>";
    }
    
    // Check if admin_response_at column exists and add it if needed
    $stmt = $db->query("SHOW COLUMNS FROM patient_issues LIKE 'admin_response_at'");
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, add it after resolved_at
        $db->exec("ALTER TABLE patient_issues ADD COLUMN admin_response_at DATETIME NULL AFTER resolved_at");
        echo "Added admin_response_at column<br>";
    } else {
        echo "admin_response_at column already exists<br>";
    }
    
    // Now check for patient_response_at and add it
    $stmt = $db->query("SHOW COLUMNS FROM patient_issues LIKE 'patient_response_at'");
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, add it at the end
        $db->exec("ALTER TABLE patient_issues ADD COLUMN patient_response_at DATETIME NULL");
        echo "Added patient_response_at column<br>";
    } else {
        echo "patient_response_at column already exists<br>";
    }
    
    echo "<hr>Database update complete!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 