<?php
/**
 * Patient Dashboard View
 * Palliative Care System
 */

// Set page title
$page_title = 'Patient Dashboard';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Log data for debugging
error_log("Dashboard data: " . print_r($data, true));

// Extract data
extract($data);
?>

<div class="container-fluid py-4">
    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold mb-1">Welcome Back, <?php echo htmlspecialchars($patient['name']); ?>!</h2>
                            <p class="mb-0">How are you feeling today?</p>
                        </div>
                        <div>
                            <a href="index.php?module=patient&action=profile" class="btn btn-light">
                                <i class="fas fa-user-edit me-2"></i> My Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-check text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Upcoming</h6>
                        <h4 class="mb-0"><?php echo count($appointments) > 0 ? count($appointments) : 'No'; ?> Appointments</h4>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 text-end">
                    <a href="index.php?module=patient&action=appointments" class="text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fas fa-prescription text-danger fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Active</h6>
                        <h4 class="mb-0"><?php echo count($prescriptions) > 0 ? count($prescriptions) : 'No'; ?> Prescriptions</h4>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 text-end">
                    <a href="index.php?module=patient&action=prescriptions" class="text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Need Help?</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                            <i class="fas fa-search text-info"></i>
                        </div>
                        <div>
                            <a href="index.php?module=patient&action=symptom_search" class="text-decoration-none">Search symptoms and find the right doctor</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                            <i class="fas fa-phone-alt text-warning"></i>
                        </div>
                        <div>
                            <p class="mb-0">Emergency Helpline: <strong>1-800-PALLIATIVE</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Services -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <h4>Quick Services</h4>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?module=patient&action=book_appointment" class="card border-0 shadow-sm h-100 text-decoration-none text-dark hover-card">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                        <i class="fas fa-calendar-plus text-success fa-2x"></i>
                    </div>
                    <h5 class="card-title">Book Appointment</h5>
                    <p class="card-text text-muted">Schedule a visit with our specialists</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?module=patient&action=order_medicine" class="card border-0 shadow-sm h-100 text-decoration-none text-dark hover-card">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                        <i class="fas fa-pills text-danger fa-2x"></i>
                    </div>
                    <h5 class="card-title">Order Medicine</h5>
                    <p class="card-text text-muted">Get your prescriptions delivered</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?module=patient&action=book_cab" class="card border-0 shadow-sm h-100 text-decoration-none text-dark hover-card">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                        <i class="fas fa-taxi text-warning fa-2x"></i>
                    </div>
                    <h5 class="card-title">Book Transport</h5>
                    <p class="card-text text-muted">Arrange hospital transportation</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?module=patient&action=symptom_search" class="card border-0 shadow-sm h-100 text-decoration-none text-dark hover-card">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                        <i class="fas fa-search text-info fa-2x"></i>
                    </div>
                    <h5 class="card-title">Symptom Checker</h5>
                    <p class="card-text text-muted">Identify possible conditions</p>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?module=patient&action=report_issue" class="card border-0 shadow-sm h-100 text-decoration-none text-dark hover-card">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                        <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                    </div>
                    <h5 class="card-title">Report Issue</h5>
                    <p class="card-text text-muted">Report service problems</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12 mb-3">
            <h4>Recent Activity</h4>
        </div>
        
        <!-- Upcoming Appointments -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Upcoming Appointments</h5>
                    <a href="index.php?module=patient&action=appointments" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($appointments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
                            <p class="text-muted">No upcoming appointments</p>
                            <a href="index.php?module=patient&action=book_appointment" class="btn btn-primary btn-sm">
                                Book Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($appointments as $appointment): ?>
                                <a href="index.php?module=patient&action=view_appointment&id=<?php echo $appointment['id']; ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-3 me-3">
                                            <i class="fas fa-user-md text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></h6>
                                                <span class="badge bg-<?php 
                                                    if ($appointment['status'] === 'confirmed') echo 'success';
                                                    elseif ($appointment['status'] === 'pending') echo 'warning';
                                                    elseif ($appointment['status'] === 'cancelled') echo 'danger';
                                                    else echo 'info';
                                                ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </div>
                                            <p class="mb-1 small text-muted">
                                                <?php if (!empty($appointment['specialization'])): ?>
                                                    <span class="me-2"><?php echo htmlspecialchars($appointment['specialization']); ?></span>
                                                <?php endif; ?>
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                                                <i class="far fa-clock ms-2 me-1"></i>
                                                <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Prescriptions -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Recent Prescriptions</h5>
                    <a href="index.php?module=patient&action=prescriptions" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($prescriptions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-prescription-bottle text-muted fa-3x mb-3"></i>
                            <p class="text-muted">No recent prescriptions</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($prescriptions as $prescription): ?>
                                <a href="index.php?module=patient&action=view_prescription&id=<?php echo $prescription['id']; ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-3 me-3">
                                            <i class="fas fa-prescription-bottle-alt text-danger"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($prescription['created_at'])); ?>
                                                </small>
                                            </div>
                                            <?php if (!empty($prescription['diagnosis'])): ?>
                                                <p class="mb-0 small text-muted">
                                                    <?php 
                                                    $diagnosis = htmlspecialchars($prescription['diagnosis']);
                                                    // Limit to first line or 100 characters
                                                    $short_diagnosis = (strpos($diagnosis, "\n") !== false) 
                                                        ? substr($diagnosis, 0, strpos($diagnosis, "\n")) . '...' 
                                                        : (strlen($diagnosis) > 100 ? substr($diagnosis, 0, 100) . '...' : $diagnosis);
                                                    echo $short_diagnosis;
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Cab Bookings -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Recent Transport Bookings</h5>
                    <a href="index.php?module=patient&action=cab_bookings" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($cab_bookings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-taxi text-muted fa-3x mb-3"></i>
                            <p class="text-muted">No transport bookings found</p>
                            <a href="index.php?module=patient&action=book_cab" class="btn btn-primary btn-sm">
                                Book Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($cab_bookings as $booking): ?>
                                <a href="index.php?module=patient&action=view_cab_booking&id=<?php echo $booking['id']; ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-3 me-3">
                                            <i class="fas fa-taxi text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($booking['provider_name'] ?? 'Transport Service'); ?></h6>
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
                                            <p class="mb-1 small text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars(substr($booking['pickup_address'], 0, 30) . (strlen($booking['pickup_address']) > 30 ? '...' : '')); ?>
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('F j, Y', strtotime($booking['pickup_datetime'])); ?>
                                                <i class="far fa-clock ms-2 me-1"></i>
                                                <?php echo date('g:i A', strtotime($booking['pickup_datetime'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Medicine Orders -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Recent Medicine Orders</h5>
                    <a href="index.php?module=patient&action=order_medicine" class="btn btn-sm btn-outline-primary">
                        Order Now
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($medicine_orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-pills text-muted fa-3x mb-3"></i>
                            <p class="text-muted">No recent medicine orders</p>
                            <a href="index.php?module=patient&action=order_medicine" class="btn btn-primary btn-sm">
                                Order Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($medicine_orders as $order): ?>
                                <a href="index.php?module=patient&action=view_medicine_order&id=<?php echo $order['id']; ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light p-3 me-3">
                                            <i class="fas fa-pills text-danger"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">Order #<?php echo $order['id']; ?></h6>
                                                <span class="badge bg-<?php 
                                                    if ($order['order_status'] === 'delivered') echo 'success';
                                                    elseif ($order['order_status'] === 'processing') echo 'primary';
                                                    elseif ($order['order_status'] === 'pending') echo 'warning';
                                                    elseif ($order['order_status'] === 'cancelled') echo 'danger';
                                                    else echo 'info';
                                                ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </div>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars($order['pharmacy_name'] ?? 'Pharmacy'); ?>
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                                <i class="fas fa-dollar-sign ms-2 me-1"></i>
                                                <?php echo number_format($order['total_amount'], 2); ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Recent Alerts
                        <?php if (isset($unread_count) && $unread_count > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-2"><?= $unread_count ?> unread</span>
                        <?php endif; ?>
                    </h6>
                    <a href="index.php?module=patient&action=alerts" class="btn btn-sm btn-primary">
                        View All Alerts
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($alerts) && count($alerts) > 0): ?>
                        <div class="list-group">
                            <?php foreach (array_slice($alerts, 0, 3) as $alert): ?>
                                <div class="list-group-item list-group-item-action <?= $alert['is_read'] ? '' : 'border-start border-primary border-3' ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 <?= $alert['is_read'] ? '' : 'fw-bold' ?>"><?= htmlspecialchars($alert['title']) ?></h6>
                                        <small><?= date('M j, Y g:i a', strtotime($alert['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1 small"><?= htmlspecialchars($alert['message']) ?></p>
                                    <?php if (!$alert['is_read']): ?>
                                        <a href="index.php?module=patient&action=mark_alert_read&id=<?= $alert['id'] ?>&redirect=<?= urlencode('index.php?module=patient&action=dashboard') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-bell fa-3x mb-3 text-gray-300"></i>
                            <p class="text-gray-600">You don't have any recent alerts</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.bg-opacity-10 {
    opacity: 0.1;
}
</style>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?>
