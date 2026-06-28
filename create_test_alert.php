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
    
    if (!$table_exists) {
        // Create the patient_alerts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `patient_alerts` (
              `id` int NOT NULL AUTO_INCREMENT,
              `patient_id` int NOT NULL,
              `title` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `alert_type` enum('appointment','medicine_order','cab_booking','prescription','payment','system') NOT NULL DEFAULT 'system',
              `reference_id` int DEFAULT NULL,
              `is_read` tinyint(1) DEFAULT '0',
              `read_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `patient_id` (`patient_id`),
              KEY `idx_alert_type_reference` (`alert_type`,`reference_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");
        echo "Created patient_alerts table<br>";
    }
    
    // Get list of patients 
    $stmt = $pdo->query("SELECT id, name FROM patients LIMIT 1");
    $patient = $stmt->fetch();
    
    if (!$patient) {
        die("No patients found in the database");
    }
    
    $patient_id = $patient['id'];
    
    // Insert a test alert
    $stmt = $pdo->prepare("
        INSERT INTO patient_alerts (
            patient_id, title, message, alert_type, reference_id, is_read
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $patient_id,
        "Test Alert",
        "This is a test alert to check if alerts are functioning correctly.",
        'system',
        null,
        0
    ]);
    
    $alert_id = $pdo->lastInsertId();
    
    echo "Created test alert with ID: $alert_id for patient: {$patient['name']} (ID: $patient_id)<br>";
    echo "<a href='index.php?module=patient&action=dashboard'>Go to Patient Dashboard</a>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} 