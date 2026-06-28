<?php
/**
 * Doctor Controller
 * Handles doctor-related functionality
 */
class DoctorController extends BaseController {
    private $doctorId;
    
    public function __construct() {
        // Initialize database connection
        parent::__construct();
        
        // Get doctor ID from the database based on user ID
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT id FROM doctors WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($doctor) {
                $this->doctorId = $doctor['id'];
            }
        }
        
        // Check if user is a doctor
        if (!$this->hasRole('doctor')) {
            $this->setFlash('error', 'You do not have permission to access this page.');
            $this->redirect('index.php?module=auth&action=login&type=doctor');
        }
    }
    
    /**
     * Display doctor dashboard
     */
    public function dashboard() {
        try {
            error_log("Doctor dashboard method called. Doctor ID: " . $this->doctorId);
            
            // Check if doctorId is set
            if (!$this->doctorId) {
                error_log("Doctor ID is not set. User ID: " . ($_SESSION['user_id'] ?? 'not set'));
                throw new Exception("Doctor record not found. Please contact the administrator.");
            }
            
            // Get doctor information
            $stmt = $this->db->prepare("
                SELECT d.*, u.email, u.name as doctor_name
                FROM doctors d
                INNER JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$this->doctorId]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Doctor data: " . print_r($doctor, true));
            
            if (!$doctor) {
                error_log("Doctor record not found for ID: " . $this->doctorId);
                throw new Exception("Doctor record not found");
            }
            
            // Get appointment statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN status = 'scheduled' OR status = 'pending' THEN 1 ELSE 0 END) as pending_appointments,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_appointments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                    SUM(CASE WHEN DATE(appointment_date) = CURDATE() AND status != 'cancelled' THEN 1 ELSE 0 END) as today_appointments
                FROM appointments
                WHERE doctor_id = ?
            ");
            $stmt->execute([$this->doctorId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Appointment stats: " . print_r($stats, true));
            
            // Get prescription statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_prescriptions,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_prescriptions,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_prescriptions
                FROM prescriptions
                WHERE doctor_id = ?
            ");
            $stmt->execute([$this->doctorId]);
            $prescription_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Prescription stats: " . print_r($prescription_stats, true));
            
            // Get today's appointments
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.name as patient_name,
                       p.phone as patient_phone
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id
                WHERE a.doctor_id = ?
                AND DATE(a.appointment_date) = CURDATE()
                AND a.status != 'cancelled'
                ORDER BY a.appointment_date
            ");
            $stmt->execute([$this->doctorId]);
            $today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Today's appointments count: " . count($today_appointments));
            
            // Add first_name and last_name for each patient in today's appointments
            foreach ($today_appointments as &$appointment) {
                if (isset($appointment['patient_name'])) {
                    $nameParts = explode(' ', $appointment['patient_name'], 2);
                    $appointment['patient_first_name'] = $nameParts[0];
                    $appointment['patient_last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
                }
            }
            
            // Get pending appointments
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.name as patient_name
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id
                WHERE a.doctor_id = ?
                AND (a.status = 'scheduled' OR a.status = 'pending')
                ORDER BY a.appointment_date
                LIMIT 5
            ");
            $stmt->execute([$this->doctorId]);
            $pending_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Pending appointments count: " . count($pending_appointments));
            
            // Add first_name and last_name for each patient in pending appointments
            foreach ($pending_appointments as &$appointment) {
                if (isset($appointment['patient_name'])) {
                    $nameParts = explode(' ', $appointment['patient_name'], 2);
                    $appointment['patient_first_name'] = $nameParts[0];
                    $appointment['patient_last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
                }
            }
            
            // Get recent prescriptions
            $stmt = $this->db->prepare("
                SELECT pr.*, 
                       p.name as patient_name,
                       pi.medicine as medication,
                       pi.dosage
                FROM prescriptions pr
                INNER JOIN patients p ON pr.patient_id = p.id
                LEFT JOIN prescription_items pi ON pi.prescription_id = pr.id
                WHERE pr.doctor_id = ?
                ORDER BY pr.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$this->doctorId]);
            $recent_prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Recent prescriptions count: " . count($recent_prescriptions));
            
            // Add first_name and last_name for each patient in recent prescriptions
            foreach ($recent_prescriptions as &$prescription) {
                if (isset($prescription['patient_name'])) {
                    $nameParts = explode(' ', $prescription['patient_name'], 2);
                    $prescription['patient_first_name'] = $nameParts[0];
                    $prescription['patient_last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
                }
            }
            
            // Prepare data for the view
            $data = [
                'page_title' => 'Doctor Dashboard',
                'doctor' => $doctor,
                'stats' => $stats,
                'prescription_stats' => $prescription_stats,
                'today_appointments' => $today_appointments,
                'pending_appointments' => $pending_appointments,
                'recent_prescriptions' => $recent_prescriptions
            ];
            
            error_log("Rendering dashboard with data: " . print_r($data, true));
            
            // Render the dashboard view
            $this->render('dashboard', $data);
            
        } catch (Exception $e) {
            error_log("Error in doctor dashboard: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->setFlash('error', 'An error occurred while loading the dashboard: ' . $e->getMessage());
            $this->render('dashboard', ['page_title' => 'Doctor Dashboard', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Display and manage appointments
     */
    public function appointments() {
        try {
            $filter = htmlspecialchars($_GET['filter'] ?? '', ENT_QUOTES, 'UTF-8');
            $date_filter = htmlspecialchars($_GET['date'] ?? '', ENT_QUOTES, 'UTF-8');
            
            // Check if the doctor has a hospital assigned
            $stmt = $this->db->prepare("SELECT hospital_id FROM doctors WHERE id = ?");
            $stmt->execute([$this->doctorId]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            $hospital_id = $doctor['hospital_id'] ?? null;
            
            // Base query
            $query = "
                SELECT a.*, 
                    p.name AS patient_first_name, '' AS patient_last_name, 
                    p.phone AS patient_phone, p.id AS patient_id,
                    h.name AS hospital_name 
                FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                LEFT JOIN hospitals h ON h.id = ?
                WHERE a.doctor_id = ? 
            ";
            
            $params = [$hospital_id, $this->doctorId];
            
            // Add date filter if provided
            if ($date_filter === 'today') {
                $query .= "AND DATE(a.appointment_date) = CURDATE() ";
            } elseif ($date_filter === 'upcoming') {
                $query .= "AND a.appointment_date > NOW() ";
            } elseif ($date_filter === 'past') {
                $query .= "AND a.appointment_date < NOW() ";
            }
            
            // Add order by
            $query .= "ORDER BY a.appointment_date ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group appointments by status
            $grouped_appointments = [
                'scheduled' => [],
                'confirmed' => [],
                'completed' => [],
                'cancelled' => []
            ];
            
            // Get unread message counts for confirmed appointments
            $unread_counts = [];
            $stmt = $this->db->prepare("
                SELECT appointment_id, COUNT(*) as count
                FROM chat_messages
                WHERE appointment_id IN (
                    SELECT id FROM appointments 
                    WHERE doctor_id = ? AND status = 'confirmed'
                )
                AND sender_type = 'patient'
                AND is_read = 0
                GROUP BY appointment_id
            ");
            $stmt->execute([$this->doctorId]);
            $unread_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($unread_results as $result) {
                $unread_counts[$result['appointment_id']] = $result['count'];
            }
            
            foreach ($appointments as $appointment) {
                // Add unread count to the appointment data
                $appointment['unread_count'] = $unread_counts[$appointment['id']] ?? 0;
                
                if (isset($appointment['status'])) {
                    if ($appointment['status'] === 'pending') {
                        $grouped_appointments['scheduled'][] = $appointment;
                    } else {
                        $grouped_appointments[$appointment['status']][] = $appointment;
                    }
                }
            }
            
            // Pass the database connection to the view
            $db = $this->db;
            
            include(__DIR__ . '/views/appointments.php');
        } catch (Exception $e) {
            $this->logError("Error fetching appointments: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred while fetching appointments.');
            $this->redirect('index.php?module=doctor&action=dashboard');
        }
    }
    
    /**
     * Update appointment status
     */
    public function update_appointment_status() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            // Debug logging
            error_log("Updating appointment status - ID: {$appointment_id}, Status: {$status}");
            error_log("POST data: " . print_r($_POST, true));
            
            if (!$appointment_id || !$status) {
                throw new Exception("Missing required parameters");
            }
            
            // Validate status
            $valid_statuses = ['scheduled', 'pending', 'confirmed', 'completed', 'cancelled'];
            if (!in_array($status, $valid_statuses)) {
                throw new Exception("Invalid status value");
            }
            
            // Check if appointment belongs to this doctor
            $stmt = $this->db->prepare("SELECT * FROM appointments WHERE id = ? AND doctor_id = ?");
            $stmt->execute([$appointment_id, $this->doctorId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found or access denied");
            }
            
            // Update appointment status
            $stmt = $this->db->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $appointment_id]);
            
            // Get patient information for alerts
            $stmt = $this->db->prepare("SELECT patient_id FROM appointments WHERE id = ?");
            $stmt->execute([$appointment_id]);
            $patient_id = $stmt->fetchColumn();
            
            // Create patient alert for appointment status update
            if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
                try {
                    // Check if patient_alerts table exists
                    $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                    $patient_alerts_exists = ($stmt->rowCount() > 0);
                    
                    // Create table if it doesn't exist
                    if (!$patient_alerts_exists) {
                        $this->db->exec("
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
                    }
                    
                    // Get appointment and doctor details
                    $stmt = $this->db->prepare("
                        SELECT a.appointment_date, d.name as doctor_name, d.specialization
                        FROM appointments a
                        JOIN doctors d ON a.doctor_id = d.id
                        WHERE a.id = ?
                    ");
                    $stmt->execute([$appointment_id]);
                    $appointmentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Format appointment date
                    $appointmentDate = date('F j, Y \a\t g:i a', strtotime($appointmentInfo['appointment_date']));
                    
                    // Set alert message based on status
                    $alertTitle = '';
                    $alertMessage = '';
                    
                    if ($status === 'confirmed') {
                        $alertTitle = "Appointment Confirmed";
                        $alertMessage = "Your appointment with Dr. {$appointmentInfo['doctor_name']} ({$appointmentInfo['specialization']}) on {$appointmentDate} has been confirmed.";
                    } elseif ($status === 'cancelled') {
                        $alertTitle = "Appointment Cancelled";
                        $alertMessage = "Your appointment with Dr. {$appointmentInfo['doctor_name']} ({$appointmentInfo['specialization']}) on {$appointmentDate} has been cancelled.";
                    } elseif ($status === 'completed') {
                        $alertTitle = "Appointment Completed";
                        $alertMessage = "Your appointment with Dr. {$appointmentInfo['doctor_name']} ({$appointmentInfo['specialization']}) on {$appointmentDate} has been marked as completed.";
                    }
                    
                    // Create the alert
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'appointment', ?, 0)
                    ");
                    $stmt->execute([
                        $patient_id,
                        $alertTitle,
                        $alertMessage,
                        $appointment_id
                    ]);
                    
                    error_log("Created patient alert for appointment ID {$appointment_id}, status: {$status}");
                } catch (PDOException $e) {
                    error_log("Error creating patient alert: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
            }
            
            // Send email notification if appointment is confirmed, cancelled, or completed
            if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
                // Check if hospital_id column exists in doctors table
                $columnExists = false;
                try {
                    $stmt = $this->db->prepare("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
                    $stmt->execute();
                    $columnExists = ($stmt->rowCount() > 0);
                } catch (PDOException $e) {
                    error_log("Error checking hospital_id column: " . $e->getMessage());
                }
                
                // Get patient, doctor, and hospital details for email
                $query = "
                    SELECT a.*, p.name as patient_name, p.email as patient_email,
                           d.name as doctor_name, d.specialization";
                
                if ($columnExists) {
                    $query .= ", d.hospital_id,
                           h.name as hospital_name, h.address as hospital_address";
                }
                
                $query .= "
                    FROM appointments a
                    INNER JOIN patients p ON a.patient_id = p.id
                    INNER JOIN doctors d ON a.doctor_id = d.id";
                
                if ($columnExists) {
                    $query .= "
                    LEFT JOIN hospitals h ON d.hospital_id = h.id";
                }
                
                $query .= "
                    WHERE a.id = ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute([$appointment_id]);
                $appointmentData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($appointmentData) {
                    $emailService = new EmailService();
                    $appointmentData['date'] = date('F j, Y', strtotime($appointmentData['appointment_date']));
                    $appointmentData['time'] = date('h:i A', strtotime($appointmentData['appointment_date']));
                    
                    // Set location based on hospital info
                    $appointmentData['location'] = isset($appointmentData['hospital_name']) && $appointmentData['hospital_name'] ? 
                        $appointmentData['hospital_name'] . ', ' . $appointmentData['hospital_address'] : 
                        'Location not specified';
                    
                    if ($status === 'confirmed') {
                        $emailService->sendAppointmentConfirmation(
                            $appointmentData['patient_email'],
                            $appointmentData['patient_name'],
                            $appointmentData
                        );
                    } else {
                        $emailService->sendAppointmentStatusUpdate(
                            $appointmentData['patient_email'],
                            $appointmentData['patient_name'],
                            $appointmentData,
                            $status
                        );
                    }
                }
            }
            
            // Set appropriate success message based on the status
            $message = 'Appointment status updated successfully';
            if ($status === 'confirmed') {
                $message = 'Appointment confirmed successfully';
            } elseif ($status === 'completed') {
                $message = 'Appointment marked as completed';
            } elseif ($status === 'cancelled') {
                $message = 'Appointment rejected successfully';
            }
            
            $this->setFlash('success', $message);
            
            // Redirect back to the referring page or to appointments page
            if (isset($_SERVER['HTTP_REFERER'])) {
                // Extract the module and action from the referer URL
                $referer = $_SERVER['HTTP_REFERER'];
                $parsed_url = parse_url($referer, PHP_URL_QUERY);
                parse_str($parsed_url, $query_params);
                
                // Check if there's a fragment/anchor in the URL
                $fragment = '';
                if (strpos($referer, '#') !== false) {
                    $parts = explode('#', $referer);
                    if (count($parts) > 1) {
                        $fragment = '#' . $parts[1];
                    }
                }
                
                // Determine where to redirect
                if (isset($query_params['module']) && $query_params['module'] === 'doctor') {
                    if (isset($query_params['action']) && $query_params['action'] === 'dashboard') {
                        $this->redirect('index.php?module=doctor&action=dashboard' . $fragment);
                    } else {
                        $this->redirect($referer);
                    }
                } else {
                    $this->redirect('index.php?module=doctor&action=appointments');
                }
            } else {
                $this->redirect('index.php?module=doctor&action=appointments');
            }
            
        } catch (Exception $e) {
            $this->logError("Error updating appointment status: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=appointments');
        }
    }
    
    /**
     * View patient details
     */
    public function view_patient() {
        try {
            $patient_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$patient_id) {
                throw new Exception("Patient ID is required");
            }
            
            // Get patient information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email
                FROM patients p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                throw new Exception("Patient not found");
            }
            
            // Get patient's appointments with this doctor
            $stmt = $this->db->prepare("
                SELECT * FROM appointments
                WHERE patient_id = ? AND doctor_id = ?
                ORDER BY appointment_date DESC
            ");
            $stmt->execute([$patient_id, $this->doctorId]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get patient's prescriptions from this doctor
            $stmt = $this->db->prepare("
                SELECT * FROM prescriptions
                WHERE patient_id = ? AND doctor_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$patient_id, $this->doctorId]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Initialize medical_history as an empty array
            $medical_history = [];
            
            // Check if medical_history table exists before querying it
            try {
                $checkTableStmt = $this->db->prepare("
                    SELECT 1 FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'medical_history'
                ");
                $checkTableStmt->execute();
                
                if ($checkTableStmt->fetchColumn()) {
                    // Table exists, get patient's medical history
                    $stmt = $this->db->prepare("
                        SELECT * FROM medical_history
                        WHERE patient_id = ?
                        ORDER BY recorded_date DESC
                    ");
                    $stmt->execute([$patient_id]);
                    $medical_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $this->logError("medical_history table does not exist in the database");
                }
            } catch (Exception $e) {
                $this->logError("Error checking medical_history table: " . $e->getMessage());
                // Continue execution, just with an empty medical_history array
            }
            
            // Prepare data for the view
            $data = [
                'page_title' => 'Patient Details',
                'patient' => $patient,
                'appointments' => $appointments,
                'prescriptions' => $prescriptions,
                'medical_history' => $medical_history
            ];
            
            $this->logError("Patient data: " . json_encode($patient));
            $this->logError("Appointments data: " . json_encode($appointments));
            $this->logError("Prescriptions data: " . json_encode($prescriptions));
            
            // Render the view_patient view
            $this->render('view_patient', $data);
            
        } catch (Exception $e) {
            $this->logError("Error viewing patient: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=dashboard');
        }
    }
    
    /**
     * Create a new prescription
     */
    public function create_prescription() {
        try {
            // If form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_SANITIZE_NUMBER_INT);
                $medication = filter_input(INPUT_POST, 'medication', FILTER_SANITIZE_STRING);
                $dosage = filter_input(INPUT_POST, 'dosage', FILTER_SANITIZE_STRING);
                $frequency = filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING);
                $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_STRING);
                $instructions = filter_input(INPUT_POST, 'instructions', FILTER_SANITIZE_STRING);
                
                // Validate required fields
                if (!$patient_id || !$medication || !$dosage || !$frequency || !$duration) {
                    throw new Exception("All fields are required");
                }
                
                // Check if patient exists
                $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->execute([$patient_id]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$patient) {
                    throw new Exception("Patient not found");
                }
                
                // Format the diagnosis field with medication, dosage, frequency, and duration
                $diagnosis = "Medication: $medication\nDosage: $dosage\nFrequency: $frequency\nDuration: $duration";
                
                // Insert prescription
                $stmt = $this->db->prepare("
                    INSERT INTO prescriptions (
                        patient_id, doctor_id, diagnosis, notes, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $patient_id, $this->doctorId, $diagnosis, $instructions
                ]);
                
                $prescription_id = $this->db->lastInsertId();
                
                // Create patient alert for new prescription
                try {
                    // Check if patient_alerts table exists
                    $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                    $patient_alerts_exists = ($stmt->rowCount() > 0);
                    
                    // Create table if it doesn't exist
                    if (!$patient_alerts_exists) {
                        $this->db->exec("
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
                    }
                    
                    // Get doctor details
                    $stmt = $this->db->prepare("SELECT name, specialization FROM doctors WHERE id = ?");
                    $stmt->execute([$this->doctorId]);
                    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Create the alert
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'prescription', ?, 0)
                    ");
                    $stmt->execute([
                        $patient_id,
                        "New Prescription Added",
                        "Dr. {$doctor['name']} ({$doctor['specialization']}) has added a new prescription for you: {$medication}. Please check your prescriptions for details.",
                        $prescription_id
                    ]);
                    
                    error_log("Created patient alert for prescription ID {$prescription_id}");
                } catch (PDOException $e) {
                    error_log("Error creating patient alert for prescription: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
                
                $this->setFlash('success', 'Prescription created successfully');
                $this->redirect('index.php?module=doctor&action=view_patient&id=' . $patient_id);
                
            } else {
                // Get patient ID from query string if provided
                $patient_id = filter_input(INPUT_GET, 'patient_id', FILTER_SANITIZE_NUMBER_INT);
                
                // Get list of patients for dropdown
                $stmt = $this->db->prepare("
                    SELECT p.id, p.name
                    FROM patients p
                    INNER JOIN appointments a ON p.id = a.patient_id
                    WHERE a.doctor_id = ?
                    GROUP BY p.id
                    ORDER BY p.name
                ");
                $stmt->execute([$this->doctorId]);
                $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get patient details if patient_id is provided
                $patient = null;
                if ($patient_id) {
                    $stmt = $this->db->prepare("
                        SELECT p.*, u.email
                        FROM patients p
                        INNER JOIN users u ON p.user_id = u.id
                        WHERE p.id = ?
                    ");
                    $stmt->execute([$patient_id]);
                    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$patient) {
                        throw new Exception("Patient not found");
                    }
                }
                
                // Prepare data for the view
                $data = [
                    'page_title' => 'Create Prescription',
                    'patients' => $patients,
                    'patient' => $patient
                ];
                
                // Render the create_prescription view
                $this->render('create_prescription', $data);
            }
        } catch (Exception $e) {
            $this->logError("Error creating prescription: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=dashboard');
        }
    }
    
    /**
     * View and edit doctor profile
     */
    public function profile() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->update_profile();
                return;
            }
            
            // Check if hospital_id column exists in doctors table
            $columnExists = false;
            try {
                $stmt = $this->db->prepare("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
                $stmt->execute();
                $columnExists = ($stmt->rowCount() > 0);
            } catch (PDOException $e) {
                error_log("Error checking hospital_id column: " . $e->getMessage());
            }
            
            // Get doctor information including hospital data
            $query = "SELECT d.*";
            
            if ($columnExists) {
                $query .= ", h.name as hospital_name, h.address as hospital_address";
            }
            
            $query .= " FROM doctors d";
            
            if ($columnExists) {
                $query .= " LEFT JOIN hospitals h ON d.hospital_id = h.id";
            }
            
            $query .= " WHERE d.id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->doctorId]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doctor) {
                $this->setFlash('error', 'Doctor profile not found');
                $this->redirect('index.php?module=doctor&action=dashboard');
                return;
            }
            
            // Get appointment statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_appointments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                    SUM(CASE WHEN status = 'confirmed' OR status = 'pending' OR status = 'scheduled' THEN 1 ELSE 0 END) as upcoming_appointments
                FROM appointments
                WHERE doctor_id = ?
            ");
            $stmt->execute([$this->doctorId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get today's appointments
            $stmt = $this->db->prepare("
                SELECT a.*, p.name as patient_name
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id
                WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
                ORDER BY a.appointment_date ASC
                LIMIT 5
            ");
            $stmt->execute([$this->doctorId]);
            $today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('profile', [
                'page_title' => 'Doctor Profile',
                'doctor' => $doctor,
                'stats' => $stats,
                'today_appointments' => $today_appointments,
                'hospital_name' => $doctor['hospital_name'] ?? null
            ]);
        } catch (Exception $e) {
            $this->logError("Error viewing profile: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=dashboard');
        }
    }
    
    /**
     * Update doctor profile
     */
    public function update_profile() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            // Get and validate form data
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
            $specialization = filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_STRING);
            $license_number = filter_input(INPUT_POST, 'license_number', FILTER_SANITIZE_STRING);
            $qualification = filter_input(INPUT_POST, 'qualification', FILTER_SANITIZE_STRING);
            $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
            $hospital_id = filter_input(INPUT_POST, 'hospital_id', FILTER_SANITIZE_NUMBER_INT);
            $experience_years = filter_input(INPUT_POST, 'experience_years', FILTER_SANITIZE_NUMBER_INT);
            $consultation_fee = filter_input(INPUT_POST, 'consultation_fee', FILTER_SANITIZE_STRING);
            
            if (!$name || !$email) {
                throw new Exception("Name and email are required");
            }
            
            // Check if hospital_id column exists in doctors table
            $columnExists = false;
            try {
                $stmt = $this->db->prepare("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
                $stmt->execute();
                $columnExists = ($stmt->rowCount() > 0);
                
                // If column doesn't exist, add it
                if (!$columnExists) {
                    $this->db->exec("ALTER TABLE doctors ADD COLUMN hospital_id INT DEFAULT NULL");
                    $this->db->exec("ALTER TABLE doctors ADD INDEX hospital_id (hospital_id)");
                    error_log("Added hospital_id column to doctors table in update_profile");
                    $columnExists = true;
                }
            } catch (PDOException $e) {
                error_log("Error checking/adding hospital_id column: " . $e->getMessage());
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update doctor details
            $sql = "
                UPDATE doctors SET
                    name = ?,
                    phone = ?,
                    specialization = ?,
                    qualification = ?,
                    experience_years = ?,
                    consultation_fee = ?,
                    license_number = ?";
                    
            if ($columnExists) {
                $sql .= ",
                    hospital_id = ?";
            }
            
            $sql .= ",
                    updated_at = NOW()
                WHERE id = ?";
                
            $params = [
                $name,
                $phone,
                $specialization,
                $qualification,
                $experience_years,
                $consultation_fee,
                $license_number
            ];
            
            if ($columnExists) {
                $params[] = !empty($hospital_id) ? $hospital_id : null;
            }
            
            $params[] = $this->doctorId;
            
            // Update doctor information
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Update email in users table
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email = ? 
                WHERE id = (SELECT user_id FROM doctors WHERE id = ?)
            ");
            $stmt->execute([$email, $this->doctorId]);
            
            // Check if password change is requested
            $current_password = filter_input(INPUT_POST, 'current_password');
            $new_password = filter_input(INPUT_POST, 'new_password');
            $confirm_password = filter_input(INPUT_POST, 'confirm_password');
            
            if ($current_password && $new_password && $confirm_password) {
                // Get user ID
                $stmt = $this->db->prepare("SELECT user_id FROM doctors WHERE id = ?");
                $stmt->execute([$this->doctorId]);
                $user_id = $stmt->fetchColumn();
                
                // Verify current password
                $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $hashed_password = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $hashed_password)) {
                    throw new Exception("Current password is incorrect");
                }
                
                // Validate new password
                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match");
                }
                
                if (strlen($new_password) < 8) {
                    throw new Exception("New password must be at least 8 characters long");
                }
                
                // Update password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_new_password, $user_id]);
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->setFlash('success', 'Profile updated successfully');
            $this->redirect('index.php?module=doctor&action=profile');
            
        } catch (Exception $e) {
            // Rollback transaction if active
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            $this->logError("Error updating profile: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=profile');
        }
    }
    
    /**
     * Display and manage prescriptions
     */
    public function prescriptions() {
        try {
            // Get all prescriptions for this doctor
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       pt.name as patient_name,
                       pt.phone as patient_phone
                FROM prescriptions p
                INNER JOIN patients pt ON p.patient_id = pt.id
                WHERE p.doctor_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$this->doctorId]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add first_name and last_name for each patient
            foreach ($prescriptions as &$prescription) {
                if (isset($prescription['patient_name'])) {
                    $nameParts = explode(' ', $prescription['patient_name'], 2);
                    $prescription['patient_first_name'] = $nameParts[0];
                    $prescription['patient_last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
                }
            }
            
            // Prepare data for the view
            $data = [
                'page_title' => 'Manage Prescriptions',
                'prescriptions' => $prescriptions
            ];
            
            // Render the prescriptions view
            $this->render('prescriptions', $data);
            
        } catch (Exception $e) {
            $this->logError("Error in prescriptions: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred while loading prescriptions: ' . $e->getMessage());
            $this->render('prescriptions', ['page_title' => 'Manage Prescriptions', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Display and manage patients
     */
    public function patients() {
        try {
            // Get all patients assigned to this doctor
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.*, 
                       COUNT(a.id) as appointment_count
                FROM patients p
                LEFT JOIN appointments a ON p.id = a.patient_id AND a.doctor_id = ?
                GROUP BY p.id
                ORDER BY p.name ASC
            ");
            $stmt->execute([$this->doctorId]);
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add first_name and last_name for each patient
            foreach ($patients as &$patient) {
                if (isset($patient['name'])) {
                    $nameParts = explode(' ', $patient['name'], 2);
                    $patient['first_name'] = $nameParts[0];
                    $patient['last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
                }
            }
            
            // Prepare data for the view
            $data = [
                'page_title' => 'Manage Patients',
                'patients' => $patients
            ];
            
            // Render the patients view
            $this->render('patients', $data);
            
        } catch (Exception $e) {
            $this->logError("Error in patients: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred while loading patients: ' . $e->getMessage());
            $this->render('patients', ['page_title' => 'Manage Patients', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Edit doctor profile
     */
    public function edit_profile() {
        try {
            // Check if form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process form submission - redirect to update_profile
                $this->redirect('index.php?module=doctor&action=update_profile');
                return;
            }
            
            // Check if hospital_id column exists in doctors table
            $columnExists = false;
            try {
                $stmt = $this->db->prepare("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
                $stmt->execute();
                $columnExists = ($stmt->rowCount() > 0);
                
                // If column doesn't exist, add it
                if (!$columnExists) {
                    $this->db->exec("ALTER TABLE doctors ADD COLUMN hospital_id INT DEFAULT NULL");
                    $this->db->exec("ALTER TABLE doctors ADD INDEX hospital_id (hospital_id)");
                    error_log("Added hospital_id column to doctors table in edit_profile");
                }
            } catch (PDOException $e) {
                error_log("Error checking/adding hospital_id column: " . $e->getMessage());
            }
            
            // Get doctor details with user information
            $stmt = $this->db->prepare("
                SELECT d.*, u.email, u.status as user_status
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$this->doctorId]);
            $doctor_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doctor_details) {
                $_SESSION['error'] = "Doctor profile not found.";
                $this->redirect('index.php?module=doctor&action=dashboard');
                return;
            }
            
            // Get all hospitals for dropdown
            $stmt = $this->db->prepare("
                SELECT id, name, address 
                FROM hospitals 
                WHERE status = 'active'
                ORDER BY name ASC
            ");
            $stmt->execute();
            $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Render the edit profile form
            $this->render('edit_profile', [
                'doctor' => $doctor_details,
                'hospitals' => $hospitals
            ]);
        } catch (PDOException $e) {
            error_log("Error in edit_profile: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "An error occurred while updating your profile: " . $e->getMessage();
            $this->redirect('index.php?module=doctor&action=profile');
        } catch (Exception $e) {
            error_log("General error in edit_profile: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = "An error occurred: " . $e->getMessage();
            $this->redirect('index.php?module=doctor&action=profile');
        }
    }
    
    /**
     * Edit prescription
     */
    public function edit_prescription() {
        try {
            $prescription_id = intval($_GET['id'] ?? 0);
            
            if ($prescription_id <= 0) {
                $this->setFlash('error', 'Invalid prescription ID');
                $this->redirect('index.php?module=doctor&action=prescriptions');
                return;
            }
            
            // Get prescription details
            $stmt = $this->db->prepare("
                SELECT p.*, pt.name as patient_name, pt.id as patient_id
                FROM prescriptions p
                JOIN patients pt ON p.patient_id = pt.id
                WHERE p.id = ? AND p.doctor_id = ?
            ");
            $stmt->execute([$prescription_id, $this->doctorId]);
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prescription) {
                $this->setFlash('error', 'Prescription not found or you do not have permission to edit it');
                $this->redirect('index.php?module=doctor&action=prescriptions');
                return;
            }
            
            // Get prescription items
            $stmt = $this->db->prepare("
                SELECT * FROM prescription_items
                WHERE prescription_id = ?
                ORDER BY id ASC
            ");
            $stmt->execute([$prescription_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('edit_prescription', [
                'page_title' => 'Edit Prescription',
                'prescription' => $prescription,
                'items' => $items
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading prescription: " . $e->getMessage());
            $this->setFlash('error', 'Error loading prescription');
            $this->redirect('index.php?module=doctor&action=prescriptions');
        }
    }
    
    /**
     * Process prescription update
     */
    public function update_prescription() {
        try {
            $prescription_id = intval($_POST['prescription_id'] ?? 0);
            $patient_id = intval($_POST['patient_id'] ?? 0);
            $diagnosis = trim($_POST['diagnosis'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            // Validate input
            if ($prescription_id <= 0 || $patient_id <= 0 || empty($diagnosis)) {
                $this->setFlash('error', 'Invalid input data');
                $this->redirect('index.php?module=doctor&action=edit_prescription&id=' . $prescription_id);
                return;
            }
            
            // Verify prescription belongs to this doctor
            $stmt = $this->db->prepare("
                SELECT id FROM prescriptions 
                WHERE id = ? AND doctor_id = ?
            ");
            $stmt->execute([$prescription_id, $this->doctorId]);
            if (!$stmt->fetch()) {
                $this->setFlash('error', 'You do not have permission to edit this prescription');
                $this->redirect('index.php?module=doctor&action=prescriptions');
                return;
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update prescription
            $stmt = $this->db->prepare("
                UPDATE prescriptions 
                SET diagnosis = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$diagnosis, $notes, $prescription_id]);
            
            // Delete existing prescription items
            $stmt = $this->db->prepare("
                DELETE FROM prescription_items 
                WHERE prescription_id = ?
            ");
            $stmt->execute([$prescription_id]);
            
            // Add new prescription items
            $medicines = $_POST['medicine'] ?? [];
            $dosages = $_POST['dosage'] ?? [];
            $frequencies = $_POST['frequency'] ?? [];
            $durations = $_POST['duration'] ?? [];
            $instructions = $_POST['instructions'] ?? [];
            
            for ($i = 0; $i < count($medicines); $i++) {
                if (!empty($medicines[$i])) {
                    $stmt = $this->db->prepare("
                        INSERT INTO prescription_items (
                            prescription_id, medicine, dosage, frequency, duration, instructions
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $prescription_id,
                        $medicines[$i],
                        $dosages[$i] ?? '',
                        $frequencies[$i] ?? '',
                        $durations[$i] ?? '',
                        $instructions[$i] ?? ''
                    ]);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->setFlash('success', 'Prescription updated successfully');
            $this->redirect('index.php?module=doctor&action=prescriptions');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error updating prescription: " . $e->getMessage());
            $this->setFlash('error', 'Error updating prescription: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=edit_prescription&id=' . ($prescription_id ?? 0));
        }
    }
    
    /**
     * Delete prescription
     */
    public function delete_prescription() {
        try {
            $prescription_id = intval($_GET['id'] ?? 0);
            
            if ($prescription_id <= 0) {
                $this->setFlash('error', 'Invalid prescription ID');
                $this->redirect('index.php?module=doctor&action=prescriptions');
                return;
            }
            
            // Verify prescription belongs to this doctor
            $stmt = $this->db->prepare("
                SELECT id FROM prescriptions 
                WHERE id = ? AND doctor_id = ?
            ");
            $stmt->execute([$prescription_id, $this->doctorId]);
            if (!$stmt->fetch()) {
                $this->setFlash('error', 'You do not have permission to delete this prescription');
                $this->redirect('index.php?module=doctor&action=prescriptions');
                return;
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Delete prescription items
            $stmt = $this->db->prepare("
                DELETE FROM prescription_items 
                WHERE prescription_id = ?
            ");
            $stmt->execute([$prescription_id]);
            
            // Delete prescription
            $stmt = $this->db->prepare("
                DELETE FROM prescriptions 
                WHERE id = ?
            ");
            $stmt->execute([$prescription_id]);
            
            // Commit transaction
            $this->db->commit();
            
            $this->setFlash('success', 'Prescription deleted successfully');
            $this->redirect('index.php?module=doctor&action=prescriptions');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error deleting prescription: " . $e->getMessage());
            $this->setFlash('error', 'Error deleting prescription: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=prescriptions');
        }
    }
    
    /**
     * Display form to reschedule an appointment
     */
    public function reschedule_appointment() {
        try {
            // Get appointment ID from the query string
            $appointment_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            
            // If form is submitted (POST request)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->process_reschedule();
            }
            
            if (!$appointment_id) {
                throw new Exception("Appointment ID is required");
            }
            
            // Check if appointment belongs to this doctor
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       p.name as patient_name,
                       p.phone as patient_phone
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id
                WHERE a.id = ? AND a.doctor_id = ?
            ");
            $stmt->execute([$appointment_id, $this->doctorId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found or access denied");
            }
            
            // Check if appointment can be rescheduled (not completed or cancelled)
            if (in_array($appointment['status'], ['completed', 'cancelled'])) {
                $this->setFlash('error', 'Cannot reschedule a completed or cancelled appointment');
                $this->redirect('index.php?module=doctor&action=appointments');
                return;
            }
            
            // Add first_name and last_name for the patient
            if (isset($appointment['patient_name'])) {
                $nameParts = explode(' ', $appointment['patient_name'], 2);
                $appointment['patient_first_name'] = $nameParts[0];
                $appointment['patient_last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
            }
            
            // Prepare data for the view
            $data = [
                'page_title' => 'Reschedule Appointment',
                'appointment' => $appointment,
                'current_date' => date('Y-m-d', strtotime($appointment['appointment_date'])),
                'current_time' => date('H:i', strtotime($appointment['appointment_date']))
            ];
            
            // Render the reschedule appointment view
            $this->render('reschedule_appointment', $data);
            
        } catch (Exception $e) {
            $this->logError("Error in reschedule_appointment: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=appointments');
        }
    }
    
    /**
     * Process the appointment reschedule form submission
     */
    private function process_reschedule() {
        try {
            $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
            $appointment_date = filter_input(INPUT_POST, 'appointment_date', FILTER_SANITIZE_STRING);
            $appointment_time = filter_input(INPUT_POST, 'appointment_time', FILTER_SANITIZE_STRING);
            $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
            
            // Validate inputs
            if (!$appointment_id || !$appointment_date || !$appointment_time) {
                throw new Exception("Please provide all required fields");
            }
            
            // Check if appointment belongs to this doctor
            $stmt = $this->db->prepare("
                SELECT a.*, p.id as patient_id, p.name as patient_name
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id
                WHERE a.id = ? AND a.doctor_id = ?
            ");
            $stmt->execute([$appointment_id, $this->doctorId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found or access denied");
            }
            
            // Combine date and time
            $new_appointment_datetime = $appointment_date . ' ' . $appointment_time . ':00';
            
            // Validate date is in the future
            if (strtotime($new_appointment_datetime) < time()) {
                throw new Exception("Appointment date must be in the future");
            }
            
            // Format old date for message
            $old_datetime = date('F j, Y \a\t g:i a', strtotime($appointment['appointment_date']));
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update appointment
            $stmt = $this->db->prepare("
                UPDATE appointments 
                SET appointment_date = ?, 
                    notes = CONCAT(IFNULL(notes, ''), '\nRescheduled from: " . $old_datetime . "'), 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$new_appointment_datetime, $appointment_id]);
            
            // Check if appointment was actually rescheduled (different time)
            $was_rescheduled = $appointment['appointment_date'] !== $new_appointment_datetime;
            
            // Create patient alert for rescheduled appointment
            if ($was_rescheduled) {
                try {
                    // Check if patient_alerts table exists
                    $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                    $patient_alerts_exists = ($stmt->rowCount() > 0);
                    
                    // Create table if it doesn't exist
                    if (!$patient_alerts_exists) {
                        $this->db->exec("
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
                        $patient_alerts_exists = true;
                        error_log("Created patient_alerts table for rescheduling");
                    }
                    
                    if ($patient_alerts_exists) {
                        // Get doctor details
                        $stmt = $this->db->prepare("
                            SELECT name, specialization 
                            FROM doctors 
                            WHERE id = ?
                        ");
                        $stmt->execute([$this->doctorId]);
                        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Format new datetime for message
                        $new_datetime = date('F j, Y \a\t g:i a', strtotime($new_appointment_datetime));
                        
                        // Create the alert
                        $stmt = $this->db->prepare("
                            INSERT INTO patient_alerts (
                                patient_id, title, message, alert_type, reference_id, is_read
                            ) VALUES (?, ?, ?, 'appointment', ?, 0)
                        ");
                        $stmt->execute([
                            $appointment['patient_id'],
                            "Appointment Rescheduled",
                            "Your appointment with Dr. {$doctor['name']} ({$doctor['specialization']}) has been rescheduled from {$old_datetime} to {$new_datetime}.",
                            $appointment_id
                        ]);
                        
                        error_log("Created patient alert for rescheduled appointment ID: {$appointment_id}, patient ID: {$appointment['patient_id']}");
                    }
                } catch (PDOException $e) {
                    error_log("Error creating patient alert for rescheduling: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
                
                // Send email notification for rescheduled appointment
                try {
                    // Get patient, doctor, and hospital details for email
                    $query = "
                        SELECT a.*, p.name as patient_name, p.email as patient_email,
                               d.name as doctor_name, d.specialization";
                    
                    // Check if hospital_id column exists in doctors table
                    $stmt = $this->db->prepare("SHOW COLUMNS FROM doctors LIKE 'hospital_id'");
                    $stmt->execute();
                    $columnExists = ($stmt->rowCount() > 0);
                    
                    if ($columnExists) {
                        $query .= ", d.hospital_id,
                               h.name as hospital_name, h.address as hospital_address";
                    }
                    
                    $query .= "
                        FROM appointments a
                        INNER JOIN patients p ON a.patient_id = p.id
                        INNER JOIN doctors d ON a.doctor_id = d.id";
                    
                    if ($columnExists) {
                        $query .= "
                        LEFT JOIN hospitals h ON d.hospital_id = h.id";
                    }
                    
                    $query .= "
                        WHERE a.id = ?";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$appointment_id]);
                    $appointmentData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($appointmentData) {
                        $emailService = new EmailService();
                        $appointmentData['date'] = date('F j, Y', strtotime($new_appointment_datetime));
                        $appointmentData['time'] = date('h:i A', strtotime($new_appointment_datetime));
                        $appointmentData['previous_date'] = date('F j, Y', strtotime($appointment['appointment_date']));
                        $appointmentData['previous_time'] = date('h:i A', strtotime($appointment['appointment_date']));
                        
                        // Set location based on hospital info
                        $appointmentData['location'] = isset($appointmentData['hospital_name']) && $appointmentData['hospital_name'] ? 
                            $appointmentData['hospital_name'] . ', ' . $appointmentData['hospital_address'] : 
                            'Location not specified';
                        
                        $emailService->sendAppointmentStatusUpdate(
                            $appointmentData['patient_email'],
                            $appointmentData['patient_name'],
                            $appointmentData,
                            'rescheduled'
                        );
                    }
                } catch (Exception $e) {
                    error_log("Error sending email for rescheduled appointment: " . $e->getMessage());
                    // Continue execution even if email fails
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->setFlash('success', 'Appointment rescheduled successfully');
            $this->redirect('index.php?module=doctor&action=appointments');
            
        } catch (Exception $e) {
            // Rollback transaction if active
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            $this->logError("Error processing reschedule: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=appointments');
        }
    }
    
    /**
     * Chat with patient
     * This method handles the chat interface for a specific appointment
     */
    public function chat_with_patient() {
        try {
            $appointment_id = filter_input(INPUT_GET, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$appointment_id) {
                throw new Exception("Appointment ID is required");
            }
            
            // Check if appointment belongs to this doctor and is confirmed
            $stmt = $this->db->prepare("
                SELECT a.*, p.name as patient_name, p.id as patient_id
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                WHERE a.id = ? AND a.doctor_id = ?
            ");
            $stmt->execute([$appointment_id, $this->doctorId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found or access denied");
            }
            
            // Check if appointment is confirmed
            if ($appointment['status'] != 'confirmed') {
                throw new Exception("Chat is only available for confirmed appointments");
            }
            
            // Get chat messages
            $stmt = $this->db->prepare("
                SELECT * FROM chat_messages
                WHERE appointment_id = ?
                ORDER BY created_at ASC
            ");
            $stmt->execute([$appointment_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mark unread messages from patient as read
            $stmt = $this->db->prepare("
                UPDATE chat_messages
                SET is_read = 1, read_at = NOW()
                WHERE appointment_id = ? AND sender_type = 'patient' AND is_read = 0
            ");
            $stmt->execute([$appointment_id]);
            
            // Render the chat view - use correct path
            include(__DIR__ . '/views/chat.php');
            
        } catch (Exception $e) {
            $this->logError("Error accessing chat: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=appointments');
        }
    }
    
    /**
     * Send message to patient
     * This method handles sending a new message to a patient
     */
    public function send_message() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
            $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (!$appointment_id || !$message) {
                throw new Exception("Missing required parameters");
            }
            
            // Check if appointment belongs to this doctor and is confirmed
            $stmt = $this->db->prepare("
                SELECT * FROM appointments 
                WHERE id = ? AND doctor_id = ? AND status = 'confirmed'
            ");
            $stmt->execute([$appointment_id, $this->doctorId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found, access denied, or not in confirmed status");
            }
            
            // Insert the message
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages (
                    appointment_id, sender_type, sender_id, message, is_read
                ) VALUES (
                    ?, 'doctor', ?, ?, 0
                )
            ");
            $stmt->execute([
                $appointment_id,
                $this->doctorId,
                $message
            ]);
            
            // Create a patient alert for the new message
            try {
                // Get doctor and patient details
                $stmt = $this->db->prepare("
                    SELECT a.patient_id, d.name as doctor_name, d.specialization
                    FROM appointments a
                    JOIN doctors d ON a.doctor_id = d.id
                    WHERE a.id = ?
                ");
                $stmt->execute([$appointment_id]);
                $details = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($details) {
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'appointment', ?, 0)
                    ");
                    
                    $stmt->execute([
                        $details['patient_id'],
                        "New message from Dr. {$details['doctor_name']}",
                        "Dr. {$details['doctor_name']} ({$details['specialization']}) has sent you a new message regarding your appointment.",
                        $appointment_id
                    ]);
                }
            } catch (PDOException $e) {
                error_log("Error creating patient alert for chat message: " . $e->getMessage());
                // Continue execution even if alert creation fails
            }
            
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            
            // Redirect back to the chat page
            $this->redirect('index.php?module=doctor&action=chat_with_patient&appointment_id=' . $appointment_id);
            
        } catch (Exception $e) {
            $this->logError("Error sending message: " . $e->getMessage());
            
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=doctor&action=appointments');
        }
    }
}
