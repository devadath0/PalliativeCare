<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Function to search diseases based on symptoms
function searchDiseasesBySymptoms($symptoms) {
    global $conn;
    
    // Convert symptoms array to string for LIKE query
    $symptomsStr = implode(',', $symptoms);
    
    // Query to find diseases based on symptoms
    $query = "SELECT DISTINCT d.*, GROUP_CONCAT(DISTINCT ds.specialization) as specializations
              FROM diseases d
              JOIN disease_symptoms ds ON d.id = ds.disease_id
              JOIN symptoms s ON ds.symptom_id = s.id
              WHERE s.name IN (" . str_repeat('?,', count($symptoms) - 1) . "?)
              GROUP BY d.id
              ORDER BY COUNT(DISTINCT ds.symptom_id) DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute($symptoms);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to recommend doctors based on disease specializations
function recommendDoctors($specializations) {
    global $conn;
    
    // Convert specializations array to string for LIKE query
    $specsStr = implode(',', $specializations);
    
    // Query to find doctors based on specializations
    $query = "SELECT DISTINCT d.*, u.name, u.email
              FROM doctors d
              JOIN users u ON d.user_id = u.id
              WHERE d.specialization IN (" . str_repeat('?,', count($specializations) - 1) . "?)
              AND d.availability_status = 'available'
              ORDER BY d.experience_years DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute($specializations);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $symptoms = isset($data['symptoms']) ? $data['symptoms'] : [];
    
    if (empty($symptoms)) {
        http_response_code(400);
        echo json_encode(['error' => 'No symptoms provided']);
        exit;
    }
    
    try {
        // Search for diseases based on symptoms
        $diseases = searchDiseasesBySymptoms($symptoms);
        
        // Get specializations from diseases
        $specializations = [];
        foreach ($diseases as $disease) {
            if (!empty($disease['specializations'])) {
                $specs = explode(',', $disease['specializations']);
                $specializations = array_merge($specializations, $specs);
            }
        }
        $specializations = array_unique($specializations);
        
        // Get recommended doctors
        $doctors = recommendDoctors($specializations);
        
        // Save search history
        $patient_id = getCurrentUserId();
        $symptomsStr = implode(',', $symptoms);
        $diseasesStr = json_encode(array_column($diseases, 'name'));
        $doctorsStr = json_encode(array_column($doctors, 'name'));
        
        $query = "INSERT INTO symptom_search_history (patient_id, symptoms, diseases_found, recommended_doctors)
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$patient_id, $symptomsStr, $diseasesStr, $doctorsStr]);
        
        // Return results
        echo json_encode([
            'diseases' => $diseases,
            'recommended_doctors' => $doctors
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 