<?php
// Database configuration
require_once 'config/database.php';

try {
    // Get the structure of the patient_issues table
    $stmt = $db->query("DESCRIBE patient_issues");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Patient Issues Table Structure</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] === null ? 'NULL' : $column['Default']) . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Also check if the table has any data
    $stmt = $db->query("SELECT COUNT(*) as count FROM patient_issues");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p>Total records in patient_issues table: " . $count . "</p>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 