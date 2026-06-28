<?php
/**
 * Patient Module Controller
 * Palliative Care System
 */

class PatientController extends BaseController {
    protected $patient;
    
    public function __construct() {
        parent::__construct();
        
        try {
            // Get patient details
            $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$patient) {
                session_destroy();
                $_SESSION['error'] = "Patient account not found. Please contact support.";
                header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=patient");
            exit;
        }
        
            $this->patient = $patient;
            
        } catch (PDOException $e) {
            error_log("Error in PatientController constructor: " . $e->getMessage());
            session_destroy();
            $_SESSION['error'] = "An error occurred. Please try again later.";
            header("Location: " . SITE_URL . "index.php?module=auth&action=login&type=patient");
            exit;
        }
    }

    /**
     * Display patient dashboard
     */
    public function dashboard() {
        try {
            // Get upcoming appointments
            $stmt = $this->db->prepare("
                SELECT a.*, d.name as doctor_name, d.specialization
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.patient_id = ? AND a.status != 'cancelled' AND a.appointment_date >= NOW()
                ORDER BY a.appointment_date
                LIMIT 3
            ");
            $stmt->execute([$this->patient['id']]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent prescriptions
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as doctor_name
                FROM prescriptions p
                JOIN doctors d ON p.doctor_id = d.id
                WHERE p.patient_id = ?
                ORDER BY p.created_at DESC
                LIMIT 3
            ");
            $stmt->execute([$this->patient['id']]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent medicine orders
            $stmt = $this->db->prepare("
                SELECT mo.*, ph.name as pharmacy_name
                FROM medicine_orders mo
                LEFT JOIN pharmacies ph ON mo.pharmacy_id = ph.id
                WHERE mo.patient_id = ?
                ORDER BY mo.created_at DESC
                LIMIT 3
            ");
            $stmt->execute([$this->patient['id']]);
            $medicine_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent cab bookings
            $stmt = $this->db->prepare("
                SELECT cb.*, sp.company_name as provider_name
                FROM cab_bookings cb
                LEFT JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.patient_id = ?
                ORDER BY cb.created_at DESC
                LIMIT 3
            ");
            $stmt->execute([$this->patient['id']]);
            $cab_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Check if patient_alerts table exists
            $table_exists = false;
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                $table_exists = ($stmt->rowCount() > 0);
            } catch (Exception $e) {
                $table_exists = false;
            }
            
            // Get recent alerts if table exists
            $alerts = [];
            $unread_count = 0;
            if ($table_exists) {
                try {
                    // Get recent alerts
                    $stmt = $this->db->prepare("
                        SELECT * FROM patient_alerts
                        WHERE patient_id = ?
                        ORDER BY created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get unread alert count
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM patient_alerts
                        WHERE patient_id = ? AND is_read = 0
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $unread_count = $stmt->fetchColumn();
                    
                    // Log alert data for debugging
                    error_log("Patient dashboard alerts for patient ID {$this->patient['id']}: " . count($alerts) . " alerts found");
                    if (count($alerts) > 0) {
                        error_log("First alert: " . print_r($alerts[0], true));
                    }
                } catch (Exception $e) {
                    error_log("Error fetching alerts: " . $e->getMessage());
                }
            }
            
            $this->render('dashboard', [
                'patient' => $this->patient,
                'appointments' => $appointments,
                'prescriptions' => $prescriptions,
                'medicine_orders' => $medicine_orders,
                'cab_bookings' => $cab_bookings,
                'alerts' => $alerts,
                'unread_count' => $unread_count
            ]);
        } catch (PDOException $e) {
            error_log("Error in dashboard: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your dashboard.";
            // Unable to load dashboard, forward to profile
            header('Location: index.php?module=patient&action=profile');
            exit();
        }
    }

    /**
     * Display and manage appointments
     */
    public function appointments() {
        try {
            // Get all appointments for this patient
            $stmt = $this->db->prepare("
                SELECT a.*, d.name as doctor_name, d.specialization, d.id as doctor_id
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.patient_id = ?
                ORDER BY a.appointment_date DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get unread message counts for confirmed appointments
            $unread_counts = [];
            $stmt = $this->db->prepare("
                SELECT appointment_id, COUNT(*) as count
                FROM chat_messages
                WHERE appointment_id IN (
                    SELECT id FROM appointments 
                    WHERE patient_id = ? AND status = 'confirmed'
                )
                AND sender_type = 'doctor'
                AND is_read = 0
                GROUP BY appointment_id
            ");
            $stmt->execute([$this->patient['id']]);
            $unread_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($unread_results as $result) {
                $unread_counts[$result['appointment_id']] = $result['count'];
            }
            
            // Add unread count to appointments
            foreach ($appointments as &$appointment) {
                $appointment['unread_count'] = $unread_counts[$appointment['id']] ?? 0;
            }
            
            include __DIR__ . '/views/appointments.php';
        } catch (Exception $e) {
            $this->logError("Error fetching appointments: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred while fetching appointments.');
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * Book new appointment
     */
    public function book_appointment() {
        try {
            // If form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate and sanitize input
                $doctor_id = filter_var($_POST['doctor_id'], FILTER_SANITIZE_NUMBER_INT);
                $appointment_date = filter_var($_POST['appointment_date'], FILTER_SANITIZE_STRING);
                $reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING);
                
                // Validate required fields
                if (empty($doctor_id) || empty($appointment_date)) {
                    $_SESSION['error'] = "Please fill in all required fields.";
                    $this->redirect('index.php?module=patient&action=book_appointment' . (empty($doctor_id) ? '' : '&doctor_id=' . $doctor_id));
                    return;
                }
                
                // Validate appointment date (must be in the future)
                $appointment_timestamp = strtotime($appointment_date);
                if ($appointment_timestamp < time()) {
                    $_SESSION['error'] = "Appointment date must be in the future.";
                    $this->redirect('index.php?module=patient&action=book_appointment' . (empty($doctor_id) ? '' : '&doctor_id=' . $doctor_id));
                    return;
                }
                
                // Get doctor's consultation fee
                $stmt = $this->db->prepare("
                    SELECT consultation_fee FROM doctors WHERE id = ?
                ");
                $stmt->execute([$doctor_id]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Add payment_status column if it doesn't exist
                $stmt = $this->db->query("SHOW COLUMNS FROM appointments LIKE 'payment_status'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE appointments ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
                }
                
                // Insert appointment with explicit pending status
                $stmt = $this->db->prepare("
                    INSERT INTO appointments (
                        patient_id, 
                        doctor_id, 
                        appointment_date, 
                        reason, 
                        status,         -- Explicitly set status
                        payment_status,  -- Payment status
                        created_at      -- Creation timestamp
                    ) VALUES (
                        ?, ?, ?, ?, 
                        'pending',      -- Always start as pending, requires doctor confirmation
                        'pending',      -- Payment starts as pending
                        NOW()
                    )
                ");
                $stmt->execute([
                    $this->patient['id'],
                    $doctor_id,
                    $appointment_date,
                    $reason
                ]);
                
                $appointment_id = $this->db->lastInsertId();
                
                // Get doctor details for the alert
                $stmt = $this->db->prepare("SELECT name, specialization FROM doctors WHERE id = ?");
                $stmt->execute([$doctor_id]);
                $doctor_details = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Create an alert for the new appointment
                $appointment_date_formatted = date("F j, Y, g:i a", strtotime($appointment_date));
                $this->createAlert(
                    "New Appointment Booked",
                    "You have booked an appointment with Dr. {$doctor_details['name']} ({$doctor_details['specialization']}) for {$appointment_date_formatted}. Please proceed with payment.",
                    'appointment',
                    $appointment_id
                );
                
                $_SESSION['success'] = "Appointment booked successfully. Please proceed with payment.";
                $this->redirect('index.php?module=patient&action=payment&type=appointment&id=' . $appointment_id);
                return;
        }

        // Get available doctors
        $stmt = $this->db->prepare("
                SELECT d.id, d.name, d.specialization, d.consultation_fee
                FROM doctors d
                JOIN users u ON d.user_id = u.id
                WHERE u.status = 'active' AND d.availability_status = 'available'
                ORDER BY d.name
        ");
        $stmt->execute();
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get selected doctor details if doctor_id is provided
            $selected_doctor = null;
            if (isset($_GET['doctor_id']) && !empty($_GET['doctor_id'])) {
                $selected_doctor_id = filter_var($_GET['doctor_id'], FILTER_SANITIZE_NUMBER_INT);
                foreach ($doctors as $doctor) {
                    if ($doctor['id'] == $selected_doctor_id) {
                        $selected_doctor = $doctor;
                        break;
                    }
                }
            }
            
            // Render the book appointment form
            $this->render('book_appointment', [
                'patient' => $this->patient,
                'doctors' => $doctors,
                'selected_doctor' => $selected_doctor
            ]);
        } catch (PDOException $e) {
            error_log("Error in book_appointment: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading available doctors.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * View specific appointment
     */
    public function view_appointment() {
        try {
            $appointment_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$appointment_id) {
                throw new Exception("Appointment ID is required.");
            }
            
            // Get appointment details
            $stmt = $this->db->prepare("
                SELECT a.*, d.name as doctor_name, d.specialization, d.id as doctor_id
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.id = ? AND a.patient_id = ?
            ");
            $stmt->execute([$appointment_id, $this->patient['id']]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                throw new Exception("Appointment not found or access denied.");
            }
            
            // Get unread message count for this appointment if confirmed
            if ($appointment['status'] === 'confirmed') {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as unread_count
                    FROM chat_messages
                    WHERE appointment_id = ?
                    AND sender_type = 'doctor'
                    AND is_read = 0
                ");
                $stmt->execute([$appointment_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $appointment['unread_count'] = $result['unread_count'] ?? 0;
            } else {
                $appointment['unread_count'] = 0;
            }
            
            include __DIR__ . '/views/view_appointment.php';
        } catch (Exception $e) {
            $this->logError("Error viewing appointment: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=patient&action=appointments');
        }
    }

    /**
     * Cancel an appointment
     */
    public function cancel_appointment() {
        try {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id) {
            $_SESSION['error'] = "Invalid appointment ID";
                $this->redirect('index.php?module=patient&action=appointments');
        }

            $this->db->beginTransaction();

            // Verify appointment belongs to patient and can be cancelled
            $stmt = $this->db->prepare("
                SELECT status, doctor_id, appointment_date 
                FROM appointments 
                WHERE id = ? AND patient_id = ?
                AND status IN ('scheduled', 'confirmed')
            ");
            $stmt->execute([$id, $this->patient['id']]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            if (!$appointment) {
                throw new Exception("Appointment cannot be cancelled");
            }

            // Cancel the appointment
            $stmt = $this->db->prepare("
                UPDATE appointments 
                SET status = 'cancelled',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            // Get doctor details for the alert
            $stmt = $this->db->prepare("
                SELECT name, specialization FROM doctors WHERE id = ?
            ");
            $stmt->execute([$appointment['doctor_id']]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Create alert for cancelled appointment
            $appointment_date_formatted = date("F j, Y, g:i a", strtotime($appointment['appointment_date']));
            $this->createAlert(
                "Appointment Cancelled",
                "Your appointment with Dr. {$doctor['name']} ({$doctor['specialization']}) scheduled for {$appointment_date_formatted} has been cancelled.",
                'appointment',
                $id
            );

            $this->db->commit();
            $_SESSION['success'] = "Appointment cancelled successfully";

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('index.php?module=patient&action=appointments');
    }

    /**
     * View prescriptions
     */
    public function prescriptions() {
        try {
            // Get all prescriptions for the patient
        $stmt = $this->db->prepare("
                SELECT p.*, d.name as doctor_name 
            FROM prescriptions p
                JOIN doctors d ON p.doctor_id = d.id 
            WHERE p.patient_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Render the prescriptions view
            $this->render('prescriptions', [
                'patient' => $this->patient,
                'prescriptions' => $prescriptions
            ]);
        } catch (PDOException $e) {
            error_log("Error in prescriptions: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your prescriptions.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * View specific prescription
     */
    public function view_prescription() {
        try {
            // Get prescription ID from URL
            $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($id)) {
                $_SESSION['error'] = "Invalid prescription ID.";
                $this->redirect('index.php?module=patient&action=prescriptions');
                return;
            }
            
            // Get prescription details
        $stmt = $this->db->prepare("
                SELECT p.*, d.name as doctor_name 
            FROM prescriptions p
                JOIN doctors d ON p.doctor_id = d.id 
            WHERE p.id = ? AND p.patient_id = ?
        ");
            $stmt->execute([$id, $this->patient['id']]);
            $prescription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prescription) {
                $_SESSION['error'] = "Prescription not found or access denied.";
                $this->redirect('index.php?module=patient&action=prescriptions');
                return;
            }
            
            // Get prescription medications
        $stmt = $this->db->prepare("
            SELECT * FROM prescription_items 
            WHERE prescription_id = ?
                ORDER BY id
            ");
            $stmt->execute([$id]);
            $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no prescription items found but diagnosis field has medication info
            if (empty($medications) && !empty($prescription['diagnosis'])) {
                // Parse medication info from diagnosis field
                $medications = $this->parsePrescriptionDiagnosis($prescription['diagnosis']);
            }
            
            // Render the prescription view
            $this->render('view_prescription', [
                'patient' => $this->patient,
                'prescription' => $prescription,
                'medications' => $medications
            ]);
        } catch (PDOException $e) {
            error_log("Error in view_prescription: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading the prescription details.";
            $this->redirect('index.php?module=patient&action=prescriptions');
        }
    }
    
    /**
     * Parse prescription diagnosis text into structured items
     */
    private function parsePrescriptionDiagnosis($text) {
        $items = [];
        $lines = explode("\n", $text);
        
        $currentItem = [
            'medicine' => '',
            'dosage' => '',
            'frequency' => '',
            'duration' => '',
            'instructions' => ''
        ];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, 'Medication:') !== false) {
                // If we already have an item, save it before starting a new one
                if (!empty($currentItem['medicine'])) {
                    $items[] = $currentItem;
                }
                
                // Start a new item
                $currentItem = [
                    'medicine' => trim(str_replace('Medication:', '', $line)),
                    'dosage' => '',
                    'frequency' => '',
                    'duration' => '',
                    'instructions' => ''
                ];
            } elseif (strpos($line, 'Dosage:') !== false) {
                $currentItem['dosage'] = trim(str_replace('Dosage:', '', $line));
            } elseif (strpos($line, 'Frequency:') !== false) {
                $currentItem['frequency'] = trim(str_replace('Frequency:', '', $line));
            } elseif (strpos($line, 'Duration:') !== false) {
                $currentItem['duration'] = trim(str_replace('Duration:', '', $line));
            } else {
                // If it doesn't match any of the above, consider it additional instructions
                if (empty($currentItem['instructions'])) {
                    $currentItem['instructions'] = $line;
                } else {
                    $currentItem['instructions'] .= "\n" . $line;
                }
            }
        }
        
        // Add the last item if not empty
        if (!empty($currentItem['medicine'])) {
            $items[] = $currentItem;
        }
        
        return $items;
    }

    /**
     * Display medicine ordering page
     */
    public function order_medicine() {
        try {
            // Get available pharmacies
            $stmt = $this->db->prepare("
                SELECT id, name, address, phone, operating_hours, delivery_available
                FROM pharmacies
                WHERE status = 'active'
                ORDER BY name ASC
            ");
            $stmt->execute();
            $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get patient's prescriptions
            $stmt = $this->db->prepare("
                SELECT p.*, d.name as doctor_name
                FROM prescriptions p
                JOIN doctors d ON p.doctor_id = d.id
                WHERE p.patient_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get patient's previous orders
            $stmt = $this->db->prepare("
                SELECT mo.*, p.name as pharmacy_name
                FROM medicine_orders mo
                JOIN pharmacies p ON mo.pharmacy_id = p.id
                WHERE mo.patient_id = ?
                ORDER BY mo.created_at DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $previous_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('order_medicine', [
                'page_title' => 'Order Medicine',
                'pharmacies' => $pharmacies,
                'prescriptions' => $prescriptions,
                'previous_orders' => $previous_orders
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading medicine ordering page: " . $e->getMessage());
            $_SESSION['error'] = "Error loading medicine ordering page";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Process medicine order
     */
    public function process_order_medicine() {
        try {
            // Validate input
            $pharmacy_id = intval($_POST['pharmacy_id'] ?? 0);
            $prescription_id = intval($_POST['prescription_id'] ?? 0);
            $delivery_address = trim($_POST['delivery_address'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            // Validate required fields
            if ($pharmacy_id <= 0) {
                $_SESSION['error'] = "Please select a pharmacy";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // Check if pharmacy exists and is active
            $stmt = $this->db->prepare("
                SELECT id, delivery_available, name FROM pharmacies 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$pharmacy_id]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pharmacy) {
                $_SESSION['error'] = "Selected pharmacy is not available";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // If delivery is requested, ensure pharmacy offers delivery and address is provided
            $delivery_requested = isset($_POST['delivery_requested']) && $_POST['delivery_requested'] == 1;
            
            if ($delivery_requested) {
                if ($pharmacy['delivery_available'] != 1) {
                    $_SESSION['error'] = "Selected pharmacy does not offer delivery service";
                    $_SESSION['form_data'] = $_POST;
                    $this->redirect('index.php?module=patient&action=order_medicine');
                    return;
                }
                
                if (empty($delivery_address)) {
                    $_SESSION['error'] = "Delivery address is required";
                    $_SESSION['form_data'] = $_POST;
                    $this->redirect('index.php?module=patient&action=order_medicine');
                    return;
                }
            }
            
            // Generate order number
            $order_number = 'ORD-' . time() . '-' . $this->patient['id'];
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO medicine_orders (
                    patient_id, pharmacy_id, prescription_id, order_number,
                    total_amount, payment_status, order_status,
                    delivery_address, notes
                ) VALUES (
                    ?, ?, ?, ?, 0.00, 'pending', 'pending', ?, ?
                )
            ");
            $stmt->execute([
                $this->patient['id'],
                $pharmacy_id,
                ($prescription_id > 0) ? $prescription_id : null,
                $order_number,
                $delivery_requested ? $delivery_address : null,
                $notes
            ]);
            
            $order_id = $this->db->lastInsertId();
            
            // If medicines were manually entered, add them to the order
            $total_order_amount = 0.00;
            if (isset($_POST['medicine']) && is_array($_POST['medicine'])) {
                $medicines = $_POST['medicine'];
                $quantities = $_POST['quantity'] ?? [];
                
                for ($i = 0; $i < count($medicines); $i++) {
                    if (!empty($medicines[$i])) {
                        $quantity = intval($quantities[$i] ?? 1);
                        
                        // Get medicine price from database
                        $stmt = $this->db->prepare("
                            SELECT price 
                            FROM medicines 
                            WHERE name = ? AND pharmacy_id = ? AND status = 'active'
                            LIMIT 1
                        ");
                        $stmt->execute([$medicines[$i], $pharmacy_id]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Use actual price from database or default price
                        $unit_price = $result ? floatval($result['price']) : 10.00;
                        $total_price = $quantity * $unit_price;
                        $total_order_amount += $total_price;

                        $stmt = $this->db->prepare("
                            INSERT INTO medicine_order_items (
                                order_id, medicine_id, medicine_name, quantity, unit_price, total_price
                            ) VALUES (
                                ?, NULL, ?, ?, ?, ?
                            )
                        ");
                        $stmt->execute([
                            $order_id,
                            $medicines[$i],
                            $quantity,
                            $unit_price,
                            $total_price
                        ]);
                    }
                }
            }
            
            // Update order total amount
            $stmt = $this->db->prepare("
                UPDATE medicine_orders 
                SET total_amount = ? 
                WHERE id = ?
            ");
            $stmt->execute([$total_order_amount, $order_id]);
            
            // Create an alert for the new medicine order
            $delivery_text = $delivery_requested ? " with delivery to your address" : "";
            $this->createAlert(
                "Medicine Order Placed",
                "Your medicine order #$order_number has been placed with {$pharmacy['name']}$delivery_text. Total amount: $" . number_format($total_order_amount, 2) . ". Please proceed with payment.",
                'medicine_order',
                $order_id
            );
            
            // Commit transaction
            $this->db->commit();
            
            $_SESSION['success'] = "Medicine order placed successfully. Please proceed with payment.";
            // Redirect to payment page instead of order details
            $this->redirect('index.php?module=patient&action=payment&type=medicine_order&id=' . $order_id);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error processing medicine order: " . $e->getMessage());
            $_SESSION['error'] = "Error processing medicine order: " . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            $this->redirect('index.php?module=patient&action=order_medicine');
        }
    }

    /**
     * Book a cab for hospital visit
     */
    public function book_cab() {
        try {
            // Check if cab_bookings table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'cab_bookings'");
            if ($stmt->rowCount() == 0) {
                // Create cab_bookings table
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS `cab_bookings` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `patient_id` int(11) NOT NULL,
                      `provider_id` int(11) NULL,
                      `pickup_address` text NOT NULL,
                      `destination` text NOT NULL,
                      `pickup_datetime` datetime NOT NULL,
                      `cab_type` enum('standard','wheelchair','stretcher') NOT NULL DEFAULT 'standard',
                      `special_requirements` text DEFAULT NULL,
                      `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
                      `created_at` datetime NOT NULL,
                      `updated_at` datetime DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `patient_id` (`patient_id`),
                      KEY `provider_id` (`provider_id`),
                      CONSTRAINT `cab_bookings_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
                      CONSTRAINT `cab_bookings_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
                error_log("Created cab_bookings table");
            } else {
                // Check if provider_id column exists
                $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'provider_id'");
                if ($stmt->rowCount() == 0) {
                    // Add provider_id column
                    $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN provider_id INT(11) NULL AFTER patient_id, ADD KEY `provider_id` (`provider_id`), ADD CONSTRAINT `cab_bookings_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`id`) ON DELETE SET NULL");
                    error_log("Added provider_id column to cab_bookings table");
                }
            }
            
            // Check if hospitals table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'hospitals'");
            if ($stmt->rowCount() == 0) {
                // Create hospitals table
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS `hospitals` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `name` varchar(100) NOT NULL,
                      `address` text NOT NULL,
                      `phone` varchar(20) NOT NULL,
                      `email` varchar(100) DEFAULT NULL,
                      `website` varchar(100) DEFAULT NULL,
                      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                      `created_at` datetime NOT NULL,
                      `updated_at` datetime DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
                
                // Insert sample hospitals
                $this->db->exec("
                    INSERT INTO `hospitals` (`name`, `address`, `phone`, `email`, `website`, `status`, `created_at`) VALUES
                    ('City General Hospital', '123 Main Street, City Center', '555-1234', 'info@citygeneral.com', 'www.citygeneral.com', 'active', NOW()),
                    ('Memorial Medical Center', '456 Park Avenue, Downtown', '555-5678', 'contact@memorialmed.com', 'www.memorialmed.com', 'active', NOW()),
                    ('St. John\'s Hospital', '789 Oak Road, Westside', '555-9012', 'info@stjohns.com', 'www.stjohns.com', 'active', NOW())
                ");
                
                error_log("Created hospitals table and inserted sample data");
            }
            
            // If form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate and sanitize input
                $pickup_address = filter_var($_POST['pickup_address'], FILTER_SANITIZE_STRING);
                $destination = filter_var($_POST['destination'], FILTER_SANITIZE_STRING);
                $pickup_time = filter_var($_POST['pickup_time'], FILTER_SANITIZE_STRING);
                $pickup_date = filter_var($_POST['pickup_date'], FILTER_SANITIZE_STRING);
                $cab_type = filter_var($_POST['cab_type'], FILTER_SANITIZE_STRING);
                $special_requirements = filter_var($_POST['special_requirements'], FILTER_SANITIZE_STRING);
                $provider_id = isset($_POST['provider_id']) ? filter_var($_POST['provider_id'], FILTER_SANITIZE_NUMBER_INT) : null;
                
                // Validate required fields
                if (empty($pickup_address) || empty($destination) || empty($pickup_time) || empty($pickup_date) || empty($cab_type) || empty($provider_id)) {
                    $_SESSION['error'] = "Please fill in all required fields including selecting a transportation provider.";
                    $this->redirect('index.php?module=patient&action=book_cab');
                    return;
                }
                
                // Validate pickup date and time (must be in the future)
                $pickup_datetime = strtotime($pickup_date . ' ' . $pickup_time);
                if ($pickup_datetime < time()) {
                    $_SESSION['error'] = "Pickup date and time must be in the future.";
                    $this->redirect('index.php?module=patient&action=book_cab');
                    return;
                }
                
                // Create cab booking
                $stmt = $this->db->prepare("
                    INSERT INTO cab_bookings (
                        patient_id, provider_id, pickup_address, destination, 
                        pickup_datetime, cab_type, special_requirements, 
                        status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    $this->patient['id'],
                    $provider_id,
                    $pickup_address, 
                    $destination, 
                    date('Y-m-d H:i:s', $pickup_datetime), 
                    $cab_type, 
                    $special_requirements
                ]);
                
                $booking_id = $this->db->lastInsertId();
                
                // Calculate estimated fare based on cab type
                $base_fare = 0;
                switch ($cab_type) {
                    case 'standard':
                        $base_fare = 150.00;
                        break;
                    case 'wheelchair':
                        $base_fare = 250.00;
                        break;
                    case 'stretcher':
                        $base_fare = 350.00;
                        break;
                }
                
                // Add estimated_fare column if it doesn't exist
                $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'estimated_fare'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN estimated_fare DECIMAL(10,2) DEFAULT 0.00");
                }
                
                // Add payment_status column if it doesn't exist
                $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'payment_status'");
                if ($stmt->rowCount() == 0) {
                    $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
                }
                
                // Update booking with estimated fare
                $stmt = $this->db->prepare("
                    UPDATE cab_bookings 
                    SET estimated_fare = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$base_fare, $booking_id]);
                
                // Get provider details
                $stmt = $this->db->prepare("
                    SELECT company_name FROM service_providers WHERE id = ?
                ");
                $stmt->execute([$provider_id]);
                $provider = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Create alert for cab booking
                $pickup_datetime_formatted = date("F j, Y, g:i a", $pickup_datetime);
                $this->createAlert(
                    "Cab Booking Created",
                    "You have booked a {$cab_type} cab with {$provider['company_name']} for {$pickup_datetime_formatted}. Estimated fare: $" . number_format($base_fare, 2) . ". Please proceed with payment.",
                    'cab_booking',
                    $booking_id
                );
                
                $_SESSION['success'] = "Cab booked successfully. Please proceed with payment.";
                $this->redirect('index.php?module=patient&action=payment&type=cab_booking&id=' . $booking_id);
                return;
            }
            
            // Get hospital addresses for destination options
            $stmt = $this->db->prepare("
                SELECT id, name, address 
                FROM hospitals 
                WHERE status = 'active'
                ORDER BY name
            ");
            $stmt->execute();
            $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get list of transportation service providers
            $stmt = $this->db->prepare("
                SELECT sp.id, sp.company_name, sp.phone, sp.address, sp.operating_hours, sp.service_area
                FROM service_providers sp
                WHERE sp.service_type IN ('transportation', 'both')
                ORDER BY sp.company_name
            ");
            $stmt->execute();
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Book Cab - Patient: " . print_r($this->patient, true));
            error_log("Book Cab - Hospitals: " . print_r($hospitals, true));
            error_log("Book Cab - Providers: " . print_r($providers, true));
            
            // Render the book cab form
            $this->render('book_cab', [
                'patient' => $this->patient,
                'hospitals' => $hospitals,
                'providers' => $providers
            ]);
        } catch (PDOException $e) {
            error_log("Error in book_cab: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while processing your request.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * View patient profile
     */
    public function profile() {
        try {
            // Get patient details with user information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email, u.status as user_status
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->patient['id']]);
            $patient_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient_details) {
                $_SESSION['error'] = "Patient profile not found.";
                $this->redirect('index.php?module=patient&action=dashboard');
                return;
            }
            
            // Render the profile view
            $this->render('profile', [
                'patient' => $patient_details
            ]);
        } catch (PDOException $e) {
            error_log("Error in profile: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your profile.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Edit patient profile
     */
    public function edit_profile() {
        try {
            // If form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate and sanitize input
                $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
                $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
                $dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);
                $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
                $blood_group = filter_var($_POST['blood_group'], FILTER_SANITIZE_STRING);
                $emergency_contact = filter_var($_POST['emergency_contact'], FILTER_SANITIZE_STRING);
                $medical_history = filter_var($_POST['medical_history'], FILTER_SANITIZE_STRING);
                
                // Validate required fields
                if (empty($name) || empty($phone)) {
                    $_SESSION['error'] = "Name and phone number are required.";
                    $this->redirect('index.php?module=patient&action=edit_profile');
                    return;
                }
                
                // Handle profile image upload
                $profile_image = null;
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 2 * 1024 * 1024; // 2MB
                    
                    // Validate file type
                    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                        $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed.";
                        $this->redirect('index.php?module=patient&action=edit_profile');
                        return;
                    }
                    
                    // Validate file size
                    if ($_FILES['profile_image']['size'] > $max_size) {
                        $_SESSION['error'] = "Image size should not exceed 2MB.";
                        $this->redirect('index.php?module=patient&action=edit_profile');
                        return;
                    }
                    
                    // Create uploads directory if it doesn't exist
                    $upload_dir = __DIR__ . '/../../../uploads/profile_images/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'patient_' . $this->patient['id'] . '_' . time() . '.' . $file_extension;
                    $target_file = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                        $profile_image = 'uploads/profile_images/' . $filename;
                        
                        // Delete old profile image if exists
                        if (!empty($this->patient['profile_image']) && file_exists(__DIR__ . '/../../../' . $this->patient['profile_image'])) {
                            unlink(__DIR__ . '/../../../' . $this->patient['profile_image']);
                        }
                    } else {
                        $_SESSION['error'] = "Failed to upload image. Please try again.";
                        $this->redirect('index.php?module=patient&action=edit_profile');
                        return;
                    }
                }
                
                // Check if patients table has profile_image column
                $stmt = $this->db->query("SHOW COLUMNS FROM patients LIKE 'profile_image'");
                if ($stmt->rowCount() === 0) {
                    // Add profile_image column to patients table
                    $this->db->exec("ALTER TABLE patients ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
                }
                
                // Update patient details
                $sql = "
                    UPDATE patients SET
                        name = ?,
                        phone = ?,
                        address = ?,
                        dob = ?,
                        gender = ?,
                        blood_group = ?,
                        emergency_contact = ?,
                        medical_history = ?,
                        updated_at = NOW()
                ";
                $params = [
                    $name,
                    $phone,
                    $address,
                    $dob,
                    $gender,
                    $blood_group,
                    $emergency_contact,
                    $medical_history
                ];
                
                // Add profile_image to update if uploaded
                if ($profile_image !== null) {
                    $sql .= ", profile_image = ?";
                    $params[] = $profile_image;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $this->patient['id'];
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                
                // Update session data
                $this->patient['name'] = $name;
                $this->patient['phone'] = $phone;
                $this->patient['address'] = $address;
                $this->patient['dob'] = $dob;
                $this->patient['gender'] = $gender;
                $this->patient['blood_group'] = $blood_group;
                $this->patient['emergency_contact'] = $emergency_contact;
                $this->patient['medical_history'] = $medical_history;
                if ($profile_image !== null) {
                    $this->patient['profile_image'] = $profile_image;
                    $_SESSION['profile_image'] = $profile_image;
                }
                
                $_SESSION['success'] = "Profile updated successfully.";
                $this->redirect('index.php?module=patient&action=profile');
                return;
            }
            
            // Get patient details with user information
            $stmt = $this->db->prepare("
                SELECT p.*, u.email, u.status as user_status
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->patient['id']]);
            $patient_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient_details) {
                $_SESSION['error'] = "Patient profile not found.";
                $this->redirect('index.php?module=patient&action=dashboard');
                return;
            }
            
            // Render the edit profile form
            $this->render('edit_profile', [
                'patient' => $patient_details
            ]);
        } catch (PDOException $e) {
            error_log("Error in edit_profile: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating your profile.";
            $this->redirect('index.php?module=patient&action=profile');
        }
    }

    /**
     * View patient health records
     */
    public function health_records() {
        try {
            // Get medical history records for the patient
            $stmt = $this->db->prepare("
                SELECT mh.*, u.name as recorded_by_name
                FROM medical_history mh
                LEFT JOIN users u ON mh.recorded_by = u.id
                WHERE mh.patient_id = ?
                ORDER BY mh.recorded_date DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get patient details
            $stmt = $this->db->prepare("
                SELECT p.*, u.email
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$this->patient['id']]);
            $patient_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Render the health records view
            $this->render('health_records', [
                'patient' => $patient_details,
                'medical_records' => $medical_records
            ]);
        } catch (PDOException $e) {
            error_log("Error in health_records: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your health records.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * View medicine order details
     */
    public function view_medicine_order() {
        try {
            $order_id = intval($_GET['id'] ?? 0);
            
            if ($order_id <= 0) {
                $_SESSION['error'] = "Invalid order ID";
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // Get order details
            $stmt = $this->db->prepare("
                SELECT mo.*, p.name as pharmacy_name, p.phone as pharmacy_phone,
                       p.address as pharmacy_address, pr.diagnosis as prescription_diagnosis
                FROM medicine_orders mo
                JOIN pharmacies p ON mo.pharmacy_id = p.id
                LEFT JOIN prescriptions pr ON mo.prescription_id = pr.id
                WHERE mo.id = ? AND mo.patient_id = ?
            ");
            $stmt->execute([$order_id, $this->patient['id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                $_SESSION['error'] = "Order not found or you do not have permission to view it";
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // Get order items
            $stmt = $this->db->prepare("
                SELECT * FROM medicine_order_items
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('view_medicine_order', [
                'page_title' => 'Order Details',
                'order' => $order,
                'items' => $items
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading order details: " . $e->getMessage());
            $_SESSION['error'] = "Error loading order details";
            $this->redirect('index.php?module=patient&action=order_medicine');
        }
    }
    
    /**
     * Cancel medicine order
     */
    public function cancel_medicine_order() {
        try {
            $order_id = intval($_GET['id'] ?? 0);
            
            if ($order_id <= 0) {
                $_SESSION['error'] = "Invalid order ID";
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // Verify order belongs to this patient and is in a cancellable state
            $stmt = $this->db->prepare("
                SELECT mo.id, mo.order_number, mo.order_status, p.name as pharmacy_name 
                FROM medicine_orders mo
                JOIN pharmacies p ON mo.pharmacy_id = p.id
                WHERE mo.id = ? AND mo.patient_id = ? AND mo.order_status IN ('pending', 'processing')
            ");
            $stmt->execute([$order_id, $this->patient['id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                $_SESSION['error'] = "Order not found, already processed, or you do not have permission to cancel it";
                $this->redirect('index.php?module=patient&action=order_medicine');
                return;
            }
            
            // Update order status
            $stmt = $this->db->prepare("
                UPDATE medicine_orders
                SET order_status = 'cancelled', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$order_id]);
            
            // Create alert for cancelled order
            $this->createAlert(
                "Medicine Order Cancelled",
                "Your medicine order #{$order['order_number']} from {$order['pharmacy_name']} has been cancelled.",
                'medicine_order',
                $order_id
            );

            $_SESSION['success'] = "Order cancelled successfully";
            $this->redirect('index.php?module=patient&action=order_medicine');
            
        } catch (Exception $e) {
            $this->logError("Error cancelling order: " . $e->getMessage());
            $_SESSION['error'] = "Error cancelling order";
            $this->redirect('index.php?module=patient&action=order_medicine');
        }
    }

    /**
     * Get medicine price
     */
    public function get_medicine_price() {
        try {
            // Get medicine name from request
            $medicine_name = trim($_GET['medicine'] ?? '');
            
            if (empty($medicine_name)) {
                http_response_code(400);
                echo json_encode(['error' => 'Medicine name is required']);
                return;
            }
            
            // Get pharmacy ID from request
            $pharmacy_id = intval($_GET['pharmacy_id'] ?? 0);
            
            if ($pharmacy_id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Pharmacy ID is required']);
                return;
            }
            
            // Get medicine price from database
            $stmt = $this->db->prepare("
                SELECT price 
                FROM medicines 
                WHERE name = ? AND pharmacy_id = ? AND status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$medicine_name, $pharmacy_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If medicine not found, return default price
            $price = $result ? floatval($result['price']) : 10.00;
            
            // Return price as JSON
            header('Content-Type: application/json');
            echo json_encode(['price' => $price]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error fetching medicine price']);
        }
    }

    /**
     * Show payment page
     */
    public function payment() {
        try {
            // Initialize PaymentController
            require_once __DIR__ . '/controllers/PaymentController.php';
            $paymentController = new PaymentController($this->db, $this->patient);
            
            // Show payment page
            $paymentController->showPaymentPage();
            
        } catch (Exception $e) {
            $this->logError("Error showing payment page: " . $e->getMessage());
            $_SESSION['error'] = "Error showing payment page: " . $e->getMessage();
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Process payment
     */
    public function process_payment() {
        try {
            // Initialize PaymentController
            require_once __DIR__ . '/controllers/PaymentController.php';
            $paymentController = new PaymentController($this->db, $this->patient);
            
            // Process payment but keep appointment status as pending
            $type = $_GET['type'] ?? '';
            $id = intval($_GET['id'] ?? 0);
            
            if ($type === 'appointment') {
                // Begin transaction
                $this->db->beginTransaction();
                
                try {
                    // Update only payment_status, keep appointment status as pending
                    $stmt = $this->db->prepare("
                        UPDATE appointments 
                        SET payment_status = 'paid'
                        WHERE id = ? AND patient_id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$id, $this->patient['id']]);
                    
                    // Get appointment details
                    $stmt = $this->db->prepare("
                        SELECT a.appointment_date, d.name as doctor_name, d.specialization
                        FROM appointments a
                        JOIN doctors d ON a.doctor_id = d.id
                        WHERE a.id = ?
                    ");
                    $stmt->execute([$id]);
                    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Create payment alert
                    if ($appointment) {
                        $appointment_date_formatted = date("F j, Y, g:i a", strtotime($appointment['appointment_date']));
                        $this->createAlert(
                            "Payment Confirmed for Appointment",
                            "Your payment for the appointment with Dr. {$appointment['doctor_name']} ({$appointment['specialization']}) scheduled for {$appointment_date_formatted} has been confirmed. The appointment is waiting for doctor's confirmation.",
                            'payment',
                            $id
                        );
                    }
                    
                    $this->db->commit();
                    $_SESSION['success'] = "Payment successful. Your appointment is pending doctor's confirmation.";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            } else if ($type === 'medicine_order') {
                // Begin transaction
                $this->db->beginTransaction();
                
                try {
                    // Update payment status for medicine order
                    $stmt = $this->db->prepare("
                        UPDATE medicine_orders 
                        SET payment_status = 'paid', order_status = 'processing'
                        WHERE id = ? AND patient_id = ?
                    ");
                    $stmt->execute([$id, $this->patient['id']]);
                    
                    // Get order details
                    $stmt = $this->db->prepare("
                        SELECT mo.order_number, mo.total_amount, p.name as pharmacy_name
                        FROM medicine_orders mo
                        JOIN pharmacies p ON mo.pharmacy_id = p.id
                        WHERE mo.id = ?
                    ");
                    $stmt->execute([$id]);
                    $order = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Create payment alert
                    if ($order) {
                        $this->createAlert(
                            "Payment Confirmed for Medicine Order",
                            "Your payment of $" . number_format($order['total_amount'], 2) . " for medicine order #{$order['order_number']} from {$order['pharmacy_name']} has been confirmed. Your order is now being processed.",
                            'payment',
                            $id
                        );
                    }
                    
                    $this->db->commit();
                    $_SESSION['success'] = "Payment successful. Your medicine order is being processed.";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            } else if ($type === 'cab_booking') {
                // Begin transaction
                $this->db->beginTransaction();
                
                try {
                    // Update payment status for cab booking
                    $stmt = $this->db->prepare("
                        UPDATE cab_bookings 
                        SET payment_status = 'paid', status = 'confirmed'
                        WHERE id = ? AND patient_id = ?
                    ");
                    $stmt->execute([$id, $this->patient['id']]);
                    
                    // Get booking details
                    $stmt = $this->db->prepare("
                        SELECT cb.pickup_datetime, cb.estimated_fare, cb.pickup_address, cb.destination, sp.company_name as provider_name
                        FROM cab_bookings cb
                        JOIN service_providers sp ON cb.provider_id = sp.id
                        WHERE cb.id = ?
                    ");
                    $stmt->execute([$id]);
                    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Create payment alert
                    if ($booking) {
                        $pickup_datetime_formatted = date("F j, Y, g:i a", strtotime($booking['pickup_datetime']));
                        $this->createAlert(
                            "Payment Confirmed for Cab Booking",
                            "Your payment of $" . number_format($booking['estimated_fare'], 2) . " for cab booking with {$booking['provider_name']} scheduled for {$pickup_datetime_formatted} has been confirmed. Your booking is now confirmed.",
                            'payment',
                            $id
                        );
                    }
                    
                    $this->db->commit();
                    $_SESSION['success'] = "Payment successful. Your cab booking is confirmed.";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            } else {
                // Handle other payment types
                $paymentController->processPayment();
            }
            
        } catch (Exception $e) {
            $this->logError("Error processing payment: " . $e->getMessage());
            $_SESSION['error'] = "Error processing payment: " . $e->getMessage();
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Show payment history
     */
    public function payment_history() {
        try {
            // Initialize PaymentController
            require_once __DIR__ . '/controllers/PaymentController.php';
            $paymentController = new PaymentController($this->db, $this->patient);
            
            // Show payment history
            $paymentController->showPaymentHistory();
            
        } catch (Exception $e) {
            $this->logError("Error showing payment history: " . $e->getMessage());
            $_SESSION['error'] = "Error showing payment history: " . $e->getMessage();
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * Show symptom search page
     */
    public function symptom_search() {
        try {
            // Check if symptoms table exists
            $tableExists = false;
            try {
                $stmt = $this->db->prepare("SELECT 1 FROM symptoms LIMIT 1");
                $stmt->execute();
                $tableExists = true;
            } catch (PDOException $e) {
                // Table doesn't exist
                $tableExists = false;
            }
            
            // If tables don't exist, try to create them and import sample data
            if (!$tableExists) {
                try {
                    // Create tables if they don't exist
                    $this->db->exec("
                        CREATE TABLE IF NOT EXISTS `symptoms` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL,
                          `description` text,
                          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `name` (`name`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                        
                        CREATE TABLE IF NOT EXISTS `diseases` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL,
                          `description` text,
                          `treatment` text,
                          `severity_level` enum('low','medium','high') DEFAULT 'medium',
                          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `name` (`name`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                        
                        CREATE TABLE IF NOT EXISTS `disease_symptoms` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `disease_id` int NOT NULL,
                          `symptom_id` int NOT NULL,
                          `severity` enum('mild','moderate','severe') DEFAULT 'moderate',
                          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `disease_symptom_unique` (`disease_id`,`symptom_id`),
                          KEY `symptom_id` (`symptom_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                        
                        CREATE TABLE IF NOT EXISTS `disease_specializations` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `disease_id` int NOT NULL,
                          `specialization` varchar(255) NOT NULL,
                          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `disease_specialization_unique` (`disease_id`,`specialization`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                        
                        CREATE TABLE IF NOT EXISTS `symptom_search_history` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `patient_id` int NOT NULL,
                          `symptoms` text NOT NULL,
                          `diseases_found` text,
                          `recommended_doctors` text,
                          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (`id`),
                          KEY `patient_id` (`patient_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                    ");
                    
                    // Add foreign key constraints
                    $this->db->exec("
                        ALTER TABLE `disease_symptoms`
                          ADD CONSTRAINT `disease_symptoms_ibfk_1` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE,
                          ADD CONSTRAINT `disease_symptoms_ibfk_2` FOREIGN KEY (`symptom_id`) REFERENCES `symptoms` (`id`) ON DELETE CASCADE;
                        
                        ALTER TABLE `disease_specializations`
                          ADD CONSTRAINT `disease_specializations_ibfk_1` FOREIGN KEY (`disease_id`) REFERENCES `diseases` (`id`) ON DELETE CASCADE;
                        
                        ALTER TABLE `symptom_search_history`
                          ADD CONSTRAINT `symptom_search_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
                    ");
                    
                    // Import sample data
                    $sampleDataPath = __DIR__ . '/../../sql/sample_symptoms_diseases.sql';
                    if (file_exists($sampleDataPath)) {
                        $sql = file_get_contents($sampleDataPath);
                        $this->db->exec($sql);
                        $_SESSION['success'] = "Symptom search functionality has been set up with sample data.";
                    }
                } catch (PDOException $e) {
                    error_log("Error creating symptom tables: " . $e->getMessage());
                    // Continue with empty symptoms
                }
            }
            
            // Check if symptoms table has data
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM symptoms");
            $stmt->execute();
            $symptomCount = $stmt->fetchColumn();
            
            if ($symptomCount == 0) {
                // No symptoms in database, try to import sample data
                try {
                    $sampleDataPath = __DIR__ . '/../../sql/sample_symptoms_diseases.sql';
                    if (file_exists($sampleDataPath)) {
                        $sql = file_get_contents($sampleDataPath);
                        $this->db->exec($sql);
                        $_SESSION['success'] = "Sample symptom and disease data has been imported.";
                    }
                } catch (PDOException $e) {
                    error_log("Error importing sample data: " . $e->getMessage());
                    // Continue with empty symptoms
                }
            }
            
            // Get common symptoms for autocomplete
            $stmt = $this->db->prepare("
                SELECT name, description 
                FROM symptoms 
                ORDER BY name 
                LIMIT 50
            ");
            $stmt->execute();
            $common_symptoms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get patient's recent symptom searches
            $stmt = $this->db->prepare("
                SELECT * FROM symptom_search_history 
                WHERE patient_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$this->patient['id']]);
            $recent_searches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Render the symptom search view
            $this->render('symptom_search', [
                'patient' => $this->patient,
                'common_symptoms' => $common_symptoms,
                'recent_searches' => $recent_searches
            ]);
        } catch (PDOException $e) {
            error_log("Error in symptom_search: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading the symptom search page.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }

    /**
     * Display cab bookings for patient
     */
    public function cab_bookings() {
        // Check if user is logged in
        if (!$this->isAuthenticated()) {
            $_SESSION['error'] = "Please log in to view your bookings.";
            $this->redirect('index.php?module=auth&action=login&type=patient');
        }
        
        try {
            // Get all cab bookings for the patient
            $stmt = $this->db->prepare("
                SELECT cb.*, sp.company_name as provider_name 
                FROM cab_bookings cb 
                LEFT JOIN service_providers sp ON cb.provider_id = sp.id 
                WHERE cb.patient_id = ?
                ORDER BY cb.pickup_datetime DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format dates and prepare data for view
            foreach ($bookings as &$booking) {
                if (isset($booking['pickup_datetime'])) {
                    $pickup_datetime = new DateTime($booking['pickup_datetime']);
                    $booking['pickup_date'] = $pickup_datetime->format('Y-m-d');
                    $booking['pickup_time'] = $pickup_datetime->format('H:i:s');
                    $booking['formatted_date'] = $pickup_datetime->format('M j, Y');
                    $booking['formatted_time'] = $pickup_datetime->format('g:i A');
                }
            }
            
            // Render the cab bookings view
            $this->render('cab_bookings', [
                'patient' => $this->patient,
                'bookings' => $bookings
            ]);
        } catch (PDOException $e) {
            error_log("Error in cab_bookings: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your cab bookings.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * View cab booking details
     */
    public function view_cab_booking() {
        // Check if user is logged in
        if (!$this->isAuthenticated()) {
            $_SESSION['error'] = "Please log in to view booking details.";
            $this->redirect('index.php?module=auth&action=login&type=patient');
        }
        
        // Get booking ID
        $booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$booking_id) {
            $_SESSION['error'] = 'Invalid booking ID.';
            $this->redirect('index.php?module=patient&action=cab_bookings');
        }
        
        try {
            // Get booking details
            $stmt = $this->db->prepare("
                SELECT cb.*, sp.company_name as provider_name, sp.phone as provider_phone
                FROM cab_bookings cb
                LEFT JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.id = ? AND cb.patient_id = ?
            ");
            $stmt->execute([$booking_id, $this->patient['id']]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                $_SESSION['error'] = 'Booking not found or you do not have permission to view it.';
                $this->redirect('index.php?module=patient&action=cab_bookings');
            }
            
            // Format pickup date and time from pickup_datetime
            if (isset($booking['pickup_datetime'])) {
                $pickup_datetime = new DateTime($booking['pickup_datetime']);
                $booking['pickup_date'] = $pickup_datetime->format('Y-m-d');
                $booking['pickup_time'] = $pickup_datetime->format('H:i:s');
            }
            
            // Load view
            $this->render('view_cab_booking', [
                'patient' => $this->patient,
                'booking' => $booking
            ]);
        } catch (Exception $e) {
            $this->logError('Error viewing cab booking: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while retrieving booking details.';
            $this->redirect('index.php?module=patient&action=cab_bookings');
        }
    }
    
    /**
     * Cancel cab booking
     */
    public function cancel_cab_booking() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("Booking ID not provided");
            }
            
            $booking_id = (int)$_GET['id'];
            
            // Check if booking exists and belongs to the patient
            $stmt = $this->db->prepare("
                SELECT cb.id, cb.status, cb.pickup_datetime, cb.cab_type, sp.company_name
                FROM cab_bookings cb
                JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.id = ? AND cb.patient_id = ?
            ");
            $stmt->execute([$booking_id, $this->patient['id']]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                throw new Exception("Booking not found or you don't have permission to cancel it");
            }
            
            if ($booking['status'] == 'cancelled' || $booking['status'] == 'completed') {
                throw new Exception("Cannot cancel a booking that is already " . $booking['status']);
            }
            
            // Update booking status to cancelled
            $stmt = $this->db->prepare("
                UPDATE cab_bookings
                SET status = 'cancelled', cancelled_at = NOW()
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$booking_id, $this->patient['id']]);
            
            // Create alert for cancelled booking
            $pickup_datetime_formatted = date("F j, Y, g:i a", strtotime($booking['pickup_datetime']));
            $this->createAlert(
                "Cab Booking Cancelled",
                "Your {$booking['cab_type']} cab booking with {$booking['company_name']} scheduled for {$pickup_datetime_formatted} has been cancelled.",
                'cab_booking',
                $booking_id
            );
            
            $_SESSION['success'] = "Cab booking cancelled successfully";
            $this->redirect('index.php?module=patient&action=cab_bookings');
        } catch (Exception $e) {
            error_log("Error in cancel_cab_booking: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('index.php?module=patient&action=cab_bookings');
        }
    }

    /**
     * Display all alerts for the patient
     */
    public function alerts() {
        try {
            // Check if patient_alerts table exists, if not create it
            $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
            if ($stmt->rowCount() == 0) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS `patient_alerts` (
                      `id` int NOT NULL AUTO_INCREMENT,
                      `patient_id` int NOT NULL,
                      `title` varchar(255) NOT NULL,
                      `message` text NOT NULL,
                      `alert_type` enum('appointment','medicine_order','cab_booking','prescription','payment','system','patient_issue') NOT NULL DEFAULT 'system',
                      `reference_id` int DEFAULT NULL,
                      `is_read` tinyint(1) DEFAULT '0',
                      `read_at` timestamp NULL DEFAULT NULL,
                      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      KEY `patient_id` (`patient_id`),
                      KEY `idx_alert_type_reference` (`alert_type`,`reference_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                ");
                
                // Add foreign key if possible
                try {
                    $this->db->exec("
                        ALTER TABLE `patient_alerts`
                        ADD CONSTRAINT `patient_alerts_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
                    ");
                } catch (PDOException $e) {
                    // Foreign key could not be added, continue anyway
                    error_log("Could not add foreign key to patient_alerts: " . $e->getMessage());
                }
            } else {
                // Check if patient_issue is already in the enum
                $stmt = $this->db->query("SHOW COLUMNS FROM patient_alerts LIKE 'alert_type'");
                $column = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($column && strpos($column['Type'], 'patient_issue') === false) {
                    // Extract enum values from the type definition
                    preg_match("/^enum\(\'(.*)\'\)$/", $column['Type'], $matches);
                    if (isset($matches[1])) {
                        $enum_values = explode("','", $matches[1]);
                        
                        // Add 'patient_issue' to enum if not present
                        if (!in_array('patient_issue', $enum_values)) {
                            $enum_values[] = 'patient_issue';
                            $new_enum = "enum('" . implode("','", $enum_values) . "')";
                            
                            // Alter the table
                            $this->db->exec("ALTER TABLE patient_alerts MODIFY COLUMN alert_type {$new_enum} NOT NULL DEFAULT 'system'");
                        }
                    }
                }
            }
            
            // Get all alerts for the patient
            $stmt = $this->db->prepare("
                SELECT * FROM patient_alerts
                WHERE patient_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$this->patient['id']]);
            $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get unread alert count
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM patient_alerts
                WHERE patient_id = ? AND is_read = 0
            ");
            $stmt->execute([$this->patient['id']]);
            $unread_count = $stmt->fetchColumn();
            
            // Render the alerts view
            $this->render('alerts', [
                'patient' => $this->patient,
                'alerts' => $alerts,
                'unread_count' => $unread_count
            ]);
        } catch (PDOException $e) {
            error_log("Error in alerts: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your alerts.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Mark an alert as read
     */
    public function mark_alert_read() {
        try {
            $alert_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            
            if (!$alert_id) {
                $_SESSION['error'] = "Invalid alert ID";
                $this->redirect('index.php?module=patient&action=alerts');
                return;
            }
            
            // Update alert to mark as read
            $stmt = $this->db->prepare("
                UPDATE patient_alerts
                SET is_read = 1, read_at = NOW()
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$alert_id, $this->patient['id']]);
            
            // If from AJAX request, return JSON response
            if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            
            // If redirect is specified, go there
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                $this->redirect($_GET['redirect']);
                return;
            }
            
            // Otherwise return to alerts page
            $this->redirect('index.php?module=patient&action=alerts');
        } catch (PDOException $e) {
            error_log("Error in mark_alert_read: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while marking the alert as read.";
            
            // If from AJAX request, return JSON error
            if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => "An error occurred"]);
                exit;
            }
            
            $this->redirect('index.php?module=patient&action=alerts');
        }
    }
    
    /**
     * Mark all alerts as read
     */
    public function mark_all_read() {
        try {
            // Update all alerts to mark as read
            $stmt = $this->db->prepare("
                UPDATE patient_alerts
                SET is_read = 1, read_at = NOW()
                WHERE patient_id = ? AND is_read = 0
            ");
            $stmt->execute([$this->patient['id']]);
            
            $_SESSION['success'] = "All alerts marked as read";
            $this->redirect('index.php?module=patient&action=alerts');
        } catch (PDOException $e) {
            error_log("Error in mark_all_read: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while marking alerts as read.";
            $this->redirect('index.php?module=patient&action=alerts');
        }
    }
    
    /**
     * Helper method to create a new alert for the patient
     * 
     * @param string $title Alert title
     * @param string $message Alert message
     * @param string $alert_type Type of alert (appointment, medicine_order, etc)
     * @param int|null $reference_id ID of the referenced entity
     * @return int|bool The ID of the created alert or false on failure
     */
    private function createAlert($title, $message, $alert_type = 'system', $reference_id = null) {
        try {
            // Check if patient_alerts table exists, if not create it
            $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
            if ($stmt->rowCount() == 0) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS `patient_alerts` (
                      `id` int NOT NULL AUTO_INCREMENT,
                      `patient_id` int NOT NULL,
                      `title` varchar(255) NOT NULL,
                      `message` text NOT NULL,
                      `alert_type` enum('appointment','medicine_order','cab_booking','prescription','payment','system','patient_issue') NOT NULL DEFAULT 'system',
                      `reference_id` int DEFAULT NULL,
                      `is_read` tinyint(1) DEFAULT '0',
                      `read_at` timestamp NULL DEFAULT NULL,
                      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      KEY `patient_id` (`patient_id`),
                      KEY `idx_alert_type_reference` (`alert_type`,`reference_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                ");
            } else {
                // Check if patient_issue is already in the enum
                $stmt = $this->db->query("SHOW COLUMNS FROM patient_alerts LIKE 'alert_type'");
                $column = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($column && strpos($column['Type'], 'patient_issue') === false) {
                    // Extract enum values from the type definition
                    preg_match("/^enum\(\'(.*)\'\)$/", $column['Type'], $matches);
                    if (isset($matches[1])) {
                        $enum_values = explode("','", $matches[1]);
                        
                        // Add 'patient_issue' to enum if not present
                        if (!in_array('patient_issue', $enum_values)) {
                            $enum_values[] = 'patient_issue';
                            $new_enum = "enum('" . implode("','", $enum_values) . "')";
                            
                            // Alter the table
                            $this->db->exec("ALTER TABLE patient_alerts MODIFY COLUMN alert_type {$new_enum} NOT NULL DEFAULT 'system'");
                        }
                    }
                }
            }
            
            // Insert the alert
            $stmt = $this->db->prepare("
                INSERT INTO patient_alerts (
                    patient_id, title, message, alert_type, reference_id
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $this->patient['id'],
                $title,
                $message,
                $alert_type,
                $reference_id
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating alert: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Display report issue form
     */
    public function report_issue() {
        try {
            // Check if patient_issues table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'patient_issues'");
            $table_exists = ($stmt->rowCount() > 0);
            
            if (!$table_exists) {
                // Create the table if it doesn't exist
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS `patient_issues` (
                      `id` int NOT NULL AUTO_INCREMENT,
                      `patient_id` int NOT NULL,
                      `issue_type` enum('medicine_delivery','cab_service','appointment','pharmacy','doctor','other') NOT NULL,
                      `reference_id` int DEFAULT NULL,
                      `title` varchar(255) NOT NULL,
                      `description` text NOT NULL,
                      `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
                      `admin_response` text DEFAULT NULL,
                      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      `resolved_at` timestamp NULL DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `patient_id` (`patient_id`),
                      KEY `issue_type_reference_id` (`issue_type`,`reference_id`),
                      CONSTRAINT `patient_issues_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
                ");
            } else {
                // Check if patient_issue is already in the enum
                $stmt = $this->db->query("SHOW COLUMNS FROM patient_issues LIKE 'issue_type'");
                $column = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($column && strpos($column['Type'], 'patient_issue') === false) {
                    // Extract enum values from the type definition
                    preg_match("/^enum\(\'(.*)\'\)$/", $column['Type'], $matches);
                    if (isset($matches[1])) {
                        $enum_values = explode("','", $matches[1]);
                        
                        // Add 'patient_issue' to enum if not present
                        if (!in_array('patient_issue', $enum_values)) {
                            $enum_values[] = 'patient_issue';
                            $new_enum = "enum('" . implode("','", $enum_values) . "')";
                            
                            // Alter the table
                            $this->db->exec("ALTER TABLE patient_issues MODIFY COLUMN issue_type {$new_enum} NOT NULL");
                        }
                    }
                }
            }
            
            // Get recent issues for the sidebar
            $stmt = $this->db->prepare("
                SELECT * FROM patient_issues
                WHERE patient_id = ?
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$this->patient['id']]);
            $recent_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('report_issue', [
                'patient' => $this->patient,
                'recent_issues' => $recent_issues
            ]);
        } catch (PDOException $e) {
            $this->logError("Error in report_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading the issue reporting form.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * Process the submission of a new issue
     */
    public function submit_issue() {
        try {
            // Validate form submission
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('index.php?module=patient&action=report_issue');
                return;
            }
            
            // Sanitize and validate inputs
            $issue_type = filter_var($_POST['issue_type'], FILTER_SANITIZE_STRING);
            $reference_id = isset($_POST['reference_id']) ? filter_var($_POST['reference_id'], FILTER_SANITIZE_NUMBER_INT) : null;
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            
            // Validate required fields
            if (empty($issue_type) || empty($title) || empty($description)) {
                $_SESSION['error'] = "Please fill in all required fields.";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=patient&action=report_issue');
                return;
            }
            
            // Insert the new issue into the database
            $stmt = $this->db->prepare("
                INSERT INTO patient_issues 
                (patient_id, issue_type, reference_id, title, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $this->patient['id'],
                $issue_type,
                $reference_id,
                $title,
                $description
            ]);
            
            $issue_id = $this->db->lastInsertId();
            
            // Create a patient alert for the submission confirmation
            try {
                $alert_title = "Issue Submitted #" . $issue_id;
                $alert_message = "Your issue '" . $title . "' has been submitted successfully. We will review it shortly.";
                
                // Create patient alert with alert_type='patient_issue'
                $this->createAlert(
                    $alert_title,
                    $alert_message,
                    'patient_issue',
                    $issue_id
                );
            } catch (Exception $e) {
                $this->logError("Error creating patient alert: " . $e->getMessage());
            }
            
            // Create an alert for the admin (if we had an admin alerts table)
            try {
                $table_exists = $this->db->query("SHOW TABLES LIKE 'admin_alerts'")->rowCount() > 0;
                if ($table_exists) {
                    $stmt = $this->db->prepare("
                        INSERT INTO admin_alerts (title, message, alert_type, reference_id, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $alert_title = "New Patient Issue #" . $issue_id;
                    $alert_message = "Patient " . $this->patient['name'] . " has submitted a new issue: " . $title;
                    $stmt->execute([$alert_title, $alert_message, 'patient_issue', $issue_id]);
                }
            } catch (Exception $e) {
                // Log but don't stop execution
                $this->logError("Error creating admin alert: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Your issue has been submitted successfully. We will review it shortly.";
            $this->redirect('index.php?module=patient&action=view_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in submit_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while submitting your issue. Please try again.";
            $_SESSION['form_data'] = $_POST;
            $this->redirect('index.php?module=patient&action=report_issue');
        }
    }
    
    /**
     * Display all issues reported by the patient
     */
    public function my_issues() {
        try {
            // Check if patient_issues table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'patient_issues'");
            $table_exists = ($stmt->rowCount() > 0);
            
            $issues = [];
            
            if ($table_exists) {
                // Get all issues for the patient
                $stmt = $this->db->prepare("
                    SELECT * FROM patient_issues
                    WHERE patient_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$this->patient['id']]);
                $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->render('my_issues', [
                'patient' => $this->patient,
                'issues' => $issues
            ]);
        } catch (PDOException $e) {
            $this->logError("Error in my_issues: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading your issues.";
            $this->redirect('index.php?module=patient&action=dashboard');
        }
    }
    
    /**
     * View details of a specific issue
     */
    public function view_issue() {
        try {
            // Validate and sanitize issue ID
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                $_SESSION['error'] = "Invalid issue ID.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            $issue_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Get issue details
            $stmt = $this->db->prepare("
                SELECT * FROM patient_issues
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$issue_id, $this->patient['id']]);
            $issue = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$issue) {
                $_SESSION['error'] = "Issue not found or you don't have permission to view it.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            // Get reference details if available
            $reference_details = null;
            if (!empty($issue['reference_id'])) {
                switch ($issue['issue_type']) {
                    case 'medicine_delivery':
                        $stmt = $this->db->prepare("
                            SELECT mo.*, ph.name as pharmacy_name
                            FROM medicine_orders mo
                            LEFT JOIN pharmacies ph ON mo.pharmacy_id = ph.id
                            WHERE mo.id = ?
                        ");
                        $stmt->execute([$issue['reference_id']]);
                        $reference_details = $stmt->fetch(PDO::FETCH_ASSOC);
                        break;
                        
                    case 'cab_service':
                        $stmt = $this->db->prepare("
                            SELECT cb.*, sp.company_name as provider_name
                            FROM cab_bookings cb
                            LEFT JOIN service_providers sp ON cb.provider_id = sp.id
                            WHERE cb.id = ?
                        ");
                        $stmt->execute([$issue['reference_id']]);
                        $reference_details = $stmt->fetch(PDO::FETCH_ASSOC);
                        break;
                        
                    case 'appointment':
                        $stmt = $this->db->prepare("
                            SELECT a.*, d.name as doctor_name
                            FROM appointments a
                            JOIN doctors d ON a.doctor_id = d.id
                            WHERE a.id = ?
                        ");
                        $stmt->execute([$issue['reference_id']]);
                        $reference_details = $stmt->fetch(PDO::FETCH_ASSOC);
                        break;
                        
                    default:
                        $reference_details = null;
                }
            }
            
            $this->render('view_issue', [
                'patient' => $this->patient,
                'issue' => $issue,
                'reference_details' => $reference_details
            ]);
        } catch (PDOException $e) {
            $this->logError("Error in view_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading the issue details.";
            $this->redirect('index.php?module=patient&action=my_issues');
        }
    }
    
    /**
     * Mark an issue as resolved by the patient
     */
    public function resolve_issue() {
        try {
            // Check if the form was submitted
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = "Invalid request method.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            // Validate and sanitize issue ID
            if (!isset($_POST['issue_id']) || !is_numeric($_POST['issue_id'])) {
                $_SESSION['error'] = "Invalid issue ID.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            $issue_id = filter_var($_POST['issue_id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Check if the issue belongs to the patient
            $stmt = $this->db->prepare("
                SELECT * FROM patient_issues
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$issue_id, $this->patient['id']]);
            $issue = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$issue) {
                $_SESSION['error'] = "Issue not found or you don't have permission to modify it.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            // Update the issue status to resolved
            $stmt = $this->db->prepare("
                UPDATE patient_issues
                SET status = 'resolved', resolved_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$issue_id]);
            
            $_SESSION['success'] = "The issue has been marked as resolved.";
            $this->redirect('index.php?module=patient&action=view_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in resolve_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while resolving the issue.";
            $this->redirect('index.php?module=patient&action=my_issues');
        }
    }
    
    /**
     * Add patient response to an issue
     */
    public function reply_to_issue() {
        try {
            // Check if the form was submitted
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = "Invalid request method.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }

            // Validate and sanitize input
            if (!isset($_POST['issue_id']) || !is_numeric($_POST['issue_id']) || empty($_POST['patient_response'])) {
                $_SESSION['error'] = "Missing required information.";
                if (isset($_POST['issue_id'])) {
                    $this->redirect('index.php?module=patient&action=view_issue&id=' . $_POST['issue_id']);
                } else {
                    $this->redirect('index.php?module=patient&action=my_issues');
                }
                return;
            }
            
            $issue_id = filter_var($_POST['issue_id'], FILTER_SANITIZE_NUMBER_INT);
            $patient_response = trim($_POST['patient_response']);
            $is_resolved = isset($_POST['is_resolved']) ? 1 : 0;
            
            // Check if the issue belongs to the patient
            $stmt = $this->db->prepare("
                SELECT * FROM patient_issues
                WHERE id = ? AND patient_id = ?
            ");
            $stmt->execute([$issue_id, $this->patient['id']]);
            $issue = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$issue) {
                $_SESSION['error'] = "Issue not found or you don't have permission to respond to it.";
                $this->redirect('index.php?module=patient&action=my_issues');
                return;
            }
            
            // Check if patient_response column exists
            $hasPatientResponseColumn = false;
            $hasPatientResponseAtColumn = false;
            
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM patient_issues LIKE 'patient_response'");
                $hasPatientResponseColumn = ($stmt->rowCount() > 0);
                
                $stmt = $this->db->query("SHOW COLUMNS FROM patient_issues LIKE 'patient_response_at'");
                $hasPatientResponseAtColumn = ($stmt->rowCount() > 0);
            } catch (Exception $e) {
                $this->logError("Error checking columns: " . $e->getMessage());
            }
            
            // If columns don't exist, give error and suggest running the update script
            if (!$hasPatientResponseColumn || !$hasPatientResponseAtColumn) {
                $_SESSION['error'] = "Database is missing required columns. Please run the update_columns.php script.";
                $this->redirect('index.php?module=patient&action=view_issue&id=' . $issue_id);
                return;
            }
            
            // Update the issue with the patient's response
            if ($is_resolved) {
                // If patient wants to mark as resolved
                $stmt = $this->db->prepare("
                    UPDATE patient_issues
                    SET patient_response = ?, patient_response_at = NOW(), 
                        status = 'resolved', resolved_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ");
            } else {
                // Just add the response without resolving
                $stmt = $this->db->prepare("
                    UPDATE patient_issues
                    SET patient_response = ?, patient_response_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ");
            }
            
            $stmt->execute([$patient_response, $issue_id]);
            
            // Create a patient alert for confirmation
            try {
                $alert_title = "Response submitted - Issue #" . $issue_id;
                $alert_message = "Your response to the issue '" . $issue['title'] . "' has been submitted successfully." . 
                    ($is_resolved ? " The issue has been marked as resolved." : "");
                
                // Create patient alert with alert_type='patient_issue'
                $this->createAlert(
                    $alert_title,
                    $alert_message,
                    'patient_issue',
                    $issue_id
                );
            } catch (Exception $e) {
                $this->logError("Error creating patient alert: " . $e->getMessage());
            }
            
            // Create a system alert for admin (if table exists)
            try {
                $table_exists = $this->db->query("SHOW TABLES LIKE 'admin_alerts'")->rowCount() > 0;
                if ($table_exists) {
                    $stmt = $this->db->prepare("
                        INSERT INTO admin_alerts (title, message, alert_type, reference_id, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $alert_title = "Patient Response - Issue #" . $issue_id;
                    $alert_message = "Patient " . $this->patient['name'] . " has responded to an issue. Issue title: " . $issue['title'];
                    $stmt->execute([$alert_title, $alert_message, 'patient_issue', $issue_id]);
                }
            } catch (Exception $e) {
                // Log but don't stop execution
                $this->logError("Error creating admin alert: " . $e->getMessage());
            }
            
            $_SESSION['success'] = "Your response has been submitted" . ($is_resolved ? " and the issue marked as resolved." : ".");
            $this->redirect('index.php?module=patient&action=view_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in reply_to_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while processing your response: " . $e->getMessage();
            
            if (isset($_POST['issue_id'])) {
                $this->redirect('index.php?module=patient&action=view_issue&id=' . $_POST['issue_id']);
            } else {
                $this->redirect('index.php?module=patient&action=my_issues');
            }
        }
    }
    
    /**
     * Get reference options for a specific issue type (AJAX endpoint)
     */
    public function get_reference_options() {
        try {
            // Ensure this is an AJAX request
            header('Content-Type: application/json');
            
            // Validate and sanitize input
            if (!isset($_GET['type'])) {
                echo json_encode(['success' => false, 'message' => 'Missing issue type']);
                return;
            }
            
            $issue_type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);
            $options = [];
            
            // Fetch appropriate reference options based on the issue type
            switch ($issue_type) {
                case 'medicine_delivery':
                    $stmt = $this->db->prepare("
                        SELECT mo.id, CONCAT('Order #', mo.order_number, ' - ', ph.name) as name
                        FROM medicine_orders mo
                        LEFT JOIN pharmacies ph ON mo.pharmacy_id = ph.id
                        WHERE mo.patient_id = ?
                        ORDER BY mo.created_at DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'cab_service':
                    $stmt = $this->db->prepare("
                        SELECT cb.id, CONCAT('Booking on ', DATE_FORMAT(cb.pickup_datetime, '%b %d, %Y')) as name
                        FROM cab_bookings cb
                        WHERE cb.patient_id = ?
                        ORDER BY cb.created_at DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'appointment':
                    $stmt = $this->db->prepare("
                        SELECT a.id, CONCAT('Dr. ', d.name, ' - ', DATE_FORMAT(a.appointment_date, '%b %d, %Y')) as name
                        FROM appointments a
                        JOIN doctors d ON a.doctor_id = d.id
                        WHERE a.patient_id = ?
                        ORDER BY a.appointment_date DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'pharmacy':
                    $stmt = $this->db->prepare("
                        SELECT DISTINCT ph.id, ph.name
                        FROM medicine_orders mo
                        JOIN pharmacies ph ON mo.pharmacy_id = ph.id
                        WHERE mo.patient_id = ?
                        ORDER BY ph.name
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'doctor':
                    $stmt = $this->db->prepare("
                        SELECT DISTINCT d.id, CONCAT('Dr. ', d.name) as name
                        FROM appointments a
                        JOIN doctors d ON a.doctor_id = d.id
                        WHERE a.patient_id = ?
                        ORDER BY d.name
                    ");
                    $stmt->execute([$this->patient['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                default:
                    $options = [];
            }
            
            echo json_encode(['success' => true, 'options' => $options]);
            
        } catch (PDOException $e) {
            $this->logError("Error in get_reference_options: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while fetching options']);
        }
    }
    
    /**
     * Chat with doctor
     * This method handles the chat interface for a specific appointment
     */
    public function chat_with_doctor() {
        try {
            $appointment_id = filter_input(INPUT_GET, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$appointment_id) {
                throw new Exception("Appointment ID is required");
            }
            
            // Check if appointment belongs to this patient and is confirmed
            $stmt = $this->db->prepare("
                SELECT a.*, d.name as doctor_name, d.specialization, d.id as doctor_id
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                WHERE a.id = ? AND a.patient_id = ?
            ");
            $stmt->execute([$appointment_id, $this->patient['id']]);
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
            
            // Mark unread messages from doctor as read
            $stmt = $this->db->prepare("
                UPDATE chat_messages
                SET is_read = 1, read_at = NOW()
                WHERE appointment_id = ? AND sender_type = 'doctor' AND is_read = 0
            ");
            $stmt->execute([$appointment_id]);
            
            // Render the chat view
            include __DIR__ . '/views/chat.php';
            
        } catch (Exception $e) {
            $this->logError("Error accessing chat: " . $e->getMessage());
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $this->redirect('index.php?module=patient&action=appointments');
        }
    }
    
    /**
     * Send message to doctor
     * This method handles sending a new message to a doctor
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

            // Get patient ID from session
            $patient_id = $_SESSION['user_id'] ?? null;
            if (!$patient_id) {
                throw new Exception("User not authenticated");
            }

            // Verify appointment belongs to this patient
            $stmt = $this->db->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
            $stmt->bind_param("ii", $appointment_id, $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Invalid appointment");
            }

            // Insert message
            $stmt = $this->db->prepare("INSERT INTO messages (appointment_id, sender_type, sender_id, message) VALUES (?, 'patient', ?, ?)");
            $stmt->bind_param("iis", $appointment_id, $patient_id, $message);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to send message");
            }

            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
