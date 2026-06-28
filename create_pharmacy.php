<?php
/**
 * Create Pharmacy Record Script
 * This script creates a pharmacy record for an existing service provider
 */

// Include database configuration
require_once 'config/config.php';
require_once 'classes/Database.php';

// Get database connection
$db = Database::getInstance();

// Check if the script is being run
echo "Starting pharmacy record creation script...\n";

try {
    // Start transaction
    $db->beginTransaction();
    
    // 1. Get service provider with type 'pharmacy'
    $stmt = $db->prepare("
        SELECT sp.*, u.id as user_id, u.email, u.name 
        FROM service_providers sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.service_type = 'pharmacy'
        AND NOT EXISTS (
            SELECT 1 FROM pharmacies p WHERE p.user_id = u.id
        )
    ");
    $stmt->execute();
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($providers)) {
        echo "No pharmacy service providers found without pharmacy records.\n";
        exit;
    }
    
    echo "Found " . count($providers) . " pharmacy service providers without pharmacy records.\n";
    
    // 2. Create pharmacy records for each provider
    foreach ($providers as $provider) {
        echo "Creating pharmacy record for: " . $provider['company_name'] . " (User ID: " . $provider['user_id'] . ")\n";
        
        $stmt = $db->prepare("
            INSERT INTO pharmacies (
                user_id, name, address, phone, email, license_number, status
            ) VALUES (
                :user_id, :name, :address, :phone, :email, :license_number, :status
            )
        ");
        
        $stmt->execute([
            ':user_id' => $provider['user_id'],
            ':name' => $provider['company_name'],
            ':address' => $provider['address'] ?? 'Address not provided',
            ':phone' => $provider['phone'] ?? 'Phone not provided',
            ':email' => $provider['email'],
            ':license_number' => $provider['license_number'] ?? 'LIC-' . rand(10000, 99999),
            ':status' => 'active'
        ]);
        
        $pharmacyId = $db->lastInsertId();
        echo "Created pharmacy record with ID: " . $pharmacyId . "\n";
    }
    
    // Commit transaction
    $db->commit();
    echo "All pharmacy records created successfully!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
} 