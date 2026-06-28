<?php
// Include pharmacy controller for integrated functionality
require_once __DIR__ . '/pharmacy_controller.php';

class ServiceController extends BaseController {
    protected $db;
    private $service_provider;

    public function __construct() {
        parent::__construct();
        
        try {
            $this->db = Database::getInstance();
            
            // Check authentication
            if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'service') {
                throw new Exception("Authentication required");
            }

            // Get service provider details
            $stmt = $this->db->prepare("
                SELECT sp.* 
                FROM service_providers sp
                WHERE sp.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $this->service_provider = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->service_provider) {
                throw new Exception("Service provider not found");
            }
        } catch (Exception $e) {
            error_log("Error in ServiceController constructor: " . $e->getMessage());
            $_SESSION['error'] = "Please login to continue";
            header('Location: ' . SITE_URL . 'index.php?module=auth&action=login&type=service');
            exit();
        }
    }

    public function dashboard() {
        try {
            // Get provider details with full info
            $stmt = $this->db->prepare("
                SELECT sp.*, u.email 
                FROM service_providers sp
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sp.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$provider) {
                throw new Exception("Service provider record not found");
            }

            // Get cab bookings for this provider
            $stmt = $this->db->prepare("
                SELECT cb.*, p.name as patient_name, p.phone as patient_phone
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE cb.provider_id = ? 
                ORDER BY cb.created_at DESC
            ");
            $stmt->execute([$provider['id']]);
            $cab_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get booking statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_bookings
                FROM cab_bookings 
                WHERE provider_id = ?
            ");
            $stmt->execute([$provider['id']]);
            $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Initialize stats to 0 if null
            $booking_stats['total_bookings'] = $booking_stats['total_bookings'] ?? 0;
            $booking_stats['pending_bookings'] = $booking_stats['pending_bookings'] ?? 0;
            $booking_stats['confirmed_bookings'] = $booking_stats['confirmed_bookings'] ?? 0;
            $booking_stats['today_bookings'] = $booking_stats['today_bookings'] ?? 0;

            $this->render('dashboard', [
                'provider' => $provider,
                'cab_requests' => $cab_requests,
                'booking_stats' => $booking_stats,
                'db' => $this->db
            ]);
        } catch (Exception $e) {
            error_log("Service dashboard error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading dashboard: " . $e->getMessage();
            $this->render('dashboard', [
                'provider' => [],
                'cab_requests' => [],
                'booking_stats' => [
                    'total_bookings' => 0,
                    'pending_bookings' => 0,
                    'confirmed_bookings' => 0,
                    'today_bookings' => 0
                ],
                'db' => $this->db
            ]);
        }
    }

    public function add_service() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_data = [
                'provider_id' => $_SESSION['user_id'],
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'cost' => $_POST['cost'],
                'availability' => $_POST['availability']
            ];
            
            if ($this->model->addService($service_data)) {
                $_SESSION['success'] = "Service added successfully";
            } else {
                $_SESSION['error'] = "Failed to add service";
            }
            header('Location: index.php?module=service&action=dashboard');
            exit();
        }
        $this->render('service/add_service');
    }

    public function edit_service() {
        $service_id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$service_id) {
            header('Location: index.php?module=service&action=dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_data = [
                'id' => $service_id,
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'cost' => $_POST['cost'],
                'availability' => $_POST['availability']
            ];
            
            if ($this->model->updateService($service_data)) {
                $_SESSION['success'] = "Service updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update service";
            }
            header('Location: index.php?module=service&action=dashboard');
            exit();
        }

        $service = $this->model->getServiceById($service_id);
        $this->render('service/edit_service', ['service' => $service]);
    }

    public function confirm_booking() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("Booking ID not provided");
            }

            // Check if confirmed_at column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'confirmed_at'");
            if ($stmt->rowCount() == 0) {
                // Add confirmed_at column if it doesn't exist
                $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN confirmed_at DATETIME DEFAULT NULL");
                error_log("Added confirmed_at column to cab_bookings table");
            }

            $stmt = $this->db->prepare("
                UPDATE cab_bookings 
                SET status = 'confirmed', 
                    confirmed_at = NOW() 
                WHERE id = ? AND provider_id = ?
            ");
            $stmt->execute([$_GET['id'], $this->service_provider['id']]);

            // Get booking details for email
            $booking_id = $_GET['id'];
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    p.email as patient_email,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time,
                    sp.company_name as provider_name
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                INNER JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.id = ?
            ");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Send email notification
            if ($booking) {
                $emailService = new EmailService();
                $bookingData = [
                    'id' => $booking['id'],
                    'service_type' => 'Transportation',
                    'date' => date('F j, Y', strtotime($booking['pickup_date'])),
                    'time' => date('h:i A', strtotime($booking['pickup_time'])),
                    'provider_name' => $booking['provider_name'],
                    'pickup_address' => $booking['pickup_address'],
                    'destination' => $booking['destination']
                ];
                
                $emailService->sendBookingStatusUpdate(
                    $booking['patient_email'],
                    $booking['patient_name'],
                    $bookingData,
                    'confirmed'
                );
                
                // Create patient alert for booking confirmation
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
                    
                    // Create the alert
                    $alertTitle = "Transport Booking Confirmed";
                    $alertMessage = "Your transport booking with {$booking['provider_name']} for {$bookingData['date']} at {$bookingData['time']} has been confirmed.";
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'cab_booking', ?, 0)
                    ");
                    $stmt->execute([
                        $booking['patient_id'],
                        $alertTitle,
                        $alertMessage,
                        $booking['id']
                    ]);
                    
                    error_log("Created patient alert for cab booking ID {$booking['id']}, status: confirmed");
                } catch (PDOException $e) {
                    error_log("Error creating patient alert for cab booking: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
            }

            $_SESSION['success'] = "Booking confirmed successfully";
        } catch (Exception $e) {
            error_log("Error confirming booking: " . $e->getMessage());
            $_SESSION['error'] = "Error confirming booking: " . $e->getMessage();
        }
        
        $this->redirect('index.php?module=service&action=transport_dashboard');
    }

    public function complete_booking() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("Booking ID not provided");
            }

            // Check if completed_at column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'completed_at'");
            if ($stmt->rowCount() == 0) {
                // Add completed_at column if it doesn't exist
                $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN completed_at DATETIME DEFAULT NULL");
                error_log("Added completed_at column to cab_bookings table");
            }

            $stmt = $this->db->prepare("
                UPDATE cab_bookings 
                SET status = 'completed', 
                    completed_at = NOW() 
                WHERE id = ? AND provider_id = ?
            ");
            $stmt->execute([$_GET['id'], $this->service_provider['id']]);

            // Get booking details for email
            $booking_id = $_GET['id'];
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    p.email as patient_email,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time,
                    sp.company_name as provider_name
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                INNER JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.id = ?
            ");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Send email notification
            if ($booking) {
                $emailService = new EmailService();
                $bookingData = [
                    'id' => $booking['id'],
                    'service_type' => 'Transportation',
                    'date' => date('F j, Y', strtotime($booking['pickup_date'])),
                    'time' => date('h:i A', strtotime($booking['pickup_time'])),
                    'provider_name' => $booking['provider_name'],
                    'pickup_address' => $booking['pickup_address'],
                    'destination' => $booking['destination']
                ];
                
                $emailService->sendBookingStatusUpdate(
                    $booking['patient_email'],
                    $booking['patient_name'],
                    $bookingData,
                    'completed'
                );
                
                // Create patient alert for booking completion
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
                    
                    // Create the alert
                    $alertTitle = "Transport Booking Completed";
                    $alertMessage = "Your transport booking with {$booking['provider_name']} for {$bookingData['date']} at {$bookingData['time']} has been marked as completed.";
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'cab_booking', ?, 0)
                    ");
                    $stmt->execute([
                        $booking['patient_id'],
                        $alertTitle,
                        $alertMessage,
                        $booking['id']
                    ]);
                    
                    error_log("Created patient alert for cab booking ID {$booking['id']}, status: completed");
                } catch (PDOException $e) {
                    error_log("Error creating patient alert for cab booking: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
            }

            $_SESSION['success'] = "Booking marked as completed";
        } catch (Exception $e) {
            error_log("Error completing booking: " . $e->getMessage());
            $_SESSION['error'] = "Error completing booking: " . $e->getMessage();
        }
        
        $this->redirect('index.php?module=service&action=transport_dashboard');
    }

    public function cancel_booking() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("Booking ID not provided");
            }

            // Check if cancelled_at column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'cancelled_at'");
            if ($stmt->rowCount() == 0) {
                // Add cancelled_at column if it doesn't exist
                $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN cancelled_at DATETIME DEFAULT NULL");
                error_log("Added cancelled_at column to cab_bookings table");
            }

            $stmt = $this->db->prepare("
                UPDATE cab_bookings 
                SET status = 'cancelled', 
                    cancelled_at = NOW() 
                WHERE id = ? AND provider_id = ?
            ");
            $stmt->execute([$_GET['id'], $this->service_provider['id']]);

            // Get booking details for email
            $booking_id = $_GET['id'];
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    p.email as patient_email,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time,
                    sp.company_name as provider_name
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                INNER JOIN service_providers sp ON cb.provider_id = sp.id
                WHERE cb.id = ?
            ");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Send email notification
            if ($booking) {
                $emailService = new EmailService();
                $bookingData = [
                    'id' => $booking['id'],
                    'service_type' => 'Transportation',
                    'date' => date('F j, Y', strtotime($booking['pickup_date'])),
                    'time' => date('h:i A', strtotime($booking['pickup_time'])),
                    'provider_name' => $booking['provider_name'],
                    'pickup_address' => $booking['pickup_address'],
                    'destination' => $booking['destination']
                ];
                
                $emailService->sendBookingStatusUpdate(
                    $booking['patient_email'],
                    $booking['patient_name'],
                    $bookingData,
                    'cancelled'
                );
                
                // Create patient alert for booking cancellation
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
                    
                    // Create the alert
                    $alertTitle = "Transport Booking Cancelled";
                    $alertMessage = "Your transport booking with {$booking['provider_name']} for {$bookingData['date']} at {$bookingData['time']} has been cancelled.";
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO patient_alerts (
                            patient_id, title, message, alert_type, reference_id, is_read
                        ) VALUES (?, ?, ?, 'cab_booking', ?, 0)
                    ");
                    $stmt->execute([
                        $booking['patient_id'],
                        $alertTitle,
                        $alertMessage,
                        $booking['id']
                    ]);
                    
                    error_log("Created patient alert for cab booking ID {$booking['id']}, status: cancelled");
                } catch (PDOException $e) {
                    error_log("Error creating patient alert for cab booking: " . $e->getMessage());
                    // Continue execution even if alert creation fails
                }
            }

            $_SESSION['success'] = "Booking cancelled successfully";
        } catch (Exception $e) {
            error_log("Error cancelling booking: " . $e->getMessage());
            $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
        }
        
        $this->redirect('index.php?module=service&action=transport_dashboard');
    }

    public function profile() {
        try {
            // Get provider details directly from the database
            $stmt = $this->db->prepare("
                SELECT sp.*, u.email, u.created_at as joined_date
                FROM service_providers sp
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sp.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$provider) {
                throw new Exception("Service provider record not found");
            }
            
            $this->render('profile', ['provider' => $provider]);
        } catch (Exception $e) {
            error_log("Error loading profile: " . $e->getMessage());
            $_SESSION['error'] = "Error loading profile";
            header("Location: index.php?module=service&action=dashboard");
            exit();
        }
    }

    public function update_profile() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }

            // Validate and sanitize input
            $company_name = filter_var($_POST['company_name'] ?? '', FILTER_SANITIZE_STRING);
            $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
            $service_type = filter_var($_POST['service_type'] ?? '', FILTER_SANITIZE_STRING);
            $address = filter_var($_POST['address'] ?? '', FILTER_SANITIZE_STRING);
            $operating_hours = filter_var($_POST['operating_hours'] ?? '', FILTER_SANITIZE_STRING);
            $service_area = filter_var($_POST['service_area'] ?? '', FILTER_SANITIZE_STRING);
            $license_number = filter_var($_POST['license_number'] ?? '', FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
            
            // Validate required fields
            if (empty($company_name) || empty($phone) || empty($service_type)) {
                $_SESSION['error'] = "Company name, phone number, and service type are required.";
                header("Location: index.php?module=service&action=edit_profile");
                exit();
            }
            
            // Handle profile image upload
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                // Validate file type
                if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                    $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed.";
                    header("Location: index.php?module=service&action=edit_profile");
                    exit();
                }
                
                // Validate file size
                if ($_FILES['profile_image']['size'] > $max_size) {
                    $_SESSION['error'] = "Image size should not exceed 2MB.";
                    header("Location: index.php?module=service&action=edit_profile");
                    exit();
                }
                
                // Create uploads directory if it doesn't exist
                $upload_dir = __DIR__ . '/../../../uploads/profile_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = 'service_' . $_SESSION['service_id'] . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    $profile_image = 'uploads/profile_images/' . $filename;
                    
                    // Get current profile image
                    $stmt = $this->db->prepare("SELECT profile_image FROM service_providers WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $current_image = $stmt->fetchColumn();
                    
                    // Delete old profile image if exists
                    if (!empty($current_image) && file_exists(__DIR__ . '/../../../' . $current_image)) {
                        unlink(__DIR__ . '/../../../' . $current_image);
                    }
                } else {
                    $_SESSION['error'] = "Failed to upload image. Please try again.";
                    header("Location: index.php?module=service&action=edit_profile");
                    exit();
                }
            }
            
            // Check if service_providers table has profile_image column
            $stmt = $this->db->query("SHOW COLUMNS FROM service_providers LIKE 'profile_image'");
            if ($stmt->rowCount() === 0) {
                // Add profile_image column to service_providers table
                $this->db->exec("ALTER TABLE service_providers ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
            }

            // Update the service provider record
            $sql = "
                UPDATE service_providers 
                SET company_name = ?,
                    phone = ?,
                    service_type = ?,
                    address = ?,
                    operating_hours = ?,
                    service_area = ?,
                    license_number = ?,
                    description = ?,
                    updated_at = NOW()
            ";
            
            $params = [
                $company_name,
                $phone,
                $service_type,
                $address,
                $operating_hours,
                $service_area,
                $license_number,
                $description
            ];
            
            // Add profile_image to update if uploaded
            if ($profile_image !== null) {
                $sql .= ", profile_image = ?";
                $params[] = $profile_image;
            }
            
            $sql .= " WHERE user_id = ?";
            $params[] = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $_SESSION['success'] = "Profile updated successfully";
            } else {
                throw new Exception("Failed to update profile");
            }
        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
        }

        header("Location: index.php?module=service&action=profile");
        exit();
    }

    public function history() {
        try {
            // Get booking history directly from the database
            $stmt = $this->db->prepare("
                SELECT cb.*, p.name as patient_name, p.phone as patient_phone
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE cb.provider_id = ? 
                ORDER BY cb.created_at DESC
            ");
            $stmt->execute([$this->service_provider['id']]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('history', ['bookings' => $bookings]);
        } catch (Exception $e) {
            error_log("Error loading history: " . $e->getMessage());
            $_SESSION['error'] = "Error loading booking history";
            header("Location: index.php?module=service&action=dashboard");
            exit();
        }
    }

    /**
     * Edit service provider profile
     */
    public function edit_profile() {
        try {
            // If form is submitted
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate and sanitize input
                $company_name = filter_var($_POST['company_name'], FILTER_SANITIZE_STRING);
                $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
                $service_type = filter_var($_POST['service_type'], FILTER_SANITIZE_STRING);
                $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
                $operating_hours = filter_var($_POST['operating_hours'], FILTER_SANITIZE_STRING);
                $service_area = filter_var($_POST['service_area'], FILTER_SANITIZE_STRING);
                $license_number = filter_var($_POST['license_number'], FILTER_SANITIZE_STRING);
                $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
                
                // Validate required fields
                if (empty($company_name) || empty($phone) || empty($service_type)) {
                    $_SESSION['error'] = "Company name, phone number, and service type are required.";
                    $this->redirect('index.php?module=service&action=edit_profile');
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
                        $this->redirect('index.php?module=service&action=edit_profile');
                        return;
                    }
                    
                    // Validate file size
                    if ($_FILES['profile_image']['size'] > $max_size) {
                        $_SESSION['error'] = "Image size should not exceed 2MB.";
                        $this->redirect('index.php?module=service&action=edit_profile');
                        return;
                    }
                    
                    // Create uploads directory if it doesn't exist
                    $upload_dir = __DIR__ . '/../../../uploads/profile_images/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'service_' . $this->service['id'] . '_' . time() . '.' . $file_extension;
                    $target_file = $upload_dir . $filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                        $profile_image = 'uploads/profile_images/' . $filename;
                        
                        // Delete old profile image if exists
                        if (!empty($this->service['profile_image']) && file_exists(__DIR__ . '/../../../' . $this->service['profile_image'])) {
                            unlink(__DIR__ . '/../../../' . $this->service['profile_image']);
                        }
                    } else {
                        $_SESSION['error'] = "Failed to upload image. Please try again.";
                        $this->redirect('index.php?module=service&action=edit_profile');
                        return;
                    }
                }
                
                // Check if service_providers table has profile_image column
                $stmt = $this->db->query("SHOW COLUMNS FROM service_providers LIKE 'profile_image'");
                if ($stmt->rowCount() === 0) {
                    // Add profile_image column to service_providers table
                    $this->db->exec("ALTER TABLE service_providers ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
                }
                
                // Update service provider details
                $sql = "
                    UPDATE service_providers SET
                        company_name = ?,
                        phone = ?,
                        service_type = ?,
                        address = ?,
                        operating_hours = ?,
                        service_area = ?,
                        license_number = ?,
                        description = ?,
                        updated_at = NOW()
                ";
                $params = [
                    $company_name,
                    $phone,
                    $service_type,
                    $address,
                    $operating_hours,
                    $service_area,
                    $license_number,
                    $description
                ];
                
                // Add profile_image to update if uploaded
                if ($profile_image !== null) {
                    $sql .= ", profile_image = ?";
                    $params[] = $profile_image;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $this->service['id'];
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                
                // Update session data
                $this->service['company_name'] = $company_name;
                $this->service['phone'] = $phone;
                $this->service['service_type'] = $service_type;
                $this->service['address'] = $address;
                $this->service['operating_hours'] = $operating_hours;
                $this->service['service_area'] = $service_area;
                $this->service['license_number'] = $license_number;
                $this->service['description'] = $description;
                if ($profile_image !== null) {
                    $this->service['profile_image'] = $profile_image;
                    $_SESSION['profile_image'] = $profile_image;
                }
                
                $_SESSION['success'] = "Profile updated successfully.";
                $this->redirect('index.php?module=service&action=profile');
                return;
            }
            
            // Get service provider details with user information
            $stmt = $this->db->prepare("
                SELECT sp.*, u.email, u.status as user_status
                FROM service_providers sp
                JOIN users u ON sp.user_id = u.id
                WHERE sp.id = ?
            ");
            $stmt->execute([$this->service['id']]);
            $service_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$service_details) {
                $_SESSION['error'] = "Service provider profile not found.";
                $this->redirect('index.php?module=service&action=dashboard');
                return;
            }
            
            // Render the edit profile form
            $this->render('edit_profile', [
                'service' => $service_details
            ]);
        } catch (PDOException $e) {
            error_log("Error in edit_profile: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating your profile.";
            $this->redirect('index.php?module=service&action=profile');
        }
    }

    /**
     * Display pharmacy dashboard
     */
    public function pharmacy_dashboard() {
        // Initialize the PharmacyController
        $pharmacyController = new PharmacyController();
        
        // Call the dashboard method from the PharmacyController
        $pharmacyController->dashboard();
    }
    
    /**
     * Display pharmacy inventory
     */
    public function pharmacy_inventory() {
        // Check if PharmacyController class exists
        if (!class_exists('PharmacyController')) {
            $this->logError("PharmacyController class not found");
            die("Error: PharmacyController class not found. Please contact support.");
        }
        
        // Initialize the PharmacyController
        $pharmacyController = new PharmacyController();
        
        // Call the inventory method from the PharmacyController
        $pharmacyController->inventory();
    }
    
    /**
     * Display pharmacy orders
     */
    public function pharmacy_orders() {
        // Initialize the PharmacyController
        $pharmacyController = new PharmacyController();
        
        // Call the orders method from the PharmacyController
        $pharmacyController->orders();
    }
    
    /**
     * Display pharmacy stock history
     */
    public function pharmacy_stock_history() {
        // Initialize the PharmacyController
        $pharmacyController = new PharmacyController();
        
        // Call the stock_history method from the PharmacyController
        $pharmacyController->stock_history();
    }
    
    public function pharmacy_update_order_status() {
        $controller = new PharmacyController();
        $controller->update_order_status();
    }

    /**
     * View prescription for pharmacy
     */
    public function pharmacy_view_prescription() {
        $controller = new PharmacyController();
        $controller->view_prescription();
    }
    
    /**
     * Get order items
     */
    public function pharmacy_get_order_items() {
        $controller = new PharmacyController();
        $controller->get_order_items();
    }
    
    /**
     * Export orders
     */
    public function pharmacy_export_orders() {
        $controller = new PharmacyController();
        $controller->export_orders();
    }
    
    /**
     * Add medicine
     */
    public function pharmacy_add_medicine() {
        $controller = new PharmacyController();
        $controller->add_medicine();
    }
    
    /**
     * Edit medicine
     */
    public function pharmacy_edit_medicine() {
        $controller = new PharmacyController();
        $controller->edit_medicine();
    }
    
    /**
     * Update stock
     */
    public function pharmacy_update_stock() {
        $controller = new PharmacyController();
        $controller->update_stock();
    }
    
    /**
     * Process pharmacy service request update
     */
    public function pharmacy_update_service_request() {
        $controller = new PharmacyController();
        $controller->update_service_request();
    }
    
    /**
     * Update medicine prices
     */
    public function pharmacy_update_medicine_prices() {
        $controller = new PharmacyController();
        $controller->update_medicine_prices();
    }
    
    /**
     * Bulk process orders
     */
    public function pharmacy_bulk_process_orders() {
        $controller = new PharmacyController();
        $controller->bulk_process_orders();
    }

    /**
     * Display order details for pharmacy
     */
    public function pharmacy_order_details() {
        // Initialize the PharmacyController
        $pharmacyController = new PharmacyController();
        
        // Call the order_details method from the PharmacyController
        $pharmacyController->order_details();
    }

    /**
     * Redirect cab_requests to transport_bookings
     * This method exists to handle older links in the app that still use cab_requests
     */
    public function cab_requests() {
        // Redirect to the new transport_bookings action
        header('Location: index.php?module=service&action=transport_bookings');
        exit();
    }
    
    /**
     * Redirect view_cab_request to view_booking
     * This method exists to handle older links in the app that still use view_cab_request
     */
    public function view_cab_request() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        // Redirect to the new view_booking action
        header('Location: index.php?module=service&action=view_booking&id=' . $id);
        exit();
    }
    
    /**
     * View transport booking details
     */
    public function view_booking() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            if (!isset($_GET['id'])) {
                throw new Exception("Booking ID not provided");
            }
            
            $booking_id = (int)$_GET['id'];
            
            // Get booking details
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    p.address as patient_address,
                    p.email as patient_email,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE cb.id = ? AND cb.provider_id = ?
            ");
            $stmt->execute([$booking_id, $this->service_provider['id']]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                throw new Exception("Booking not found or you don't have permission to view it");
            }
            
            $this->render('transport/view_booking', [
                'service_provider' => $this->service_provider,
                'booking' => $booking,
                'db' => $this->db
            ]);
        } catch (Exception $e) {
            error_log("View booking error: " . $e->getMessage());
            $_SESSION['error'] = "Error viewing booking: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=transport_bookings');
        }
    }
    
    /**
     * Transport Dashboard
     */
    public function transport_dashboard() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            // Get service provider with email
            $stmt = $this->db->prepare("
                SELECT sp.*, u.email 
                FROM service_providers sp
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sp.id = :provider_id
            ");
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$provider) {
                throw new Exception("Service provider not found");
            }
            
            // Get booking statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN DATE(pickup_datetime) = CURDATE() THEN 1 ELSE 0 END) as today_bookings
                FROM cab_bookings
                WHERE provider_id = :provider_id
            ");
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Initialize stats to 0 if null
            $booking_stats['total_bookings'] = $booking_stats['total_bookings'] ?? 0;
            $booking_stats['pending_bookings'] = $booking_stats['pending_bookings'] ?? 0;
            $booking_stats['confirmed_bookings'] = $booking_stats['confirmed_bookings'] ?? 0;
            $booking_stats['completed_bookings'] = $booking_stats['completed_bookings'] ?? 0;
            $booking_stats['cancelled_bookings'] = $booking_stats['cancelled_bookings'] ?? 0;
            $booking_stats['today_bookings'] = $booking_stats['today_bookings'] ?? 0;
            
            // Get recent cab bookings
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time,
                    cb.destination
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE cb.provider_id = :provider_id 
                ORDER BY cb.created_at DESC
                LIMIT 5
            ");
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $recent_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get today's bookings
            $stmt = $this->db->prepare("
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time,
                    cb.destination
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE cb.provider_id = :provider_id 
                AND DATE(pickup_datetime) = CURDATE()
                ORDER BY cb.pickup_datetime ASC
            ");
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $today_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('transport/dashboard', [
                'service_provider' => $provider, // Use the provider with email
                'booking_stats' => $booking_stats,
                'recent_requests' => $recent_requests,
                'today_bookings' => $today_bookings,
                'db' => $this->db
            ]);
        } catch (Exception $e) {
            error_log("Transport dashboard error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading transport dashboard: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=dashboard');
        }
    }
    
    /**
     * Display transport bookings
     */
    public function transport_bookings() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            // Get filter parameters
            $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
            $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
            
            // Build query conditions
            $conditions = ["provider_id = :provider_id"];
            $params = [':provider_id' => $this->service_provider['id']];
            
            if ($status_filter != 'all' && in_array($status_filter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
                $conditions[] = "status = :status";
                $params[':status'] = $status_filter;
            }
            
            if (!empty($date_filter)) {
                $conditions[] = "DATE(pickup_datetime) = :date";
                $params[':date'] = $date_filter;
            }
            
            // Build the query
            $query = "
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY cb.pickup_datetime
            ";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get booking statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
                FROM cab_bookings
                WHERE provider_id = :provider_id
            ");
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Initialize stats to 0 if null
            $booking_stats['total_bookings'] = $booking_stats['total_bookings'] ?? 0;
            $booking_stats['pending_bookings'] = $booking_stats['pending_bookings'] ?? 0;
            $booking_stats['confirmed_bookings'] = $booking_stats['confirmed_bookings'] ?? 0;
            $booking_stats['completed_bookings'] = $booking_stats['completed_bookings'] ?? 0;
            $booking_stats['cancelled_bookings'] = $booking_stats['cancelled_bookings'] ?? 0;
            
            $this->render('transport/bookings', [
                'service_provider' => $this->service_provider,
                'bookings' => $bookings,
                'booking_stats' => $booking_stats,
                'status_filter' => $status_filter,
                'date_filter' => $date_filter,
                'db' => $this->db
            ]);
        } catch (Exception $e) {
            error_log("Transport bookings error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading transport bookings: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=dashboard');
        }
    }
    
    /**
     * Transport Booking History
     */
    public function transport_history() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            // Pagination settings
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Get filter parameters
            $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
            $date_filter_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
            $date_filter_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
            $patient_filter = isset($_GET['patient']) ? $_GET['patient'] : '';
            
            // Build query conditions
            $conditions = ["provider_id = :provider_id"];
            $params = [':provider_id' => $this->service_provider['id']];
            
            if (!empty($status_filter) && in_array($status_filter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
                $conditions[] = "status = :status";
                $params[':status'] = $status_filter;
            }
            
            if (!empty($date_filter_start)) {
                $conditions[] = "DATE(pickup_datetime) >= :date_start";
                $params[':date_start'] = $date_filter_start;
            }
            
            if (!empty($date_filter_end)) {
                $conditions[] = "DATE(pickup_datetime) <= :date_end";
                $params[':date_end'] = $date_filter_end;
            }
            
            if (!empty($patient_filter)) {
                $conditions[] = "p.name LIKE :patient_name";
                $params[':patient_name'] = '%' . $patient_filter . '%';
            }
            
            // Get total count for pagination
            $count_query = "
                SELECT COUNT(*) as total
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE " . implode(" AND ", $conditions);
            
            $stmt = $this->db->prepare($count_query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total_records = $stmt->fetchColumn();
            $total_pages = ceil($total_records / $limit);
            
            // Build the main query
            $query = "
                SELECT 
                    cb.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    DATE(cb.pickup_datetime) as pickup_date,
                    TIME(cb.pickup_datetime) as pickup_time
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY cb.pickup_datetime DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get summary statistics
            $stats_query = "
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    MIN(pickup_datetime) as oldest_booking,
                    MAX(pickup_datetime) as newest_booking
                FROM cab_bookings
                WHERE provider_id = :provider_id
            ";
            
            $stmt = $this->db->prepare($stats_query);
            $stmt->bindParam(':provider_id', $this->service_provider['id']);
            $stmt->execute();
            $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Create formatted data for the view
            $filters = [
                'status' => $status_filter,
                'date_start' => $date_filter_start,
                'date_end' => $date_filter_end,
                'patient' => $patient_filter
            ];
            
            $pagination = [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'limit' => $limit
            ];
            
            $this->render('transport/history', [
                'service_provider' => $this->service_provider,
                'bookings' => $bookings,
                'booking_stats' => $booking_stats,
                'filters' => $filters,
                'pagination' => $pagination,
                'db' => $this->db
            ]);
        } catch (Exception $e) {
            error_log("Transport history error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading transport history: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=dashboard');
        }
    }
    
    /**
     * Update booking notes
     */
    public function update_booking_notes() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            
            if (!isset($_POST['booking_id']) || !isset($_POST['provider_notes'])) {
                throw new Exception("Missing required parameters");
            }
            
            // Check if provider_notes column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'provider_notes'");
            if ($stmt->rowCount() == 0) {
                // Add provider_notes column if it doesn't exist
                $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN provider_notes TEXT DEFAULT NULL");
                error_log("Added provider_notes column to cab_bookings table");
            }
            
            // Check if updated_at column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM cab_bookings LIKE 'updated_at'");
            if ($stmt->rowCount() == 0) {
                // Add updated_at column if it doesn't exist
                $this->db->exec("ALTER TABLE cab_bookings ADD COLUMN updated_at DATETIME DEFAULT NULL");
                error_log("Added updated_at column to cab_bookings table");
            }
            
            $booking_id = (int)$_POST['booking_id'];
            $provider_notes = trim($_POST['provider_notes']);
            
            // Update booking notes
            $stmt = $this->db->prepare("
                UPDATE cab_bookings 
                SET provider_notes = ?, 
                    updated_at = NOW() 
                WHERE id = ? AND provider_id = ?
            ");
            $stmt->execute([$provider_notes, $booking_id, $this->service_provider['id']]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Booking notes updated successfully";
            } else {
                throw new Exception("Failed to update booking notes or booking not found");
            }
            
            // Always redirect back to transport_bookings
            $this->redirect('index.php?module=service&action=transport_bookings');
        } catch (Exception $e) {
            error_log("Update booking notes error: " . $e->getMessage());
            $_SESSION['error'] = "Error updating booking notes: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=transport_bookings');
        }
    }
    
    /**
     * Export transport bookings to Excel
     */
    public function export_bookings() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            // Get filter parameters
            $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
            $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
            
            // Build query conditions
            $conditions = ["provider_id = :provider_id"];
            $params = [':provider_id' => $this->service_provider['id']];
            
            if ($status_filter != 'all' && in_array($status_filter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
                $conditions[] = "status = :status";
                $params[':status'] = $status_filter;
            }
            
            if (!empty($date_filter)) {
                $conditions[] = "DATE(pickup_datetime) = :date";
                $params[':date'] = $date_filter;
            }
            
            // Build the query
            $query = "
                SELECT 
                    cb.id, 
                    DATE(cb.pickup_datetime) as pickup_date, 
                    TIME(cb.pickup_datetime) as pickup_time, 
                    cb.pickup_address, 
                    cb.destination, 
                    cb.status, 
                    cb.special_requirements, 
                    cb.provider_notes, 
                    cb.created_at,
                    p.name as patient_name, 
                    p.phone as patient_phone
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY cb.pickup_datetime
            ";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="transport_bookings_' . date('Ymd') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Create Excel output
            echo "<table border='1'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Pickup Date</th>
                    <th>Pickup Time</th>
                    <th>Patient Name</th>
                    <th>Patient Phone</th>
                    <th>Pickup Address</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Special Requirements</th>
                    <th>Provider Notes</th>
                    <th>Created</th>
                  </tr>";
            
            foreach ($bookings as $booking) {
                echo "<tr>";
                echo "<td>" . $booking['id'] . "</td>";
                echo "<td>" . date('Y-m-d', strtotime($booking['pickup_date'])) . "</td>";
                echo "<td>" . date('H:i', strtotime($booking['pickup_time'])) . "</td>";
                echo "<td>" . htmlspecialchars($booking['patient_name']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['patient_phone']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['pickup_address']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['destination']) . "</td>";
                echo "<td>" . ucfirst($booking['status']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['special_requirements'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($booking['provider_notes'] ?? '') . "</td>";
                echo "<td>" . date('Y-m-d H:i', strtotime($booking['created_at'])) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            exit;
        } catch (Exception $e) {
            error_log("Export bookings error: " . $e->getMessage());
            $_SESSION['error'] = "Error exporting bookings: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=transport_bookings');
        }
    }
    
    /**
     * Export transport booking history to Excel
     */
    public function export_transport_history() {
        try {
            // Check if provider is transportation service
            if ($this->service_provider['service_type'] != 'transportation' && $this->service_provider['service_type'] != 'both') {
                throw new Exception("You are not authorized to access transportation services");
            }
            
            // Get filter parameters
            $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
            $date_filter_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
            $date_filter_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
            $patient_filter = isset($_GET['patient']) ? $_GET['patient'] : '';
            
            // Build query conditions
            $conditions = ["provider_id = :provider_id"];
            $params = [':provider_id' => $this->service_provider['id']];
            
            if (!empty($status_filter) && in_array($status_filter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
                $conditions[] = "status = :status";
                $params[':status'] = $status_filter;
            }
            
            if (!empty($date_filter_start)) {
                $conditions[] = "DATE(pickup_datetime) >= :date_start";
                $params[':date_start'] = $date_filter_start;
            }
            
            if (!empty($date_filter_end)) {
                $conditions[] = "DATE(pickup_datetime) <= :date_end";
                $params[':date_end'] = $date_filter_end;
            }
            
            if (!empty($patient_filter)) {
                $conditions[] = "p.name LIKE :patient_name";
                $params[':patient_name'] = '%' . $patient_filter . '%';
            }
            
            // Build the query
            $query = "
                SELECT 
                    cb.id, 
                    DATE(cb.pickup_datetime) as pickup_date, 
                    TIME(cb.pickup_datetime) as pickup_time, 
                    cb.pickup_address, 
                    cb.destination, 
                    cb.status, 
                    cb.special_requirements, 
                    cb.provider_notes, 
                    cb.created_at, 
                    cb.confirmed_at, 
                    cb.completed_at, 
                    cb.cancelled_at,
                    p.name as patient_name, 
                    p.phone as patient_phone
                FROM cab_bookings cb
                INNER JOIN patients p ON cb.patient_id = p.id 
                WHERE " . implode(" AND ", $conditions) . "
                ORDER BY cb.pickup_datetime DESC
            ";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="transport_history_' . date('Ymd') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Create Excel output
            echo "<table border='1'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Pickup Date</th>
                    <th>Pickup Time</th>
                    <th>Patient Name</th>
                    <th>Patient Phone</th>
                    <th>Pickup Address</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Special Requirements</th>
                    <th>Provider Notes</th>
                    <th>Created</th>
                    <th>Confirmed</th>
                    <th>Completed</th>
                    <th>Cancelled</th>
                  </tr>";
            
            foreach ($bookings as $booking) {
                echo "<tr>";
                echo "<td>" . $booking['id'] . "</td>";
                echo "<td>" . date('Y-m-d', strtotime($booking['pickup_date'])) . "</td>";
                echo "<td>" . date('H:i', strtotime($booking['pickup_time'])) . "</td>";
                echo "<td>" . htmlspecialchars($booking['patient_name']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['patient_phone']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['pickup_address']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['destination']) . "</td>";
                echo "<td>" . ucfirst($booking['status']) . "</td>";
                echo "<td>" . htmlspecialchars($booking['special_requirements'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($booking['provider_notes'] ?? '') . "</td>";
                echo "<td>" . date('Y-m-d H:i', strtotime($booking['created_at'])) . "</td>";
                echo "<td>" . (!empty($booking['confirmed_at']) ? date('Y-m-d H:i', strtotime($booking['confirmed_at'])) : '') . "</td>";
                echo "<td>" . (!empty($booking['completed_at']) ? date('Y-m-d H:i', strtotime($booking['completed_at'])) : '') . "</td>";
                echo "<td>" . (!empty($booking['cancelled_at']) ? date('Y-m-d H:i', strtotime($booking['cancelled_at'])) : '') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            exit;
        } catch (Exception $e) {
            error_log("Export transport history error: " . $e->getMessage());
            $_SESSION['error'] = "Error exporting transport history: " . $e->getMessage();
            $this->redirect('index.php?module=service&action=transport_history');
        }
    }
}
?>
