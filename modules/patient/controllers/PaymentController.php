<?php
/**
 * Payment Controller
 * Handles all payment-related operations
 */

class PaymentController {
    private $db;
    private $patient;

    public function __construct($db, $patient) {
        $this->db = $db;
        $this->patient = $patient;
    }

    /**
     * Show payment page
     */
    public function showPaymentPage() {
        $payment_type = $_GET['type'] ?? '';
        $reference_id = intval($_GET['id'] ?? 0);

        if (!in_array($payment_type, ['medicine_order', 'cab_booking', 'appointment']) || $reference_id <= 0) {
            $_SESSION['error'] = "Invalid payment request";
            header("Location: index.php?module=patient&action=dashboard");
            exit;
        }

        // Get payment amount based on type
        try {
            switch ($payment_type) {
                case 'medicine_order':
                    $stmt = $this->db->prepare("
                        SELECT total_amount as amount 
                        FROM medicine_orders 
                        WHERE id = ? AND patient_id = ? AND payment_status = 'pending'
                    ");
                    break;
                
                case 'cab_booking':
                    $stmt = $this->db->prepare("
                        SELECT estimated_fare as amount 
                        FROM cab_bookings 
                        WHERE id = ? AND patient_id = ? AND status = 'pending'
                    ");
                    break;
                
                case 'appointment':
                    $stmt = $this->db->prepare("
                        SELECT d.consultation_fee as amount 
                        FROM appointments a 
                        JOIN doctors d ON a.doctor_id = d.id 
                        WHERE a.id = ? AND a.patient_id = ? AND a.status = 'pending'
                    ");
                    break;
            }

            $stmt->execute([$reference_id, $this->patient['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("Invalid payment request or already paid");
            }

            // Extract amount from result
            $amount = floatval($result['amount']);

            // Render payment page
            require_once __DIR__ . '/../views/payment.php';

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: index.php?module=patient&action=dashboard");
            exit;
        }
    }

    /**
     * Process payment
     */
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?module=patient&action=dashboard");
            exit;
        }

        $payment_type = $_POST['payment_type'] ?? '';
        $reference_id = intval($_POST['reference_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? '';

        try {
            // Validate payment data
            if (!in_array($payment_type, ['medicine_order', 'cab_booking', 'appointment'])) {
                throw new Exception("Invalid payment type");
            }

            if ($reference_id <= 0 || $amount <= 0) {
                throw new Exception("Invalid payment details");
            }

            if (!in_array($payment_method, ['credit_card', 'debit_card', 'upi', 'net_banking'])) {
                throw new Exception("Invalid payment method");
            }

            // Start transaction
            $this->db->beginTransaction();

            // Generate transaction ID
            $transaction_id = uniqid('TXN') . time();

            // Create payment record
            $stmt = $this->db->prepare("
                INSERT INTO payments (
                    payment_type, reference_id, amount, payment_method,
                    transaction_id, status, payment_date
                ) VALUES (?, ?, ?, ?, ?, 'completed', NOW())
            ");
            $stmt->execute([
                $payment_type,
                $reference_id,
                $amount,
                $payment_method,
                $transaction_id
            ]);

            $payment_id = $this->db->lastInsertId();

            // Update status based on payment type
            switch ($payment_type) {
                case 'medicine_order':
                    $stmt = $this->db->prepare("
                        UPDATE medicine_orders 
                        SET payment_status = 'paid', 
                            order_status = 'processing'
                        WHERE id = ? AND patient_id = ?
                    ");
                    break;
                
                case 'cab_booking':
                    $stmt = $this->db->prepare("
                        UPDATE cab_bookings 
                        SET payment_status = 'paid',
                            status = 'confirmed'
                        WHERE id = ? AND patient_id = ?
                    ");
                    break;
                
                case 'appointment':
                    $stmt = $this->db->prepare("
                        UPDATE appointments 
                        SET payment_status = 'paid',
                            status = 'pending'
                        WHERE id = ? AND patient_id = ?
                    ");
                    break;
            }

            $stmt->execute([$reference_id, $this->patient['id']]);

            // Commit transaction
            $this->db->commit();

            // Get payment details for confirmation page
            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            // Send payment confirmation email
            $emailService = new EmailService();
            $paymentData = [
                'id' => $payment['transaction_id'],
                'amount' => number_format($payment['amount'], 2),
                'date' => date('F j, Y', strtotime($payment['payment_date'])),
                'service' => ucwords(str_replace('_', ' ', $payment['payment_type']))
            ];

            // Get patient details for email
            $stmt = $this->db->prepare("SELECT name, email FROM patients WHERE id = ?");
            $stmt->execute([$this->patient['id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($patient) {
                $emailService->sendPaymentConfirmation(
                    $patient['email'],
                    $patient['name'],
                    $paymentData
                );
            }

            // Show confirmation page
            require_once __DIR__ . '/../views/payment_confirmation.php';

        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();

            $_SESSION['error'] = "Payment failed: " . $e->getMessage();
            header("Location: index.php?module=patient&action=payment&type={$payment_type}&id={$reference_id}");
            exit;
        }
    }

    /**
     * Show payment history
     */
    public function showPaymentHistory() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM payments 
                WHERE reference_id IN (
                    SELECT id FROM medicine_orders WHERE patient_id = ?
                    UNION
                    SELECT id FROM cab_bookings WHERE patient_id = ?
                    UNION
                    SELECT id FROM appointments WHERE patient_id = ?
                )
                ORDER BY created_at DESC
            ");
            $stmt->execute([
                $this->patient['id'],
                $this->patient['id'],
                $this->patient['id']
            ]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../views/payment_history.php';

        } catch (Exception $e) {
            $_SESSION['error'] = "Error loading payment history";
            header("Location: index.php?module=patient&action=dashboard");
            exit;
        }
    }
}
?> 