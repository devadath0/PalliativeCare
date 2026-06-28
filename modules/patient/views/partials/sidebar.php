<?php
/**
 * Patient Sidebar Partial
 * Palliative Care System
 */
?>
<div class="card shadow mb-4">
    <div class="card-header py-3 patient-header">
        <h6 class="m-0 font-weight-bold text-primary">Patient Navigation</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <a href="index.php?module=patient&action=dashboard" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt fa-fw mr-2"></i> Dashboard
            </a>
            <a href="index.php?module=patient&action=appointments" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'appointments' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check fa-fw mr-2"></i> Appointments
            </a>
            <a href="index.php?module=patient&action=prescriptions" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'prescriptions' ? 'active' : '' ?>">
                <i class="fas fa-prescription fa-fw mr-2"></i> Prescriptions
            </a>
            <a href="index.php?module=patient&action=order_medicine" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'order_medicine' ? 'active' : '' ?>">
                <i class="fas fa-pills fa-fw mr-2"></i> Order Medicine
            </a>
            <a href="index.php?module=patient&action=book_cab" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'book_cab' ? 'active' : '' ?>">
                <i class="fas fa-taxi fa-fw mr-2"></i> Book Transport
            </a>
            <a href="index.php?module=patient&action=cab_bookings" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'cab_bookings' ? 'active' : '' ?>">
                <i class="fas fa-list fa-fw mr-2"></i> My Bookings
            </a>
            <a href="index.php?module=patient&action=alerts" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'alerts' ? 'active' : '' ?>">
                <i class="fas fa-bell fa-fw mr-2"></i> Alerts
                <?php if (isset($unread_count) && $unread_count > 0): ?>
                <span class="badge badge-danger float-right"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php?module=patient&action=payment_history" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'payment_history' ? 'active' : '' ?>">
                <i class="fas fa-credit-card fa-fw mr-2"></i> Payment History
            </a>
            <a href="index.php?module=patient&action=profile" 
               class="list-group-item list-group-item-action <?= ($_GET['action'] ?? '') == 'profile' ? 'active' : '' ?>">
                <i class="fas fa-user-circle fa-fw mr-2"></i> My Profile
            </a>
        </div>
    </div>
</div> 