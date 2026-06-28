<?php
// Include database configuration
require_once 'config/config.php';

try {
    // Create database connection
    $conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
    if ($stmt->rowCount() === 0) {
        // Add the hospital_id column
        $conn->exec('ALTER TABLE doctors ADD COLUMN hospital_id INT DEFAULT NULL');
        echo "hospital_id column added successfully.\n";
        
        // Add the index if it doesn't exist
        $conn->exec('ALTER TABLE doctors ADD INDEX hospital_id (hospital_id)');
        echo "hospital_id index added successfully.\n";
    } else {
        echo "hospital_id column already exists.\n";
    }
    
    echo "Database update completed successfully.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?> 