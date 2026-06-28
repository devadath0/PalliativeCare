<?php
/**
 * Admin Module Controller
 * Palliative Care System
 */

require_once __DIR__ . '/../base_controller.php';

class AdminController extends BaseController {
    
    public function __construct() {
        // Initialize database connection from parent first
        parent::__construct();
        
        // Ensure database connection is initialized
        if ($this->db === null) {
            $this->db = new Database();
        }
        
        // Check if user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header("Location: index.php?module=auth&action=login&type=admin");
            exit;
        }
    }

    /**
     * Display admin dashboard
     */
    public function dashboard() {
        try {
            // Get counts for dashboard
            $counts = $this->getDashboardCounts();
            
            // Get recent activities
            $activities = $this->getRecentActivities();
            
            // Render the dashboard view
            $this->render('dashboard', [
                'page_title' => 'Admin Dashboard',
                'counts' => $counts,
                'activities' => $activities
            ]);
        } catch (Exception $e) {
            $this->logError("Dashboard error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading dashboard";
            $this->render('dashboard', [
                'page_title' => 'Admin Dashboard',
                'counts' => [],
                'activities' => []
            ]);
        }
    }

    /**
     * Get counts for dashboard widgets
     */
    private function getDashboardCounts() {
        try {
            $counts = [];
            
            // Get total patients
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM patients");
            $counts['patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get total doctors
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM doctors");
            $counts['doctors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get total services
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM service_providers");
            $counts['service_providers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get active appointments
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'scheduled'");
            $counts['active_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $counts;
            
        } catch (PDOException $e) {
            $this->logError("Error getting dashboard counts: " . $e->getMessage());
            return [
                'patients' => 0,
                'doctors' => 0,
                'service_providers' => 0,
                'active_services' => 0
            ];
        }
    }

    /**
     * Get recent activities for the dashboard
     */
    private function getRecentActivities() {
        $activities = [];
        
        try {
        // Get recent patient registrations
            $stmt = $this->db->query("SELECT 'patient' as type, name, email, created_at FROM patients 
                     ORDER BY created_at DESC LIMIT 5");
            $patientActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recent doctor registrations
            $stmt = $this->db->query("SELECT 'doctor' as type, name, email, created_at FROM doctors 
                     ORDER BY created_at DESC LIMIT 5");
            $doctorActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combine activities
            $activities = array_merge($patientActivities, $doctorActivities);
        
        // Sort by created_at
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
            // Limit to 5 most recent
            $activities = array_slice($activities, 0, 5);
        } catch (Exception $e) {
            $this->logError("Error getting recent activities: " . $e->getMessage());
        }
        
        return $activities;
    }

    /**
     * Display users management page
     */
    public function users() {
        $this->render('users', [
            'page_title' => 'Manage Users'
        ]);
    }

    /**
     * Display patients management page
     */
    public function patients() {
        try {
            // Fetch all patients with their user status
            $stmt = $this->db->query("
                SELECT p.*, u.status 
                FROM patients p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.name ASC
            ");
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('patients', [
                'page_title' => 'Manage Patients',
                'patients' => $patients
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching patients: " . $e->getMessage());
            $this->render('patients', [
                'page_title' => 'Manage Patients',
                'patients' => [],
                'error' => 'Error fetching patients data'
            ]);
        }
    }

    /**
     * Display doctors management page
     */
    public function doctors() {
        try {
            // Fetch all doctors
            $stmt = $this->db->query("SELECT * FROM doctors ORDER BY name ASC");
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('doctors', [
                'page_title' => 'Manage Doctors',
                'doctors' => $doctors
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching doctors: " . $e->getMessage());
            $this->render('doctors', [
                'page_title' => 'Manage Doctors',
                'doctors' => [],
                'error' => 'Error fetching doctors data'
            ]);
        }
    }

    /**
     * Display services management page
     */
    public function services() {
        try {
            // Fetch all service providers
            $stmt = $this->db->query("SELECT * FROM service_providers ORDER BY company_name ASC");
            $serviceProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map service provider fields to the expected fields in the view
            $services = [];
            foreach ($serviceProviders as $provider) {
                // Set cost based on service type or fetch from another table if needed
                $cost = $this->getServiceCost($provider['id'], $provider['service_type']);
                
                // Log cost for debugging
                error_log("Service ID: {$provider['id']}, Type: {$provider['service_type']}, Cost: {$cost}");
                
                $services[] = [
                    'id' => $provider['id'],
                    'name' => $provider['company_name'],
                    'description' => $provider['service_type'] . ' services' . ($provider['service_area'] ? ' in ' . $provider['service_area'] : ''),
                    'provider_name' => $provider['company_name'],
                    'cost' => $cost,
                    'status' => $provider['status']
                ];
            }
            
            $this->render('services', [
                'page_title' => 'Manage Services',
                'services' => $services
            ]);
        } catch (Exception $e) {
            $this->logError("Error fetching services: " . $e->getMessage());
            $this->render('services', [
                'page_title' => 'Manage Services',
                'services' => [],
                'error' => 'Error fetching service providers data'
            ]);
        }
    }
    
    /**
     * Get service cost based on service type or from another table
     * 
     * @param int $provider_id The service provider ID
     * @param string $service_type The type of service
     * @return float The cost of the service
     */
    private function getServiceCost($provider_id, $service_type) {
        try {
            // Try to fetch service cost from a pricing table if it exists
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'service_pricing'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $this->db->prepare("SELECT cost FROM service_pricing WHERE provider_id = ?");
                $stmt->execute([$provider_id]);
                $pricing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($pricing && isset($pricing['cost'])) {
                    return $pricing['cost'];
                }
            }
            
            // If no pricing table or no entry found, use default pricing based on service type
            switch($service_type) {
                case 'pharmacy':
                    return 75.50;
                case 'transportation':
                    return 95.75;
                case 'nursing':
                    return 125.00;
                case 'equipment':
                    return 150.00;
                case 'rehabilitation':
                    return 185.00;
                case 'counseling':
                    return 160.00;
                default:
                    return 100.00;
            }
        } catch (Exception $e) {
            $this->logError("Error getting service cost: " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Display reports page
     */
    public function reports() {
        // Get filter parameters
        $report_type = $_GET['report_type'] ?? 'patients';
        $date_range = $_GET['date_range'] ?? 'year';
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        // Set default date range based on selected option
        if ($date_range !== 'custom') {
            switch ($date_range) {
                case 'today':
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d');
                    break;
                case 'week':
                    $start_date = date('Y-m-d', strtotime('monday this week'));
                    $end_date = date('Y-m-d', strtotime('sunday this week'));
                    break;
                case 'month':
                    $start_date = date('Y-m-01');
                    $end_date = date('Y-m-t');
                    break;
                case 'year':
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-12-31');
                    break;
            }
        }

        try {
            // Get report statistics
            $query = "SELECT * FROM reports WHERE report_type = :report_type AND date BETWEEN :start_date AND :end_date";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':report_type', $report_type);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log(date('Y-m-d H:i:s') . ' - Error getting report stats: ' . $e->getMessage());
            $stats = [];
        }

        // Format stats for display
        $formatted_stats = $this->formatReportStats($stats, $report_type);

        $this->render('reports', [
            'page_title' => 'Reports',
            'stats' => $formatted_stats,
            'detailed_data' => [], // Add detailed data if needed
            'report_type' => $report_type,
            'date_range' => $date_range,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }

    private function formatReportStats($stats, $report_type) {
        try {
            // Initialize formatted stats array
            $formatted = [];
            
            if ($report_type == 'patients') {
                // Get total patients
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM patients");
                $formatted['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get active patients (users who are active)
                $stmt = $this->db->query("SELECT COUNT(*) as count 
                    FROM patients p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE u.status = 'active'");
                $formatted['active_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get new patients (registered in last 30 days)
                $stmt = $this->db->query("SELECT COUNT(*) as count 
                    FROM patients 
                    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)");
                $formatted['new_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get discharged patients
                $stmt = $this->db->query("SELECT COUNT(*) as count 
                    FROM patients p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE u.status = 'inactive'");
                $formatted['discharged_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
            } elseif ($report_type == 'doctors') {
                // Get total doctors
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM doctors");
                $formatted['total_doctors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get available doctors
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM doctors WHERE availability_status = 'available'");
                $formatted['available_doctors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get doctors on leave
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM doctors WHERE availability_status = 'on_leave'");
                $formatted['doctors_on_leave'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
            } elseif ($report_type == 'services') {
                // Get total services
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM service_providers");
                $formatted['total_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Get active services
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM service_providers WHERE status = 'active'");
                $formatted['active_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
    
            return $formatted;
        } catch (Exception $e) {
            $this->logError("Error formatting report stats: " . $e->getMessage());
            return [
                'total_patients' => 0,
                'active_patients' => 0,
                'new_patients' => 0,
                'discharged_patients' => 0
            ];
        }
    }

    /**
     * Generate Patient Activity Report
     */
    private function generatePatientActivityReport($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT p.name, p.email, COUNT(a.id) as appointment_count
                 FROM patients p
                 LEFT JOIN appointments a ON p.id = a.patient_id
                 WHERE a.appointment_date BETWEEN ? AND ?
                 GROUP BY p.id
                 ORDER BY appointment_count DESC");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate Doctor Performance Report
     */
    private function generateDoctorPerformanceReport($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT d.name, d.specialization, 
                 COUNT(a.id) as total_appointments,
                 COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments
                 FROM doctors d
                 LEFT JOIN appointments a ON d.id = a.doctor_id
                 WHERE a.appointment_date BETWEEN ? AND ?
                 GROUP BY d.id
                 ORDER BY total_appointments DESC");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate Service Usage Report
     */
    private function generateServiceUsageReport($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT sp.name as service_provider, s.service_name,
                 COUNT(s.id) as usage_count
                 FROM services s
                 LEFT JOIN service_providers sp ON s.provider_id = sp.id
                 WHERE s.service_date BETWEEN ? AND ?
                 GROUP BY sp.id, s.service_name
                 ORDER BY usage_count DESC");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate Financial Report
     */
    private function generateFinancialReport($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT 
                 COUNT(DISTINCT p.id) as total_patients,
                 COUNT(a.id) as total_appointments,
                 SUM(CASE WHEN p.payment_status = 'paid' THEN p.amount ELSE 0 END) as total_revenue
                 FROM payments p
                 LEFT JOIN appointments a ON p.appointment_id = a.id
                 WHERE p.payment_date BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Display add patient form
     */
    public function add_patient() {
        $this->render('add_patient');
    }

    /**
     * Process add patient form submission
     */
    public function process_add_patient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=patients');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $emergency_contact = $_POST['emergency_contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $medical_history = $_POST['medical_history'] ?? '';
        $status = $_POST['status'] ?? 'active';

        // Validate input
        if (empty($name) || empty($email) || empty($dob)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: index.php?module=admin&action=add_patient');
            exit;
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // First create user account with minimal fields
            $sql = "INSERT INTO users (name, email, user_type, status) VALUES (:name, :email, 'patient', :status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'status' => $status
            ]);
            
            $user_id = $this->db->lastInsertId();

            // Then create patient record
            $sql = "INSERT INTO patients (user_id, name, email, dob, phone, emergency_contact, address, medical_history) 
                    VALUES (:user_id, :name, :email, :dob, :phone, :emergency_contact, :address, :medical_history)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'dob' => $dob,
                'phone' => $phone,
                'emergency_contact' => $emergency_contact,
                'address' => $address,
                'medical_history' => $medical_history
            ]);

            // Commit transaction
            $this->db->commit();

            $_SESSION['success'] = 'Patient added successfully. A user account has been created with their email.';
            header('Location: index.php?module=admin&action=patients');
            exit;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error adding patient: " . $e->getMessage());
            $_SESSION['error'] = 'Error adding patient: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=add_patient');
            exit;
        }
    }

    /**
     * Display edit patient form
     */
    public function edit_patient() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid patient ID';
            header('Location: index.php?module=admin&action=patients');
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.status 
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$patient) {
                $_SESSION['error'] = 'Patient not found';
                header('Location: index.php?module=admin&action=patients');
                exit;
            }

            $this->render('edit_patient', ['patient' => $patient]);
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error fetching patient data: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=patients');
            exit;
        }
    }

    /**
     * Process edit patient form submission
     */
    public function process_edit_patient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=patients');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid patient ID';
            header('Location: index.php?module=admin&action=patients');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $emergency_contact = $_POST['emergency_contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $medical_history = $_POST['medical_history'] ?? '';
        $status = $_POST['status'] ?? 'active';

        // Validate input
        if (empty($name) || empty($email) || empty($dob)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header("Location: index.php?module=admin&action=edit_patient&id=$id");
            exit;
        }

        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // First, get the user_id for this patient
            $stmt = $this->db->prepare("SELECT user_id FROM patients WHERE id = ?");
            $stmt->execute([$id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                throw new Exception("Patient not found");
            }
            
            $user_id = $patient['user_id'];
            
            // Update patient record
            $sql = "UPDATE patients SET 
                    name = :name, 
                    email = :email, 
                    dob = :dob, 
                    phone = :phone, 
                    emergency_contact = :emergency_contact, 
                    address = :address, 
                    medical_history = :medical_history 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'dob' => $dob,
                'phone' => $phone,
                'emergency_contact' => $emergency_contact,
                'address' => $address,
                'medical_history' => $medical_history
            ]);
            
            // Update user record
            $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email,
                    status = :status
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $user_id,
                'name' => $name,
                'email' => $email,
                'status' => $status
            ]);
            
            // Commit transaction
            $this->db->commit();

            $_SESSION['success'] = 'Patient updated successfully';
            header('Location: index.php?module=admin&action=patients');
            exit;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $_SESSION['error'] = 'Error updating patient: ' . $e->getMessage();
            header("Location: index.php?module=admin&action=edit_patient&id=$id");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header("Location: index.php?module=admin&action=edit_patient&id=$id");
            exit;
        }
    }

    /**
     * Display add doctor form
     */
    public function add_doctor() {
        $this->render('add_doctor');
    }

    /**
     * Export report data
     */
    public function export_report() {
        try {
            $report_type = $_GET['type'] ?? 'patients';
            $format = $_GET['format'] ?? 'csv';
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
    
            // Get report data based on type
            switch ($report_type) {
                case 'patients':
                    $data = $this->generatePatientActivityReport($start_date, $end_date);
                    $filename = "patient_report_{$start_date}_{$end_date}";
                    $headers = ['Name', 'Email', 'Appointment Count'];
                    break;
                case 'doctors':
                    $data = $this->generateDoctorPerformanceReport($start_date, $end_date);
                    $filename = "doctor_report_{$start_date}_{$end_date}";
                    $headers = ['Name', 'Specialization', 'Total Appointments', 'Completed Appointments'];
                    break;
                case 'services':
                    $data = $this->generateServiceUsageReport($start_date, $end_date);
                    $filename = "service_report_{$start_date}_{$end_date}";
                    $headers = ['Service Provider', 'Service Name', 'Usage Count'];
                    break;
                default:
                    throw new Exception('Invalid report type');
            }
    
            // Export based on format
            switch ($format) {
                case 'csv':
                    $this->exportToCsv($data, $headers, $filename);
                    break;
                case 'pdf':
                    $this->exportToPdf($data, $report_type, $start_date, $end_date);
                    break;
                default:
                    throw new Exception('Invalid export format');
            }
        } catch (Exception $e) {
            $this->logError("Export error: " . $e->getMessage());
            $_SESSION['error'] = "Error exporting report: " . $e->getMessage();
            header('Location: index.php?module=admin&action=reports');
            exit;
        }
    }
    
    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $headers, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data to PDF
     */
    private function exportToPdf($data, $type, $start_date, $end_date) {
        // Clean any output buffers to ensure no content has been output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
    
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
        // Set document information
        $pdf->SetCreator('Palliative Care System');
        $pdf->SetAuthor('Palliative Care System');
        $pdf->SetTitle(ucfirst($type ?? 'Report'));
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    
        // Set margins and breaks
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();
    
        // Title section
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Palliative Care System', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, ucfirst($type ?? 'Report') . ' Report', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Period: ' . date('Y-m-d', strtotime($start_date)) . ' to ' . date('Y-m-d', strtotime($end_date)), 0, 1, 'C');
        
        $pdf->Ln(10);
        
        // Add null coalescing operator to handle undefined variable
        switch ($type ?? 'default') {
            case 'patients':
                $this->generatePatientsPdfContent($pdf, $data);
                break;
            case 'doctors':
                $this->generateDoctorsPdfContent($pdf, $data);
                break;
            case 'services':
                $this->generateServicesPdfContent($pdf, $data);
                break;
            case 'financial':
                $this->generateFinancialPdfContent($pdf, $data);
                break;
        }

        // Generate filename with date
        $filename = sprintf('%s_report_%s.pdf', $type, date('Y-m-d'));
        
        // Output the PDF
        $pdf->Output($filename, 'D');
        exit;
    }

    private function generatePatientsPdfContent($pdf, $data) {
        // Add table headers
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 10, 'Metric', 1);
        $pdf->Cell(30, 10, 'Count', 1);
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 12);
        
        // Add statistics rows
        $metrics = [
            'Total Patients' => $data['total_patients'] ?? 0,
            'Active Patients' => $data['active_patients'] ?? 0,
            'New Patients' => $data['new_patients'] ?? 0,
            'Discharged Patients' => $data['discharged_patients'] ?? 0
        ];
        
        foreach ($metrics as $label => $value) {
            $pdf->Cell(60, 10, $label, 1);
            $pdf->Cell(30, 10, $value, 1);
            $pdf->Ln();
        }
    }

    private function generateDoctorsPdfContent($pdf, $data) {
        // Add table headers
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 10, 'Metric', 1);
        $pdf->Cell(30, 10, 'Count', 1);
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 12);
        
        // Determine if we're working with formatted stats or detailed report data
        if (isset($data[0]) && is_array($data[0]) && isset($data[0]['name'])) {
            // This is detailed doctor performance data
            // First, add summary metrics
            $totalDoctors = count($data);
            $totalAppointments = 0;
            $completedAppointments = 0;
            
            foreach ($data as $row) {
                $totalAppointments += $row['total_appointments'] ?? 0;
                $completedAppointments += $row['completed_appointments'] ?? 0;
            }
            
            $metrics = [
                'Total Doctors' => $totalDoctors,
                'Total Appointments' => $totalAppointments,
                'Completed Appointments' => $completedAppointments
            ];
            
            foreach ($metrics as $label => $value) {
                $pdf->Cell(60, 10, $label, 1);
                $pdf->Cell(30, 10, $value, 1);
                $pdf->Ln();
            }
            
            // Then, add a page break and detailed table if there's data
            if (count($data) > 0) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Cell(0, 10, 'Doctor Performance Details', 0, 1, 'C');
                $pdf->Ln(5);
                
                // Add table headers
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->Cell(60, 10, 'Doctor Name', 1);
                $pdf->Cell(60, 10, 'Specialization', 1);
                $pdf->Cell(35, 10, 'Total Appointments', 1);
                $pdf->Cell(35, 10, 'Completed', 1);
                $pdf->Ln();
                
                // Add table rows
                $pdf->SetFont('helvetica', '', 10);
                foreach ($data as $row) {
                    $pdf->Cell(60, 10, $row['name'], 1);
                    $pdf->Cell(60, 10, $row['specialization'] ?? 'N/A', 1);
                    $pdf->Cell(35, 10, $row['total_appointments'] ?? 0, 1, 0, 'C');
                    $pdf->Cell(35, 10, $row['completed_appointments'] ?? 0, 1, 0, 'C');
                    $pdf->Ln();
                }
            }
        } else {
            // This is formatted stats from formatReportStats
            $metrics = [
                'Total Doctors' => $data['total_doctors'] ?? 0,
                'Available Doctors' => $data['available_doctors'] ?? 0,
                'Doctors on Leave' => $data['doctors_on_leave'] ?? 0
            ];
            
            foreach ($metrics as $label => $value) {
                $pdf->Cell(60, 10, $label, 1);
                $pdf->Cell(30, 10, $value, 1);
                $pdf->Ln();
            }
        }
    }

    private function generateServicesPdfContent($pdf, $data) {
        // Add table headers
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 10, 'Metric', 1);
        $pdf->Cell(30, 10, 'Count', 1);
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 12);
        
        // Add statistics rows
        $metrics = [
            'Total Services' => $data['total_services'] ?? 0,
            'Active Services' => $data['active_services'] ?? 0
        ];
        
        foreach ($metrics as $label => $value) {
            $pdf->Cell(60, 10, $label, 1);
            $pdf->Cell(30, 10, $value, 1);
            $pdf->Ln();
        }
    }

    private function generateFinancialPdfContent($pdf, $data) {
        // Add table headers
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 10, 'Metric', 1);
        $pdf->Cell(30, 10, 'Amount', 1);
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 12);
        
        // Add statistics rows
        $metrics = [
            'Total Revenue' => '$' . number_format($data['total_revenue'] ?? 0, 2),
            'Paid Invoices' => '$' . number_format($data['paid_invoices'] ?? 0, 2),
            'Pending Payments' => '$' . number_format($data['pending_payments'] ?? 0, 2),
            'Average Invoice' => '$' . number_format($data['average_invoice'] ?? 0, 2)
        ];
        
        foreach ($metrics as $label => $value) {
            $pdf->Cell(60, 10, $label, 1);
            $pdf->Cell(30, 10, $value, 1);
            $pdf->Ln();
        }
    }

    /**
     * Process add doctor form submission
     */
    public function process_add_doctor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $specialization = $_POST['specialization'] ?? '';
        $license_number = $_POST['license_number'] ?? '';
        $experience_years = $_POST['experience_years'] ?? '';
        $availability_status = $_POST['availability_status'] ?? 'available';

        // Validate input
        if (empty($name) || empty($email) || empty($specialization) || empty($license_number)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: index.php?module=admin&action=add_doctor');
            exit;
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // First create user account with minimal fields
            $sql = "INSERT INTO users (name, email, user_type) VALUES (:name, :email, 'doctor')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'email' => $email
            ]);
            
            $user_id = $this->db->lastInsertId();

            // Then create doctor record
            $sql = "INSERT INTO doctors (user_id, name, email, specialization, license_number, experience_years, availability_status) 
                    VALUES (:user_id, :name, :email, :specialization, :license_number, :experience_years, :availability_status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'specialization' => $specialization,
                'license_number' => $license_number,
                'experience_years' => $experience_years,
                'availability_status' => $availability_status
            ]);

            // Commit transaction
            $this->db->commit();

            $_SESSION['success'] = 'Doctor added successfully. A user account has been created with their email.';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error adding doctor: " . $e->getMessage());
            $_SESSION['error'] = 'Error adding doctor: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=add_doctor');
            exit;
        }
    }

    /**
     * Display edit doctor form
     */
    public function edit_doctor() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid doctor ID';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM doctors WHERE id = ?");
            $stmt->execute([$id]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doctor) {
                $_SESSION['error'] = 'Doctor not found';
                header('Location: index.php?module=admin&action=doctors');
                exit;
            }

            $this->render('edit_doctor', ['doctor' => $doctor]);
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error fetching doctor data: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=doctors');
            exit;
        }
    }

    /**
     * Process edit doctor form submission
     */
    public function process_edit_doctor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid doctor ID';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $specialization = $_POST['specialization'] ?? '';
        $license_number = $_POST['license_number'] ?? '';
        $experience_years = $_POST['experience_years'] ?? '';
        $availability_status = $_POST['availability_status'] ?? 'available';

        // Validate input
        if (empty($name) || empty($email) || empty($specialization) || empty($license_number)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header("Location: index.php?module=admin&action=edit_doctor&id=$id");
            exit;
        }

        try {
            $sql = "UPDATE doctors SET 
                    name = :name, 
                    email = :email, 
                    specialization = :specialization, 
                    license_number = :license_number, 
                    experience_years = :experience_years, 
                    availability_status = :availability_status,
                    consultation_fee = :consultation_fee 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'specialization' => $specialization,
                'license_number' => $license_number,
                'experience_years' => $experience_years,
                'availability_status' => $availability_status,
                'consultation_fee' => floatval($_POST['consultation_fee'] ?? 0)
            ]);

            $_SESSION['success'] = 'Doctor updated successfully';
            header('Location: index.php?module=admin&action=doctors');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error updating doctor: ' . $e->getMessage();
            header("Location: index.php?module=admin&action=edit_doctor&id=$id");
            exit;
        }
    }

    /**
     * Display add service provider form
     */
    public function add_service() {
        $this->render('add_service');
    }

    /**
     * Process add service provider form submission
     */
    public function process_add_service() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=services');
            exit;
        }

        $company_name = $_POST['company_name'] ?? '';
        $service_type = $_POST['service_type'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $operating_hours = $_POST['operating_hours'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $address = $_POST['address'] ?? '';
        $service_area = $_POST['service_area'] ?? '';
        $license_number = $_POST['license_number'] ?? '';

        // Validate input
        if (empty($company_name) || empty($service_type) || empty($email)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: index.php?module=admin&action=add_service');
            exit;
        }

        try {
            // Create a user account for the service provider
            $password = $this->generateRandomPassword();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $this->db->beginTransaction();
            
            // Insert into users table
            $sql = "INSERT INTO users (email, password_hash, name, user_type, status) 
                    VALUES (:email, :password_hash, :name, 'service', :status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $email,
                'password_hash' => $password_hash,
                'name' => $company_name,
                'status' => $status
            ]);
            
            $user_id = $this->db->lastInsertId();
            
            // Insert into service_providers table
            $sql = "INSERT INTO service_providers (user_id, company_name, email, phone, service_type, address, operating_hours, service_area, license_number, status) 
                    VALUES (:user_id, :company_name, :email, :phone, :service_type, :address, :operating_hours, :service_area, :license_number, :status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'company_name' => $company_name,
                'service_type' => $service_type,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'operating_hours' => $operating_hours,
                'service_area' => $service_area,
                'license_number' => $license_number,
                'status' => $status
            ]);
            
            $this->db->commit();

            $_SESSION['success'] = 'Service provider added successfully. Temporary password: ' . $password;
            header('Location: index.php?module=admin&action=services');
            exit;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Error adding service provider: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=add_service');
            exit;
        }
    }
    
    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    /**
     * Display edit service provider form
     */
    public function edit_service() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid service provider ID';
            header('Location: index.php?module=admin&action=services');
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT sp.*, u.status as user_status 
                                        FROM service_providers sp 
                                        JOIN users u ON sp.user_id = u.id 
                                        WHERE sp.id = ?");
            $stmt->execute([$id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                $_SESSION['error'] = 'Service provider not found';
                header('Location: index.php?module=admin&action=services');
                exit;
            }

            $this->render('edit_service', [
                'service' => $service
            ]);
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error fetching service provider: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=services');
            exit;
        }
    }

    /**
     * Process edit service provider form submission
     */
    public function process_edit_service() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=services');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid service provider ID';
            header('Location: index.php?module=admin&action=services');
            exit;
        }

        $company_name = $_POST['company_name'] ?? '';
        $service_type = $_POST['service_type'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $operating_hours = $_POST['operating_hours'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $address = $_POST['address'] ?? '';
        $service_area = $_POST['service_area'] ?? '';
        $license_number = $_POST['license_number'] ?? '';
        $user_id = $_POST['user_id'] ?? null;

        // Validate input
        if (empty($company_name) || empty($service_type) || empty($email) || !$user_id) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header("Location: index.php?module=admin&action=edit_service&id=$id");
            exit;
        }

        try {
            $this->db->beginTransaction();
            
            // Update service_providers table
            $sql = "UPDATE service_providers SET 
                    company_name = :company_name, 
                    service_type = :service_type, 
                    email = :email, 
                    phone = :phone, 
                    operating_hours = :operating_hours, 
                    address = :address,
                    service_area = :service_area,
                    license_number = :license_number,
                    status = :status 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'company_name' => $company_name,
                'service_type' => $service_type,
                'email' => $email,
                'phone' => $phone,
                'operating_hours' => $operating_hours,
                'address' => $address,
                'service_area' => $service_area,
                'license_number' => $license_number,
                'status' => $status
            ]);
            
            // Update users table
            $sql = "UPDATE users SET 
                    name = :name,
                    email = :email,
                    status = :status
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $user_id,
                'name' => $company_name,
                'email' => $email,
                'status' => $status
            ]);
            
            $this->db->commit();

            $_SESSION['success'] = 'Service provider updated successfully';
            header('Location: index.php?module=admin&action=services');
            exit;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Error updating service provider: ' . $e->getMessage();
            header("Location: index.php?module=admin&action=edit_service&id=$id");
            exit;
        }
    }

    /**
     * Display admin token management page
     */
    public function admin_tokens() {
        // Check if user is super admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || $_SESSION['admin_level'] !== 'super') {
            $_SESSION['error'] = 'Access denied. Super admin privileges required.';
            header('Location: index.php?module=admin&action=dashboard');
            exit;
        }

        try {
            // Get all tokens with generator information
            $query = "SELECT t.*, u.name as generated_by_name 
                     FROM admin_tokens t 
                     LEFT JOIN users u ON t.generated_by = u.id 
                     ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Load the view
            require_once 'modules/admin/views/admin_tokens.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error loading tokens: ' . $e->getMessage();
            header('Location: index.php?module=admin&action=dashboard');
            exit;
        }
    }

    /**
     * Generate a new admin token
     */
    public function generate_token() {
        // Check if user is super admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || $_SESSION['admin_level'] !== 'super') {
            $_SESSION['error'] = 'Access denied. Super admin privileges required.';
            header('Location: index.php?module=admin&action=dashboard');
            exit;
        }

        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: index.php?module=admin&action=admin_tokens');
            exit;
        }

        // Validate input
        $admin_level = $_POST['admin_level'] ?? '';
        $expiry_hours = intval($_POST['expiry_hours'] ?? 24);

        if (!in_array($admin_level, ['standard', 'super'])) {
            $_SESSION['error'] = 'Invalid admin level specified';
            header('Location: index.php?module=admin&action=admin_tokens');
            exit;
        }

        if ($expiry_hours < 1 || $expiry_hours > 168) {
            $_SESSION['error'] = 'Invalid expiry time specified';
            header('Location: index.php?module=admin&action=admin_tokens');
            exit;
        }

        try {
            // Generate a secure random token
            $token = bin2hex(random_bytes(32));
            
            // Calculate expiry timestamp
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$expiry_hours} hours"));

            // Insert the token
            $query = "INSERT INTO admin_tokens (token, admin_level, generated_by, expires_at) 
                     VALUES (:token, :admin_level, :generated_by, :expires_at)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'token' => $token,
                'admin_level' => $admin_level,
                'generated_by' => $_SESSION['user_id'],
                'expires_at' => $expires_at
            ]);

            $_SESSION['success'] = 'Token generated successfully';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error generating token: ' . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error generating secure token';
        }

        header('Location: index.php?module=admin&action=admin_tokens');
        exit;
    }

    /**
     * Revoke an admin token
     */
    public function revoke_token() {
        // Check if user is super admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin' || $_SESSION['admin_level'] !== 'super') {
            $_SESSION['error'] = 'Access denied. Super admin privileges required.';
            header('Location: index.php?module=admin&action=dashboard');
            exit;
        }

        $token_id = $_GET['id'] ?? 0;

        try {
            // Update token expiry to current timestamp
            $query = "UPDATE admin_tokens SET expires_at = CURRENT_TIMESTAMP 
                     WHERE id = :id AND is_used = 0 AND expires_at > CURRENT_TIMESTAMP";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $token_id]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Token revoked successfully';
            } else {
                $_SESSION['error'] = 'Token not found or already expired/used';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error revoking token: ' . $e->getMessage();
        }

        header('Location: index.php?module=admin&action=admin_tokens');
        exit;
    }

    /**
     * Validate an admin registration token
     */
    private function validate_admin_token($token) {
        try {
            $query = "SELECT * FROM admin_tokens 
                     WHERE token = :token 
                     AND is_used = 0 
                     AND expires_at > CURRENT_TIMESTAMP";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mark a token as used
     */
    private function mark_token_used($token_id, $user_id) {
        try {
            $query = "UPDATE admin_tokens 
                     SET is_used = 1, used_by = :user_id, used_at = CURRENT_TIMESTAMP 
                     WHERE id = :token_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'token_id' => $token_id,
                'user_id' => $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Display and handle admin profile
     */
    public function profile() {
        try {
            // Get admin details
            $stmt = $this->db->prepare("
                SELECT a.*, u.email, u.name, u.status 
                FROM admins a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                throw new Exception("Admin profile not found");
            }

            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Validate current password
                if (!empty($current_password)) {
                    $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!password_verify($current_password, $user['password_hash'])) {
                        throw new Exception("Current password is incorrect");
                    }

                    // Validate new password
                    if (empty($new_password) || strlen($new_password) < 8) {
                        throw new Exception("New password must be at least 8 characters long");
                    }

                    if ($new_password !== $confirm_password) {
                        throw new Exception("New passwords do not match");
                    }

                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                }

                // Update name and email
                $this->db->beginTransaction();

                // Update users table
                $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $_SESSION['user_id']]);

                // Update admins table
                $stmt = $this->db->prepare("UPDATE admins SET name = ?, email = ? WHERE user_id = ?");
                $stmt->execute([$name, $email, $_SESSION['user_id']]);

                $this->db->commit();

                // Update session variables
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;

                $_SESSION['success'] = "Profile updated successfully";
                header("Location: index.php?module=admin&action=profile");
                exit;
            }

            // Render profile view
            $this->render('profile', [
                'page_title' => 'Admin Profile',
                'admin' => $admin
            ]);

        } catch (Exception $e) {
            $this->logError("Error in profile action: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            
            // If we have admin data, render the view with error
            if (isset($admin)) {
                $this->render('profile', [
                    'page_title' => 'Admin Profile',
                    'admin' => $admin
                ]);
            } else {
                // If we don't have admin data, redirect to dashboard
                header("Location: index.php?module=admin&action=dashboard");
                exit;
            }
        }
    }

    /**
     * Display list of pharmacies
     */
    public function pharmacies() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.email, u.status as user_status
                FROM pharmacies p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.name ASC
            ");
            $stmt->execute();
            $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('pharmacies', [
                'page_title' => 'Manage Pharmacies',
                'pharmacies' => $pharmacies
            ]);
        } catch (Exception $e) {
            $this->logError("Error loading pharmacies: " . $e->getMessage());
            $_SESSION['error'] = "Error loading pharmacies";
            $this->redirect('index.php?module=admin&action=dashboard');
        }
    }
    
    /**
     * Display add pharmacy form
     */
    public function add_pharmacy() {
        $this->render('add_pharmacy', [
            'page_title' => 'Add New Pharmacy'
        ]);
    }
    
    /**
     * Process add pharmacy form
     */
    public function process_add_pharmacy() {
        try {
            // Validate input
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $operating_hours = trim($_POST['operating_hours'] ?? '');
            $delivery_available = isset($_POST['delivery_available']) ? 1 : 0;
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($license_number)) {
                $_SESSION['error'] = "Name, email, and license number are required";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=admin&action=add_pharmacy');
                return;
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=admin&action=add_pharmacy');
                return;
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email already exists";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=admin&action=add_pharmacy');
                return;
            }
            
            // Check if license number already exists
            $stmt = $this->db->prepare("SELECT id FROM pharmacies WHERE license_number = ?");
            $stmt->execute([$license_number]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "License number already exists";
                $_SESSION['form_data'] = $_POST;
                $this->redirect('index.php?module=admin&action=add_pharmacy');
                return;
            }
            
            // Generate random password
            $password = $this->generateRandomPassword(12);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Create user account
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, name, user_type, status)
                VALUES (?, ?, ?, 'service', 'active')
            ");
            $stmt->execute([$email, $password_hash, $name]);
            $user_id = $this->db->lastInsertId();
            
            // Create pharmacy record
            $stmt = $this->db->prepare("
                INSERT INTO pharmacies (
                    user_id, name, email, phone, address, license_number, 
                    operating_hours, delivery_available, status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, 'active'
                )
            ");
            $stmt->execute([
                $user_id, $name, $email, $phone, $address, $license_number,
                $operating_hours, $delivery_available
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            $_SESSION['success'] = "Pharmacy added successfully. Temporary password: " . $password;
            $this->redirect('index.php?module=admin&action=pharmacies');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error adding pharmacy: " . $e->getMessage());
            $_SESSION['error'] = "Error adding pharmacy: " . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            $this->redirect('index.php?module=admin&action=add_pharmacy');
        }
    }
    
    /**
     * Display edit pharmacy form
     */
    public function edit_pharmacy() {
        try {
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = "Invalid pharmacy ID";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT p.*, u.email, u.status as user_status
                FROM pharmacies p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pharmacy) {
                $_SESSION['error'] = "Pharmacy not found";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            $this->render('edit_pharmacy', [
                'page_title' => 'Edit Pharmacy',
                'pharmacy' => $pharmacy
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading pharmacy: " . $e->getMessage());
            $_SESSION['error'] = "Error loading pharmacy";
            $this->redirect('index.php?module=admin&action=pharmacies');
        }
    }
    
    /**
     * Process edit pharmacy form
     */
    public function process_edit_pharmacy() {
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = "Invalid pharmacy ID";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            // Validate input
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $operating_hours = trim($_POST['operating_hours'] ?? '');
            $delivery_available = isset($_POST['delivery_available']) ? 1 : 0;
            $status = $_POST['status'] ?? 'active';
            $user_status = $_POST['user_status'] ?? 'active';
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($license_number)) {
                $_SESSION['error'] = "Name, email, and license number are required";
                $this->redirect('index.php?module=admin&action=edit_pharmacy&id=' . $id);
                return;
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                $this->redirect('index.php?module=admin&action=edit_pharmacy&id=' . $id);
                return;
            }
            
            // Get current pharmacy data
            $stmt = $this->db->prepare("
                SELECT p.*, u.email as current_email, u.id as user_id
                FROM pharmacies p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current) {
                $_SESSION['error'] = "Pharmacy not found";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            // Check if email already exists (if changed)
            if ($email !== $current['current_email']) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $current['user_id']]);
                if ($stmt->rowCount() > 0) {
                    $_SESSION['error'] = "Email already exists";
                    $this->redirect('index.php?module=admin&action=edit_pharmacy&id=' . $id);
                    return;
                }
            }
            
            // Check if license number already exists (if changed)
            if ($license_number !== $current['license_number']) {
                $stmt = $this->db->prepare("SELECT id FROM pharmacies WHERE license_number = ? AND id != ?");
                $stmt->execute([$license_number, $id]);
                if ($stmt->rowCount() > 0) {
                    $_SESSION['error'] = "License number already exists";
                    $this->redirect('index.php?module=admin&action=edit_pharmacy&id=' . $id);
                    return;
                }
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update user record
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email = ?, name = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$email, $name, $user_status, $current['user_id']]);
            
            // Update pharmacy record
            $stmt = $this->db->prepare("
                UPDATE pharmacies 
                SET name = ?, email = ?, phone = ?, address = ?, 
                    license_number = ?, operating_hours = ?, 
                    delivery_available = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name, $email, $phone, $address, $license_number,
                $operating_hours, $delivery_available, $status, $id
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            $_SESSION['success'] = "Pharmacy updated successfully";
            $this->redirect('index.php?module=admin&action=pharmacies');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            $this->logError("Error updating pharmacy: " . $e->getMessage());
            $_SESSION['error'] = "Error updating pharmacy: " . $e->getMessage();
            $this->redirect('index.php?module=admin&action=edit_pharmacy&id=' . ($id ?? 0));
        }
    }
    
    /**
     * Reset pharmacy password
     */
    public function reset_pharmacy_password() {
        try {
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = "Invalid pharmacy ID";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            // Get pharmacy user ID
            $stmt = $this->db->prepare("
                SELECT user_id FROM pharmacies WHERE id = ?
            ");
            $stmt->execute([$id]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pharmacy) {
                $_SESSION['error'] = "Pharmacy not found";
                $this->redirect('index.php?module=admin&action=pharmacies');
                return;
            }
            
            // Generate new password
            $password = $this->generateRandomPassword(12);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user password
            $stmt = $this->db->prepare("
                UPDATE users SET password_hash = ? WHERE id = ?
            ");
            $stmt->execute([$password_hash, $pharmacy['user_id']]);
            
            $_SESSION['success'] = "Password reset successfully. New temporary password: " . $password;
            $this->redirect('index.php?module=admin&action=pharmacies');
            
        } catch (Exception $e) {
            $this->logError("Error resetting pharmacy password: " . $e->getMessage());
            $_SESSION['error'] = "Error resetting pharmacy password";
            $this->redirect('index.php?module=admin&action=pharmacies');
        }
    }

    /**
     * Display patient reported issues
     */
    public function patient_issues() {
        try {
            // Check if table exists
            $stmt = $this->db->query("SHOW TABLES LIKE 'patient_issues'");
            $table_exists = ($stmt->rowCount() > 0);
            
            $issues = [];
            $counts = [
                'pending' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed' => 0
            ];
            
            if ($table_exists) {
                // Get filter from URL if present
                $filter = isset($_GET['filter']) ? htmlspecialchars($_GET['filter'], ENT_QUOTES, 'UTF-8') : null;
                
                // Build query based on filter
                $query = "
                    SELECT pi.*, p.name as patient_name, p.profile_image as patient_profile_image, u.email as patient_email
                    FROM patient_issues pi
                    JOIN patients p ON pi.patient_id = p.id
                    JOIN users u ON p.user_id = u.id
                ";
                
                $params = [];
                if ($filter) {
                    $query .= " WHERE pi.status = ?";
                    $params[] = $filter;
                }
                
                $query .= " ORDER BY pi.created_at DESC";
                
                // Get issues
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get counts for status filter
                $stmt = $this->db->query("
                    SELECT status, COUNT(*) as count
                    FROM patient_issues
                    GROUP BY status
                ");
                $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($status_counts as $status_count) {
                    $counts[$status_count['status']] = $status_count['count'];
                }
            }
            
            $this->render('patient_issues', [
                'page_title' => 'Patient Reported Issues',
                'issues' => $issues,
                'counts' => $counts,
                'filter' => $filter ?? null
            ]);
        } catch (PDOException $e) {
            $this->logError("Error in patient_issues: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading patient issues.";
            $this->redirect('index.php?module=admin&action=dashboard');
        }
    }
    
    /**
     * View details of a specific patient issue
     */
    public function view_patient_issue() {
        try {
            // Validate and sanitize issue ID
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                $_SESSION['error'] = "Invalid issue ID.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            $issue_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Get issue details with patient info
            $stmt = $this->db->prepare("
                SELECT pi.*
                FROM patient_issues pi
                WHERE pi.id = ?
            ");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$issue) {
                $_SESSION['error'] = "Issue not found.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            // Get patient details
            $stmt = $this->db->prepare("
                SELECT p.*, u.email
                FROM patients p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$issue['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
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
            
            // Get other issues from this patient
            $stmt = $this->db->prepare("
                SELECT * FROM patient_issues
                WHERE patient_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$issue['patient_id']]);
            $other_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('view_patient_issue', [
                'page_title' => 'View Patient Issue',
                'issue' => $issue,
                'patient' => $patient,
                'reference_details' => $reference_details,
                'other_issues' => $other_issues
            ]);
        } catch (PDOException $e) {
            $this->logError("Error in view_patient_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while loading the issue details.";
            $this->redirect('index.php?module=admin&action=patient_issues');
        }
    }
    
    /**
     * Respond to a patient issue
     */
    public function respond_to_issue() {
        try {
            // Validate form submission
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            // Sanitize and validate inputs
            $issue_id = filter_var($_POST['issue_id'], FILTER_SANITIZE_NUMBER_INT);
            $admin_response = htmlspecialchars($_POST['admin_response'], ENT_QUOTES, 'UTF-8');
            $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');
            $notify_patient = isset($_POST['notify_patient']) ? true : false;
            
            // Validate required fields
            if (empty($issue_id) || empty($admin_response) || empty($status)) {
                $_SESSION['error'] = "Please fill in all required fields.";
                $this->redirect('index.php?module=admin&action=view_patient_issue&id=' . $issue_id);
                return;
            }
            
            // Get issue details first
            $stmt = $this->db->prepare("SELECT * FROM patient_issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$issue) {
                $_SESSION['error'] = "Issue not found.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            // Update the issue with admin response
            $stmt = $this->db->prepare("
                UPDATE patient_issues
                SET admin_response = ?, status = ?, updated_at = NOW()
                " . ($status === 'resolved' ? ", resolved_at = NOW()" : "") . "
                WHERE id = ?
            ");
            $stmt->execute([$admin_response, $status, $issue_id]);
            
            // Notify the patient if requested
            if ($notify_patient) {
                // Get patient details
                $stmt = $this->db->prepare("
                    SELECT p.*, u.email 
                    FROM patients p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.id = ?
                ");
                $stmt->execute([$issue['patient_id']]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    // Create patient alert if alerts table exists
                    $stmt = $this->db->query("SHOW TABLES LIKE 'patient_alerts'");
                    if ($stmt->rowCount() > 0) {
                        $stmt = $this->db->prepare("
                            INSERT INTO patient_alerts (
                                patient_id, title, message, alert_type, reference_id, created_at
                            ) VALUES (?, ?, ?, 'system', ?, NOW())
                        ");
                        
                        $alert_title = "Response to your issue #" . $issue_id;
                        $alert_message = "Your reported issue '" . $issue['title'] . "' has been updated. Status: " . ucfirst($status);
                        
                        $stmt->execute([
                            $patient['id'],
                            $alert_title,
                            $alert_message,
                            $issue_id
                        ]);
                    }
                    
                    // In a real system, you might also send an email notification here
                }
            }
            
            $_SESSION['success'] = "Your response has been submitted successfully.";
            $this->redirect('index.php?module=admin&action=view_patient_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in respond_to_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while submitting your response.";
            $this->redirect('index.php?module=admin&action=patient_issues');
        }
    }
    
    /**
     * Update the status of a patient issue
     */
    public function update_patient_issue_status() {
        try {
            // Validate and sanitize inputs
            if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['status'])) {
                $_SESSION['error'] = "Invalid request.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            $issue_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            $status = htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8');
            
            // Validate status value
            $valid_statuses = ['pending', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $valid_statuses)) {
                $_SESSION['error'] = "Invalid status value.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            // Update issue status
            $stmt = $this->db->prepare("
                UPDATE patient_issues
                SET status = ?, updated_at = NOW()
                " . ($status === 'resolved' ? ", resolved_at = NOW()" : "") . "
                WHERE id = ?
            ");
            $stmt->execute([$status, $issue_id]);
            
            $_SESSION['success'] = "Issue status updated to " . ucfirst($status) . ".";
            $this->redirect('index.php?module=admin&action=view_patient_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in update_patient_issue_status: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating the issue status.";
            $this->redirect('index.php?module=admin&action=patient_issues');
        }
    }
    
    /**
     * Reopen a resolved or closed issue
     */
    public function reopen_issue() {
        try {
            // Validate and sanitize issue ID
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                $_SESSION['error'] = "Invalid issue ID.";
                $this->redirect('index.php?module=admin&action=patient_issues');
                return;
            }
            
            $issue_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Update issue status to in_progress
            $stmt = $this->db->prepare("
                UPDATE patient_issues
                SET status = 'in_progress', updated_at = NOW(), resolved_at = NULL
                WHERE id = ?
            ");
            $stmt->execute([$issue_id]);
            
            $_SESSION['success'] = "Issue has been reopened and set to In Progress.";
            $this->redirect('index.php?module=admin&action=view_patient_issue&id=' . $issue_id);
            
        } catch (PDOException $e) {
            $this->logError("Error in reopen_issue: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while reopening the issue.";
            $this->redirect('index.php?module=admin&action=patient_issues');
        }
    }
}
?>
