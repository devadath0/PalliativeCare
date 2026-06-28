<?php
// Set content type to JSON
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$dbname = "palliative";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if required tables exist
    $requiredTables = ["symptoms", "diseases", "disease_symptoms", "disease_specializations", "doctors"];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo json_encode([
            "status" => "error",
            "message" => "Required tables missing: " . implode(", ", $missingTables)
        ]);
        exit;
    }
    
    // Get symptoms from request
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data["symptoms"]) || empty($data["symptoms"])) {
        echo json_encode([
            "status" => "error",
            "message" => "No symptoms provided"
        ]);
        exit;
    }
    
    $symptoms = $data["symptoms"];
    $patientId = isset($data["patient_id"]) ? $data["patient_id"] : 1; // Default to 1 if not provided
    
    // Get symptom IDs from database
    $placeholders = implode(",", array_fill(0, count($symptoms), "?"));
    $stmt = $conn->prepare("
        SELECT id, name 
        FROM symptoms 
        WHERE name IN ($placeholders)
    ");
    $stmt->execute($symptoms);
    $symptomData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($symptomData)) {
        echo json_encode([
            "status" => "error",
            "message" => "No matching symptoms found in database"
        ]);
        exit;
    }
    
    // Extract symptom IDs
    $symptomIds = array_column($symptomData, "id");
    
    // Find diseases that match these symptoms
    $placeholders = implode(",", array_fill(0, count($symptomIds), "?"));
    $stmt = $conn->prepare("
        SELECT d.id, d.name, d.description, d.treatment, d.severity_level, COUNT(ds.symptom_id) as matching_symptoms
        FROM diseases d
        JOIN disease_symptoms ds ON d.id = ds.disease_id
        WHERE ds.symptom_id IN ($placeholders)
        GROUP BY d.id
        ORDER BY matching_symptoms DESC, d.severity_level DESC
    ");
    $stmt->execute($symptomIds);
    $diseases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($diseases)) {
        echo json_encode([
            "status" => "success",
            "test_symptoms" => $symptoms,
            "symptom_ids" => $symptomIds,
            "diseases" => [],
            "specializations" => [],
            "recommended_doctors" => []
        ]);
        exit;
    }
    
    // Get disease IDs
    $diseaseIds = array_column($diseases, "id");
    
    // Get specializations for these diseases
    $placeholders = implode(",", array_fill(0, count($diseaseIds), "?"));
    $stmt = $conn->prepare("
        SELECT DISTINCT ds.specialization
        FROM disease_specializations ds
        WHERE ds.disease_id IN ($placeholders)
    ");
    $stmt->execute($diseaseIds);
    $specializations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Find doctors based on specializations
    $recommendedDoctors = [];
    
    if (!empty($specializations)) {
        // Try exact match first
        $placeholders = implode(",", array_fill(0, count($specializations), "?"));
        $stmt = $conn->prepare("
            SELECT d.id, d.name, d.specialization, d.experience_years, d.consultation_fee
            FROM doctors d
            WHERE d.specialization IN ($placeholders)
            AND d.availability_status = 'available'
            ORDER BY d.experience_years DESC
            LIMIT 5
        ");
        $stmt->execute($specializations);
        $recommendedDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no exact matches, try partial match using LIKE
        if (empty($recommendedDoctors)) {
            $likeQueries = [];
            $likeParams = [];
            
            foreach ($specializations as $spec) {
                $likeQueries[] = "d.specialization LIKE ?";
                $likeParams[] = "%" . $spec . "%";
            }
            
            $likeClause = implode(" OR ", $likeQueries);
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.specialization, d.experience_years, d.consultation_fee
                FROM doctors d
                WHERE ($likeClause)
                AND d.availability_status = 'available'
                ORDER BY d.experience_years DESC
                LIMIT 5
            ");
            $stmt->execute($likeParams);
            $recommendedDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // If still no matches, try general practitioners or family medicine
        if (empty($recommendedDoctors)) {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.specialization, d.experience_years, d.consultation_fee
                FROM doctors d
                WHERE (d.specialization = 'General Practice' OR d.specialization = 'Family Medicine')
                AND d.availability_status = 'available'
                ORDER BY d.experience_years DESC
                LIMIT 3
            ");
            $stmt->execute();
            $recommendedDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // If still no matches, get any available doctors
        if (empty($recommendedDoctors)) {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.specialization, d.experience_years, d.consultation_fee
                FROM doctors d
                WHERE d.availability_status = 'available'
                ORDER BY d.experience_years DESC
                LIMIT 3
            ");
            $stmt->execute();
            $recommendedDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Save search history
    $stmt = $conn->prepare("
        INSERT INTO symptom_search_history (patient_id, symptoms, diseases_found, recommended_doctors)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $patientId,
        implode(",", $symptoms),
        json_encode(array_column($diseases, "name")),
        json_encode(array_column($recommendedDoctors, "name"))
    ]);
    
    // Return response
    echo json_encode([
        "status" => "success",
        "test_symptoms" => $symptoms,
        "symptom_ids" => $symptomIds,
        "diseases" => $diseases,
        "specializations" => $specializations,
        "recommended_doctors" => $recommendedDoctors
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>