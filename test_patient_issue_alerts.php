<?php
// Database configuration
require_once 'config/database.php';

// Start output
echo "<h1>Patient Issue Alert System Test</h1>";

try {
    // Check patient_alerts table structure
    echo "<h2>1. Checking patient_alerts table structure</h2>";
    
    // Check if table exists
    $table_exists = $db->query("SHOW TABLES LIKE 'patient_alerts'")->rowCount() > 0;
    
    if (!$table_exists) {
        echo "<div style='color: red;'>Error: patient_alerts table does not exist!</div>";
    } else {
        echo "<div style='color: green;'>Success: patient_alerts table exists</div>";
        
        // Check enum values
        $stmt = $db->query("SHOW COLUMNS FROM patient_alerts LIKE 'alert_type'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($column) {
            echo "<p>Current alert_type enum: <code>" . htmlspecialchars($column['Type']) . "</code></p>";
            
            if (strpos($column['Type'], 'patient_issue') !== false) {
                echo "<div style='color: green;'>Success: 'patient_issue' is in the enum</div>";
            } else {
                echo "<div style='color: red;'>Error: 'patient_issue' is NOT in the enum!</div>";
                echo "<p>Please run the update_patient_alerts.php script to add this value.</p>";
            }
        }
    }
    
    // Check alerts with patient_issue type
    echo "<h2>2. Checking existing patient_issue alerts</h2>";
    
    if ($table_exists) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM patient_alerts WHERE alert_type = 'patient_issue'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Number of patient_issue alerts: " . $result['count'] . "</p>";
        
        if ($result['count'] > 0) {
            echo "<h3>Sample patient_issue alerts:</h3>";
            $stmt = $db->query("SELECT * FROM patient_alerts WHERE alert_type = 'patient_issue' ORDER BY created_at DESC LIMIT 5");
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Patient ID</th><th>Title</th><th>Message</th><th>Reference ID</th><th>Created</th><th>Read</th></tr>";
            
            while ($alert = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $alert['id'] . "</td>";
                echo "<td>" . $alert['patient_id'] . "</td>";
                echo "<td>" . htmlspecialchars($alert['title']) . "</td>";
                echo "<td>" . htmlspecialchars($alert['message']) . "</td>";
                echo "<td>" . $alert['reference_id'] . "</td>";
                echo "<td>" . $alert['created_at'] . "</td>";
                echo "<td>" . ($alert['is_read'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
    // Test alerts view functionality
    echo "<h2>3. View Issue Details Link Test</h2>";
    echo "<p>To manually test:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='index.php?module=patient&action=alerts'>Patient Alerts Page</a></li>";
    echo "<li>Look for alerts with type 'patient_issue'</li>";
    echo "<li>Click 'View Details' button - it should take you to the correct issue page</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    echo "<p>If you encountered any issues above, please ensure you've run the following scripts:</p>";
    echo "<ol>";
    echo "<li><a href='update_columns.php'>update_columns.php</a> - To add patient_response and admin_response_at columns</li>";
    echo "<li><a href='update_patient_alerts.php'>update_patient_alerts.php</a> - To add patient_issue alert type to the enum</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
?> 