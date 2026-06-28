<?php
/**
 * Update Patient Issues Table Schema
 * Adds columns for patient response functionality
 */

// Include database configuration
require_once 'config/database.php';

try {
    // Check if patient_response and patient_response_at columns exist
    $columns = [];
    $stmt = $db->query("SHOW COLUMNS FROM patient_issues");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    // Output current columns
    echo "Current columns in patient_issues table:\n";
    echo implode(", ", $columns) . "\n\n";
    
    // Add patient_response column if it doesn't exist
    if (!in_array('patient_response', $columns)) {
        echo "Adding patient_response column...\n";
        $db->query("ALTER TABLE patient_issues ADD COLUMN patient_response TEXT NULL AFTER admin_response");
        echo "patient_response column added successfully!\n";
    } else {
        echo "patient_response column already exists.\n";
    }
    
    // Add patient_response_at column if it doesn't exist
    if (!in_array('patient_response_at', $columns)) {
        echo "Adding patient_response_at column...\n";
        $db->query("ALTER TABLE patient_issues ADD COLUMN patient_response_at DATETIME NULL AFTER admin_response_at");
        echo "patient_response_at column added successfully!\n";
    } else {
        echo "patient_response_at column already exists.\n";
    }
    
    echo "\nSchema update completed successfully!";
    
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
} 