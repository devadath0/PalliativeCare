<?php
/**
 * Booking Reminder Class
 * Handles sending reminders for upcoming bookings
 * This class is designed to be used with a cron job
 */
class BookingReminder {
    private $db;
    private $emailService;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/../config/config.php';
        require_once __DIR__ . '/../classes/Database.php';
        require_once __DIR__ . '/../classes/EmailService.php';
        
        $this->db = Database::getInstance();
        $this->emailService = new EmailService();
    }
    
    /**
     * Send reminders for upcoming transport bookings
     * Looks for bookings that are 24 hours away and have status 'confirmed'
     * 
     * @return array Results of the reminder sending operation
     */
    public function sendTransportReminders() {
        $results = [
            'success' => 0,
            'failure' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        try {
            // Get bookings scheduled for tomorrow with status 'confirmed'
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
                WHERE cb.status = 'confirmed'
                AND DATE(cb.pickup_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                AND cb.reminder_sent = 0
            ");
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($bookings)) {
                error_log("No upcoming transport bookings found for reminders");
                $results['skipped'] = 0;
                return $results;
            }
            
            foreach ($bookings as $booking) {
                try {
                    $bookingData = [
                        'id' => $booking['id'],
                        'service_type' => 'Transportation',
                        'date' => date('F j, Y', strtotime($booking['pickup_date'])),
                        'time' => date('h:i A', strtotime($booking['pickup_time'])),
                        'provider_name' => $booking['provider_name'],
                        'pickup_address' => $booking['pickup_address'],
                        'destination' => $booking['destination']
                    ];
                    
                    $sent = $this->emailService->sendBookingReminder(
                        $booking['patient_email'],
                        $booking['patient_name'],
                        $bookingData
                    );
                    
                    if ($sent) {
                        // Update the booking to mark reminder as sent
                        $updateStmt = $this->db->prepare("
                            UPDATE cab_bookings 
                            SET reminder_sent = 1, 
                                reminder_sent_at = NOW() 
                            WHERE id = ?
                        ");
                        $updateStmt->execute([$booking['id']]);
                        
                        $results['success']++;
                    } else {
                        $results['failure']++;
                        $results['errors'][] = "Failed to send reminder for booking ID: " . $booking['id'];
                    }
                } catch (Exception $e) {
                    error_log("Error sending transport booking reminder: " . $e->getMessage());
                    $results['failure']++;
                    $results['errors'][] = "Error for booking ID " . $booking['id'] . ": " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            error_log("Error in transport reminders: " . $e->getMessage());
            $results['errors'][] = "System error: " . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Send reminders for upcoming appointments
     * Looks for appointments that are 24 hours away and have status 'confirmed'
     * 
     * @return array Results of the reminder sending operation
     */
    public function sendAppointmentReminders() {
        $results = [
            'success' => 0,
            'failure' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        try {
            // Get appointments scheduled for tomorrow with status 'confirmed'
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    p.name as patient_name, 
                    p.phone as patient_phone,
                    p.email as patient_email,
                    DATE(a.appointment_date) as appointment_date,
                    TIME(a.appointment_date) as appointment_time,
                    d.name as doctor_name
                FROM appointments a
                INNER JOIN patients p ON a.patient_id = p.id 
                INNER JOIN doctors d ON a.doctor_id = d.id
                WHERE a.status = 'confirmed'
                AND DATE(a.appointment_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                AND a.reminder_sent = 0
            ");
            $stmt->execute();
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($appointments)) {
                error_log("No upcoming appointments found for reminders");
                $results['skipped'] = 0;
                return $results;
            }
            
            foreach ($appointments as $appointment) {
                try {
                    $appointmentData = [
                        'id' => $appointment['id'],
                        'doctor_name' => $appointment['doctor_name'],
                        'date' => date('F j, Y', strtotime($appointment['appointment_date'])),
                        'time' => date('h:i A', strtotime($appointment['appointment_time'])),
                        'location' => 'Not specified' // Since we don't have hospital information
                    ];
                    
                    $sent = $this->emailService->sendAppointmentReminder(
                        $appointment['patient_email'],
                        $appointment['patient_name'],
                        $appointmentData
                    );
                    
                    if ($sent) {
                        // Update the appointment to mark reminder as sent
                        $updateStmt = $this->db->prepare("
                            UPDATE appointments 
                            SET reminder_sent = 1, 
                                reminder_sent_at = NOW() 
                            WHERE id = ?
                        ");
                        $updateStmt->execute([$appointment['id']]);
                        
                        $results['success']++;
                    } else {
                        $results['failure']++;
                        $results['errors'][] = "Failed to send reminder for appointment ID: " . $appointment['id'];
                    }
                } catch (Exception $e) {
                    error_log("Error sending appointment reminder: " . $e->getMessage());
                    $results['failure']++;
                    $results['errors'][] = "Error for appointment ID " . $appointment['id'] . ": " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            error_log("Error in appointment reminders: " . $e->getMessage());
            $results['errors'][] = "System error: " . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Run all reminders
     * This is the main method that should be called from a cron job
     * 
     * @return array Summary of all reminder operations
     */
    public function runAllReminders() {
        $startTime = microtime(true);
        
        $transportResults = $this->sendTransportReminders();
        $appointmentResults = $this->sendAppointmentReminders();
        
        $summary = [
            'execution_time' => round(microtime(true) - $startTime, 2) . ' seconds',
            'transport_reminders' => $transportResults,
            'appointment_reminders' => $appointmentResults,
            'total_success' => $transportResults['success'] + $appointmentResults['success'],
            'total_failure' => $transportResults['failure'] + $appointmentResults['failure'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log the summary
        error_log("Reminder job completed: " . json_encode($summary));
        
        return $summary;
    }
} 