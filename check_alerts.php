<?php
// Connect to the database
$host = 'localhost';
$db   = 'palliative';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if patient_alerts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'patient_alerts'");
    $table_exists = ($stmt->rowCount() > 0);
    
    echo "patient_alerts table " . ($table_exists ? "exists" : "does not exist") . "<br>";
    
    if ($table_exists) {
        // Get alerts count
        $stmt = $pdo->query("SELECT COUNT(*) FROM patient_alerts");
        $count = $stmt->fetchColumn();
        
        echo "Total alerts: $count<br>";
        
        // Get recent alerts
        $stmt = $pdo->query("SELECT * FROM patient_alerts ORDER BY created_at DESC LIMIT 10");
        $alerts = $stmt->fetchAll();
        
        echo "<h3>Recent Alerts:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Patient ID</th><th>Title</th><th>Message</th><th>Alert Type</th><th>Reference ID</th><th>Read</th><th>Created At</th></tr>";
        
        foreach ($alerts as $alert) {
            echo "<tr>";
            echo "<td>" . $alert['id'] . "</td>";
            echo "<td>" . $alert['patient_id'] . "</td>";
            echo "<td>" . $alert['title'] . "</td>";
            echo "<td>" . $alert['message'] . "</td>";
            echo "<td>" . $alert['alert_type'] . "</td>";
            echo "<td>" . ($alert['reference_id'] ?? 'NULL') . "</td>";
            echo "<td>" . ($alert['is_read'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $alert['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Check for patients in the database
    $stmt = $pdo->query("SELECT id, name FROM patients LIMIT 5");
    $patients = $stmt->fetchAll();
    
    echo "<h3>Patients in the database:</h3>";
    echo "<ul>";
    foreach ($patients as $patient) {
        echo "<li>ID: " . $patient['id'] . " - Name: " . $patient['name'] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} 