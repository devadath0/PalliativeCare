<?php
/**
 * Cab Bookings View
 * Palliative Care System
 */

// Set page title
$page_title = 'Transport Bookings History';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Initialize variables if not set
if (!isset($bookings)) {
    $bookings = [];
}
if (!isset($patient)) {
    $patient = [];
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Transport Bookings History</h2>
            <p class="lead">View and manage your transport bookings</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-primary me-2">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="index.php?module=patient&action=book_cab" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Booking
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

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-taxi text-muted fa-5x mb-4"></i>
                    <h4 class="text-muted">No Transport Bookings Found</h4>
                    <p class="mb-4">You haven't made any transport bookings yet.</p>
                    <a href="index.php?module=patient&action=book_cab" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Book Transport
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Booking ID</th>
                                <th scope="col">Service Provider</th>
                                <th scope="col">Pickup Date/Time</th>
                                <th scope="col">Destination</th>
                                <th scope="col">Cab Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['provider_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($booking['pickup_datetime'])); ?></td>
                                    <td><?php 
                                        $short_dest = htmlspecialchars(substr($booking['destination'], 0, 30));
                                        echo $short_dest . (strlen($booking['destination']) > 30 ? '...' : '');
                                    ?></td>
                                    <td><?php echo ucfirst($booking['cab_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            if ($booking['status'] === 'confirmed') echo 'success';
                                            elseif ($booking['status'] === 'pending') echo 'warning';
                                            elseif ($booking['status'] === 'completed') echo 'info';
                                            elseif ($booking['status'] === 'cancelled') echo 'danger';
                                            else echo 'secondary';
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?module=patient&action=view_cab_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isset($booking['payment_status']) && $booking['payment_status'] == 'pending'): ?>
                                            <a href="index.php?module=patient&action=payment&type=cab&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                                            <a href="index.php?module=patient&action=cancel_cab_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this cab booking?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
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