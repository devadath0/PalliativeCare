<?php
/**
 * Payment Confirmation Page
 * Palliative Care System
 */

// Set page title
$page_title = 'Payment Confirmation';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <?php if ($payment['status'] === 'completed'): ?>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                        </div>
                        <h3 class="mb-3">Payment Successful!</h3>
                        <p class="text-muted mb-4">Your payment has been processed successfully.</p>
                        
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="50%">Transaction ID</th>
                                        <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Amount Paid</th>
                                        <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method</th>
                                        <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date & Time</th>
                                        <td><?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        // Generate return URL based on payment type
                        switch($payment['payment_type']) {
                            case 'medicine_order':
                                $return_url = 'index.php?module=patient&action=view_medicine_order&id=' . $payment['reference_id'];
                                $button_text = 'View Order Details';
                                break;
                            case 'cab_booking':
                                $return_url = 'index.php?module=patient&action=view_cab_booking&id=' . $payment['reference_id'];
                                $button_text = 'View Booking Details';
                                break;
                            case 'appointment':
                                $return_url = 'index.php?module=patient&action=view_appointment&id=' . $payment['reference_id'];
                                $button_text = 'View Appointment Details';
                                break;
                            default:
                                $return_url = 'index.php?module=patient&action=dashboard';
                                $button_text = 'Return to Dashboard';
                        }
                        ?>

                        <div class="d-grid gap-2">
                            <a href="<?php echo $return_url; ?>" class="btn btn-primary">
                                <?php echo $button_text; ?>
                            </a>
                            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-secondary">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h3 class="mb-3">Payment Failed</h3>
                        <p class="text-muted mb-4">
                            <?php if ($payment['status'] === 'failed'): ?>
                                Sorry, your payment could not be processed. Please try again.
                            <?php else: ?>
                                Payment is still processing. Please check back later.
                            <?php endif; ?>
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="javascript:history.back()" class="btn btn-primary">
                                Try Again
                            </a>
                            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-secondary">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 