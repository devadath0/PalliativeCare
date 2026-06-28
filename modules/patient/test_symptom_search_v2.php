<?php
/**
 * Test Script for Symptom Search API (Version 2)
 * This file tests the symptom search API without requiring authentication
 * Includes improved doctor recommendation logic
 */

// Set content type to JSON
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'palliative';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if required tables exist
    $tables_to_check = ['symptoms', 'diseases', 'disease_symptoms', 'disease_specializations', 'doctors'];
    $missing_tables = [];
    
    foreach ($tables_to_check as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing tables: ' . implode(', ', $missing_tables),
            'tables_to_create' => $missing_tables
        ]);
        exit;
    }
    
    // Get all symptoms for testing
    $stmt = $conn->prepare("SELECT name FROM symptoms LIMIT 10");
    $stmt->execute();
    $symptoms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($symptoms)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No symptoms found in the database'
        ]);
        exit;
    }
    
    // Test with the first three symptoms
    $test_symptoms = array_slice($symptoms, 0, 3);
    
    // Get symptom IDs
    $symptomIds = [];
    foreach ($test_symptoms as $symptomName) {
        $stmt = $conn->prepare("SELECT id FROM symptoms WHERE name = ?");
        $stmt->execute([$symptomName]);
        $id = $stmt->fetchColumn();
        if ($id) {
            $symptomIds[] = $id;
        }
    }
    
    if (empty($symptomIds)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No symptom IDs found for the test symptoms'
        ]);
        exit;
    }
    
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($symptomIds), '?'));
    
    // Search for diseases based on symptom IDs
    $stmt = $conn->prepare("
        SELECT DISTINCT d.*
        FROM diseases d
        JOIN disease_symptoms ds ON d.id = ds.disease_id
        WHERE ds.symptom_id IN ($placeholders)
        GROUP BY d.id
        ORDER BY COUNT(DISTINCT ds.symptom_id) DESC
    ");
    $stmt->execute($symptomIds);
    $diseases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get specializations for each disease
    $specializations = [];
    foreach ($diseases as $key => $disease) {
        $stmt = $conn->prepare("
            SELECT specialization 
            FROM disease_specializations 
            WHERE disease_id = ?
        ");
        $stmt->execute([$disease['id']]);
        $specs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $diseases[$key]['specializations'] = $specs;
        $specializations = array_merge($specializations, $specs);
    }
    $specializations = array_unique($specializations);
    
    // Get recommended doctors with improved logic
    $doctors = [];
    if (!empty($specializations)) {
        try {
            // Create placeholders for the IN clause
            $specPlaceholders = implode(',', array_fill(0, count($specializations), '?'));
            
            // Prepare the parameters array with all specializations
            $params = $specializations;
            
            // Log the query and parameters for debugging
            error_log("Test Doctor query: SELECT DISTINCT d.*, u.name, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.specialization IN ($specPlaceholders) AND d.availability_status = 'available' ORDER BY d.experience_years DESC");
            error_log("Test Doctor params: " . implode(', ', $params));
            
            // First check if any doctors match these specializations exactly
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) FROM doctors 
                WHERE specialization IN ($specPlaceholders)
            ");
            $checkStmt->execute($params);
            $doctorCount = $checkStmt->fetchColumn();
            
            if ($doctorCount > 0) {
                $stmt = $conn->prepare("
                    SELECT DISTINCT d.*, u.name, u.email
                    FROM doctors d
                    JOIN users u ON d.user_id = u.id
                    WHERE d.specialization IN ($specPlaceholders)
                    AND d.availability_status = 'available'
                    ORDER BY d.experience_years DESC
                ");
                $stmt->execute($params);
                $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // If no exact matches, try partial matches using LIKE
                $likeParams = [];
                $likeConditions = [];
                
                foreach ($specializations as $spec) {
                    $likeConditions[] = "d.specialization LIKE ?";
                    $likeParams[] = "%$spec%";
                }
                
                $likeQuery = implode(' OR ', $likeConditions);
                
                $stmt = $conn->prepare("
                    SELECT DISTINCT d.*, u.name, u.email
                    FROM doctors d
                    JOIN users u ON d.user_id = u.id
                    WHERE ($likeQuery)
                    AND d.availability_status = 'available'
                    ORDER BY d.experience_years DESC
                ");
                $stmt->execute($likeParams);
                $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // If still no doctors, get general practitioners
                if (empty($doctors)) {
                    $stmt = $conn->prepare("
                        SELECT DISTINCT d.*, u.name, u.email
                        FROM doctors d
                        JOIN users u ON d.user_id = u.id
                        WHERE (d.specialization = 'General Practitioner' OR d.specialization = 'Family Medicine')
                        AND d.availability_status = 'available'
                        ORDER BY d.experience_years DESC
                        LIMIT 3
                    ");
                    $stmt->execute();
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // If still no doctors, get any available doctors
                    if (empty($doctors)) {
                        $stmt = $conn->prepare("
                            SELECT DISTINCT d.*, u.name, u.email
                            FROM doctors d
                            JOIN users u ON d.user_id = u.id
                            WHERE d.availability_status = 'available'
                            ORDER BY d.experience_years DESC
                            LIMIT 3
                        ");
                        $stmt->execute();
                        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error in test doctor query: " . $e->getMessage());
            // Continue with empty doctors array
            $doctors = [];
        }
    } else {
        // If no specializations found, get general practitioners
        try {
            $stmt = $conn->prepare("
                SELECT DISTINCT d.*, u.name, u.email
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                WHERE (d.specialization = 'General Practitioner' OR d.specialization = 'Family Medicine')
                AND d.availability_status = 'available'
                ORDER BY d.experience_years DESC
                LIMIT 3
            ");
            $stmt->execute();
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no general practitioners, get any available doctors
            if (empty($doctors)) {
                $stmt = $conn->prepare("
                    SELECT DISTINCT d.*, u.name, u.email
                    FROM doctors d
                    JOIN users u ON d.user_id = u.id
                    WHERE d.availability_status = 'available'
                    ORDER BY d.experience_years DESC
                    LIMIT 3
                ");
                $stmt->execute();
                $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error in fallback test doctor query: " . $e->getMessage());
            // Continue with empty doctors array
        }
    }
    
    // Return test results
    echo json_encode([
        'status' => 'success',
        'test_symptoms' => $test_symptoms,
        'symptom_ids' => $symptomIds,
        'diseases_found' => $diseases,
        'specializations' => $specializations,
        'recommended_doctors' => $doctors
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'General error: ' . $e->getMessage()
    ]);
}
?> 