<?php
/**
 * Email Service Class
 * Handles sending email notifications in the system
 */
class EmailService {
    private $mailer;
    private $fromEmail;
    private $fromName;
    private $replyTo;
    private $isEnabled;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Load PHPMailer library
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $this->isEnabled = defined('MAIL_ENABLED') ? MAIL_ENABLED : false;
        $this->fromEmail = defined('MAIL_FROM') ? MAIL_FROM : 'no-reply@palliative.care';
        $this->fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Palliative Care System';
        $this->replyTo = defined('MAIL_REPLY_TO') ? MAIL_REPLY_TO : 'support@palliative.care';
        
        if ($this->isEnabled) {
            try {
                $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
                $this->mailer->isSMTP();
                $this->mailer->Host = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.yourdomain.com';
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
                $this->mailer->Password = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
                $this->mailer->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
                $this->mailer->Port = defined('MAIL_PORT') ? MAIL_PORT : 587;
                $this->mailer->setFrom($this->fromEmail, $this->fromName);
                $this->mailer->addReplyTo($this->replyTo, $this->fromName);
                $this->mailer->isHTML(true);
            } catch (Exception $e) {
                error_log('Email service initialization error: ' . $e->getMessage());
                $this->isEnabled = false;
            }
        }
    }
    
    /**
     * Send a payment confirmation email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $paymentData Payment details
     * @return bool Whether the email was sent successfully
     */
    public function sendPaymentConfirmation($toEmail, $toName, $paymentData) {
        $subject = 'Payment Confirmation - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Payment Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Payment Confirmation</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>Your payment has been successfully processed. Here are the details:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Payment ID</th>
                                <td>{$paymentData['id']}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>\${$paymentData['amount']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$paymentData['date']}</td>
                            </tr>
                            <tr>
                                <th>Service</th>
                                <td>{$paymentData['service']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Thank you for using our services.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send a booking confirmation email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $bookingData Booking details
     * @return bool Whether the email was sent successfully
     */
    public function sendBookingConfirmation($toEmail, $toName, $bookingData) {
        $subject = 'Booking Confirmation - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Booking Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Booking Confirmation</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>Your booking has been confirmed. Here are the details:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Booking ID</th>
                                <td>{$bookingData['id']}</td>
                            </tr>
                            <tr>
                                <th>Service Type</th>
                                <td>{$bookingData['service_type']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$bookingData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$bookingData['time']}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{$bookingData['provider_name']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Thank you for using our services.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send a booking reminder email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $bookingData Booking details
     * @return bool Whether the email was sent successfully
     */
    public function sendBookingReminder($toEmail, $toName, $bookingData) {
        $subject = 'Reminder: Upcoming Booking - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Booking Reminder</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF9800; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Booking Reminder</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>This is a reminder for your upcoming booking:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Booking ID</th>
                                <td>{$bookingData['id']}</td>
                            </tr>
                            <tr>
                                <th>Service Type</th>
                                <td>{$bookingData['service_type']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$bookingData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$bookingData['time']}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{$bookingData['provider_name']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send an appointment confirmation email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $appointmentData Appointment details
     * @return bool Whether the email was sent successfully
     */
    public function sendAppointmentConfirmation($toEmail, $toName, $appointmentData) {
        $subject = 'Appointment Confirmation - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Appointment Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #673AB7; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Confirmation</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>Your appointment has been confirmed. Here are the details:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Appointment ID</th>
                                <td>{$appointmentData['id']}</td>
                            </tr>
                            <tr>
                                <th>Doctor</th>
                                <td>{$appointmentData['doctor_name']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$appointmentData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$appointmentData['time']}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{$appointmentData['location']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Please arrive 15 minutes before your scheduled appointment time.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send an appointment reminder email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $appointmentData Appointment details
     * @return bool Whether the email was sent successfully
     */
    public function sendAppointmentReminder($toEmail, $toName, $appointmentData) {
        $subject = 'Reminder: Upcoming Appointment - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Appointment Reminder</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #9C27B0; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Reminder</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>This is a reminder for your upcoming appointment:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Appointment ID</th>
                                <td>{$appointmentData['id']}</td>
                            </tr>
                            <tr>
                                <th>Doctor</th>
                                <td>{$appointmentData['doctor_name']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$appointmentData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$appointmentData['time']}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{$appointmentData['location']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Please arrive 15 minutes before your scheduled appointment time.</p>
                    <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send a booking status update email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $bookingData Booking details
     * @param string $status New status
     * @return bool Whether the email was sent successfully
     */
    public function sendBookingStatusUpdate($toEmail, $toName, $bookingData, $status) {
        $statusMapping = [
            'confirmed' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled'
        ];
        
        $status = isset($statusMapping[$status]) ? $statusMapping[$status] : $status;
        
        $subject = 'Booking Update: Your booking has been ' . ucfirst($status) . ' - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Booking Status Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #3F51B5; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
                .status { font-weight: bold; }
                .confirmed { color: #4CAF50; }
                .completed { color: #2196F3; }
                .cancelled { color: #F44336; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Booking Status Update</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>The status of your booking has been updated to <span class='status {$status}'>" . ucfirst($status) . "</span>.</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Booking ID</th>
                                <td>{$bookingData['id']}</td>
                            </tr>
                            <tr>
                                <th>Service Type</th>
                                <td>{$bookingData['service_type']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$bookingData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$bookingData['time']}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{$bookingData['provider_name']}</td>
                            </tr>
                        </table>
                    </div>";
        
        if ($status == 'cancelled') {
            $message .= "<p>If you have any questions about this cancellation, please contact us.</p>";
        } elseif ($status == 'confirmed') {
            $message .= "<p>Your booking is now confirmed. We look forward to seeing you.</p>";
        } elseif ($status == 'completed') {
            $message .= "<p>Thank you for using our services. We hope everything was satisfactory.</p>";
        }
        
        $message .= "
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send an appointment status update email
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $appointmentData Appointment details
     * @param string $status New status
     * @return bool Whether the email was sent successfully
     */
    public function sendAppointmentStatusUpdate($toEmail, $toName, $appointmentData, $status) {
        $statusMapping = [
            'confirmed' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled'
        ];
        
        $status = isset($statusMapping[$status]) ? $statusMapping[$status] : $status;
        
        $subject = 'Appointment Update: Your appointment has been ' . ucfirst($status) . ' - ' . SITE_NAME;
        
        $message = "
        <html>
        <head>
            <title>Appointment Status Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #3F51B5; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
                .status { font-weight: bold; }
                .confirmed { color: #4CAF50; }
                .completed { color: #2196F3; }
                .cancelled { color: #F44336; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Status Update</h2>
                </div>
                <div class='content'>
                    <p>Dear {$toName},</p>
                    <p>The status of your appointment has been updated to <span class='status {$status}'>" . ucfirst($status) . "</span>.</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Appointment ID</th>
                                <td>{$appointmentData['id']}</td>
                            </tr>
                            <tr>
                                <th>Doctor</th>
                                <td>{$appointmentData['doctor_name']}</td>
                            </tr>
                            <tr>
                                <th>Specialization</th>
                                <td>{$appointmentData['specialization']}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{$appointmentData['date']}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{$appointmentData['time']}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{$appointmentData['location']}</td>
                            </tr>
                        </table>
                    </div>";
        
        if ($status == 'cancelled') {
            $message .= "<p>If you have any questions about this cancellation, please contact us.</p>";
        } elseif ($status == 'confirmed') {
            $message .= "<p>Your appointment is now confirmed. Please arrive 15 minutes before your scheduled appointment time.</p>";
        } elseif ($status == 'completed') {
            $message .= "<p>Thank you for choosing our services. We hope everything was satisfactory.</p>";
        }
        
        $message .= "
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->_send($toEmail, $toName, $subject, $message);
    }
    
    /**
     * Send a custom email with the provided content
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $message Email message (HTML)
     * @return bool Whether the email was sent successfully
     */
    public function send($toEmail, $toName, $subject, $message) {
        if (!$this->isEnabled) {
            error_log("Email not sent to {$toEmail} - Email service is disabled");
            return false;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $message));
            
            $result = $this->mailer->send();
            error_log("Email sent to {$toEmail} successfully");
            return $result;
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email (private implementation)
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $message Email message (HTML)
     * @return bool Whether the email was sent successfully
     */
    private function _send($toEmail, $toName, $subject, $message) {
        return $this->send($toEmail, $toName, $subject, $message);
    }
} 