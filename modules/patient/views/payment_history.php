<?php
/**
 * Payment History Page
 * Palliative Care System
 */

// Set page title
$page_title = 'Payment History';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Payment History</h2>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($payments)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-receipt text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="text-muted">No payment history found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction ID</th>
                                <th>Payment For</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                    <td>
                                        <?php
                                        switch($payment['payment_type']) {
                                            case 'medicine_order':
                                                echo 'Medicine Order #' . $payment['reference_id'];
                                                break;
                                            case 'cab_booking':
                                                echo 'Cab Booking #' . $payment['reference_id'];
                                                break;
                                            case 'appointment':
                                                echo 'Appointment #' . $payment['reference_id'];
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($payment['status']) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                'refunded' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        switch($payment['payment_type']) {
                                            case 'medicine_order':
                                                $view_url = 'index.php?module=patient&action=view_medicine_order&id=' . $payment['reference_id'];
                                                break;
                                            case 'cab_booking':
                                                $view_url = 'index.php?module=patient&action=view_cab_booking&id=' . $payment['reference_id'];
                                                break;
                                            case 'appointment':
                                                $view_url = 'index.php?module=patient&action=view_appointment&id=' . $payment['reference_id'];
                                                break;
                                        }
                                        ?>
                                        <a href="<?php echo $view_url; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 