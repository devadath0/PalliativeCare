<?php
// Database configuration
require_once 'config/database.php';

try {
    // Check if patient_alerts table exists
    $table_exists = $db->query("SHOW TABLES LIKE 'patient_alerts'")->rowCount() > 0;
    
    if (!$table_exists) {
        echo "Patient alerts table doesn't exist. Please create it first via the system.";
    } else {
        // Check if 'patient_issue' is already in the enum values
        $stmt = $db->query("SHOW COLUMNS FROM patient_alerts LIKE 'alert_type'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Parse the enum values
        if ($column) {
            // Extract enum values from the type definition
            preg_match("/^enum\(\'(.*)\'\)$/", $column['Type'], $matches);
            if (isset($matches[1])) {
                $enum_values = explode("','", $matches[1]);
                
                if (!in_array('patient_issue', $enum_values)) {
                    // Add 'patient_issue' to the enum
                    $enum_values[] = 'patient_issue';
                    $new_enum = "enum('" . implode("','", $enum_values) . "')";
                    
                    // Alter the table
                    $db->exec("ALTER TABLE patient_alerts MODIFY COLUMN alert_type {$new_enum} NOT NULL DEFAULT 'system'");
                    echo "Successfully added 'patient_issue' to alert_type enum.<br>";
                } else {
                    echo "'patient_issue' is already in the alert_type enum.<br>";
                }
            }
        }
    }
    
    echo "<hr>Update completed!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 