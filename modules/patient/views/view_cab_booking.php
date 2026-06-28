<?php
/**
 * View Cab Booking Details
 * Palliative Care System
 */

// Set page title
$page_title = 'Booking Details';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Initialize variables if not set
if (!isset($booking)) {
    $booking = [];
}
if (!isset($patient)) {
    $patient = [];
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Transport Booking Details</h2>
            <p class="lead">Booking #<?php echo $booking['id'] ?? ''; ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=cab_bookings" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($booking)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Booking not found or you don't have permission to view it.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Booking Information</h5>
                            <span class="badge bg-<?php 
                                if ($booking['status'] === 'confirmed') echo 'success';
                                elseif ($booking['status'] === 'pending') echo 'warning';
                                elseif ($booking['status'] === 'completed') echo 'info';
                                elseif ($booking['status'] === 'cancelled') echo 'danger';
                                else echo 'secondary';
                            ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Pickup Details</h6>
                                <div class="mb-3">
                                    <div class="text-muted small">Date & Time</div>
                                    <div>
                                        <i class="far fa-calendar-alt me-1"></i> 
                                        <?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?>
                                        <i class="far fa-clock ms-2 me-1"></i>
                                        <?php echo date('g:i A', strtotime($booking['pickup_time'])); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Pickup Address</div>
                                    <div><?php echo nl2br(htmlspecialchars($booking['pickup_address'])); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Destination</div>
                                    <div><?php echo nl2br(htmlspecialchars($booking['destination'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Service Details</h6>
                                <div class="mb-3">
                                    <div class="text-muted small">Service Provider</div>
                                    <div><?php echo htmlspecialchars($booking['provider_name'] ?? 'Not assigned'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Provider Phone</div>
                                    <div>
                                        <?php if (!empty($booking['provider_phone'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($booking['provider_phone']); ?>">
                                                <?php echo htmlspecialchars($booking['provider_phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            Not available
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Cab Type</div>
                                    <div>
                                        <?php 
                                            $cab_type_icons = [
                                                'standard' => 'fa-taxi',
                                                'wheelchair' => 'fa-wheelchair',
                                                'stretcher' => 'fa-ambulance'
                                            ];
                                            $icon = $cab_type_icons[$booking['cab_type']] ?? 'fa-taxi';
                                            echo '<i class="fas ' . $icon . ' me-1"></i> ' . ucfirst($booking['cab_type']);
                                        ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Estimated Fare</div>
                                    <div>
                                        <?php 
                                            if (isset($booking['estimated_fare'])) {
                                                echo '$' . number_format($booking['estimated_fare'], 2);
                                            } else {
                                                echo 'To be determined';
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($booking['special_requirements'])): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold">Special Requirements</h6>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br(htmlspecialchars($booking['special_requirements'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold">Booking Timeline</h6>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-item-marker">
                                            <div class="timeline-item-marker-indicator bg-primary">
                                                <i class="fas fa-plus"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-item-content">
                                            Booking Created
                                            <div class="text-muted small"><?php echo date('F j, Y g:i A', strtotime($booking['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($booking['confirmed_at'])): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-item-marker">
                                            <div class="timeline-item-marker-indicator bg-success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-item-content">
                                            Booking Confirmed
                                            <div class="text-muted small"><?php echo date('F j, Y g:i A', strtotime($booking['confirmed_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($booking['completed_at'])): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-item-marker">
                                            <div class="timeline-item-marker-indicator bg-info">
                                                <i class="fas fa-flag-checkered"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-item-content">
                                            Booking Completed
                                            <div class="text-muted small"><?php echo date('F j, Y g:i A', strtotime($booking['completed_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($booking['cancelled_at'])): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-item-marker">
                                            <div class="timeline-item-marker-indicator bg-danger">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-item-content">
                                            Booking Cancelled
                                            <div class="text-muted small"><?php echo date('F j, Y g:i A', strtotime($booking['cancelled_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                            <a href="index.php?module=patient&action=cancel_cab_booking&id=<?php echo $booking['id']; ?>" 
                               class="btn btn-danger w-100 mb-3"
                               onclick="return confirm('Are you sure you want to cancel this booking?');">
                                <i class="fas fa-times"></i> Cancel Booking
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isset($booking['payment_status']) && $booking['payment_status'] === 'pending'): ?>
                            <a href="index.php?module=patient&action=payment&type=cab&id=<?php echo $booking['id']; ?>" 
                               class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        <?php endif; ?>
                        
                        <a href="index.php?module=patient&action=book_cab" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-plus"></i> New Booking
                        </a>
                        
                        <a href="index.php?module=patient&action=cab_bookings" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-list"></i> View All Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1rem;
    margin: 1rem 0;
    border-left: 1px solid #dee2e6;
}
.timeline-item {
    position: relative;
    padding-bottom: 1rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item-marker {
    position: absolute;
    left: -0.75rem;
    width: 1.5rem;
    height: 1.5rem;
    background-color: #fff;
}
.timeline-item-marker-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 100%;
    color: #fff;
}
.timeline-item-content {
    padding-left: 1rem;
    padding-bottom: 1rem;
}
</style>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 