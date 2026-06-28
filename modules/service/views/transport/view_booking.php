<?php
/**
 * View Transport Booking Details
 * Palliative Care System - Transport Service Provider Module
 */

// Check if user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'service') {
    header('Location: index.php?module=auth&action=login&type=service');
    exit;
}

// Include header
include_once 'views/includes/header.php';

// Initialize variables to prevent warnings
$booking = $booking ?? [];
$provider = $service_provider ?? [];
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2>Booking Details</h2>
            <p class="text-muted">Viewing details for booking #<?php echo htmlspecialchars($booking['id'] ?? ''); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="index.php?module=service&action=transport_bookings" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($booking)): ?>
        <div class="alert alert-warning">Booking not found or you don't have permission to view it.</div>
    <?php else: ?>
        <!-- Booking Details Card -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Booking Information</h6>
                        <span class="badge bg-<?php 
                            echo match($booking['status'] ?? '') {
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'completed' => 'info',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo ucfirst(htmlspecialchars($booking['status'] ?? 'unknown')); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Pickup Details</h5>
                                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($booking['pickup_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['pickup_time'])); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['pickup_address'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Destination</h5>
                                <p><?php echo htmlspecialchars($booking['destination'] ?? ''); ?></p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Patient Information</h5>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['patient_name'] ?? ''); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['patient_phone'] ?? ''); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['patient_email'] ?? ''); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['patient_address'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Booking Details</h5>
                                <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking['id'] ?? ''); ?></p>
                                <p><strong>Created:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['created_at'] ?? 'now')); ?></p>
                                <?php if (!empty($booking['confirmed_at'])): ?>
                                <p><strong>Confirmed:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['confirmed_at'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($booking['completed_at'])): ?>
                                <p><strong>Completed:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['completed_at'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($booking['cancelled_at'])): ?>
                                <p><strong>Cancelled:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['cancelled_at'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($booking['special_requirements'])): ?>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12">
                                <h5>Special Requirements</h5>
                                <p><?php echo nl2br(htmlspecialchars($booking['special_requirements'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Status Updates Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <a href="index.php?module=service&action=confirm_booking&id=<?php echo $booking['id']; ?>" class="btn btn-success btn-block w-100 mb-3">
                                <i class="fas fa-check-circle"></i> Confirm Booking
                            </a>
                            <a href="index.php?module=service&action=cancel_booking&id=<?php echo $booking['id']; ?>" class="btn btn-danger btn-block w-100 mb-3">
                                <i class="fas fa-times-circle"></i> Cancel Booking
                            </a>
                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                            <a href="index.php?module=service&action=complete_booking&id=<?php echo $booking['id']; ?>" class="btn btn-info btn-block w-100 mb-3">
                                <i class="fas fa-check-double"></i> Mark as Completed
                            </a>
                            <a href="index.php?module=service&action=cancel_booking&id=<?php echo $booking['id']; ?>" class="btn btn-danger btn-block w-100 mb-3">
                                <i class="fas fa-times-circle"></i> Cancel Booking
                            </a>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <!-- Provider Notes Form -->
                        <h6 class="font-weight-bold">Provider Notes</h6>
                        <form action="index.php?module=service&action=update_booking_notes" method="post">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <div class="mb-3">
                                <textarea name="provider_notes" class="form-control" rows="4"><?php echo htmlspecialchars($booking['provider_notes'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-save"></i> Save Notes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'views/includes/footer.php'; ?> 