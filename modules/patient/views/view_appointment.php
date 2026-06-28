<?php
/**
 * View Appointment Details
 * Palliative Care System
 */

// Set page title
$page_title = 'Appointment Details';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Check if appointment exists
if (empty($appointment)) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: index.php?module=patient&action=appointments");
    exit;
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Appointment Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=appointments" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <span class="badge bg-<?php 
                    echo match($appointment['status']) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'secondary'
                    };
                ?>">
                    <?php echo ucfirst($appointment['status']); ?>
                </span>
                Appointment with Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Appointment Details</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Date & Time:</dt>
                        <dd class="col-sm-8"><?php echo date('F j, Y g:i A', strtotime($appointment['appointment_date'])); ?></dd>
                        
                        <dt class="col-sm-4">Doctor:</dt>
                        <dd class="col-sm-8">Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></dd>
                        
                        <dt class="col-sm-4">Specialization:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($appointment['specialization']); ?></dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php 
                                echo match($appointment['status']) {
                                    'confirmed' => 'success',
                                    'pending' => 'warning',
                                    'cancelled' => 'danger',
                                    'completed' => 'info',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Reason for Visit</h6>
                    <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
                    
                    <?php if (!empty($appointment['notes'])): ?>
                        <h6 class="text-muted mt-3">Doctor's Notes</h6>
                        <p><?php echo htmlspecialchars($appointment['notes']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                <?php if (isset($appointment['payment_status']) && $appointment['payment_status'] == 'pending'): ?>
                    <a href="index.php?module=patient&action=payment&type=appointment&id=<?php echo $appointment['id']; ?>" 
                       class="btn btn-primary me-2">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </a>
                <?php endif; ?>
                <a href="index.php?module=patient&action=cancel_appointment&id=<?php echo $appointment['id']; ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to cancel this appointment?');">
                    <i class="fas fa-times"></i> Cancel Appointment
                </a>
                
                <?php if ($appointment['status'] === 'confirmed'): ?>
                    <a href="index.php?module=patient&action=chat_with_doctor&appointment_id=<?php echo $appointment['id']; ?>" 
                       class="btn btn-primary ms-2">
                        <i class="fas fa-comment-medical"></i> Chat with Doctor
                        <?php if (isset($appointment['unread_count']) && $appointment['unread_count'] > 0): ?>
                            <span class="badge bg-danger"><?php echo $appointment['unread_count']; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($appointment['status'] === 'completed'): ?>
                <a href="index.php?module=patient&action=book_appointment&copy=<?php echo $appointment['id']; ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-copy"></i> Book Similar Appointment
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?>
