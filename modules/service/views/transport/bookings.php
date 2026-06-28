<?php
/**
 * Transport Bookings Management View
 * Palliative Care System - Transport Service Provider Module
 */

// Check if user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'service') {
    header('Location: index.php?module=auth&action=login&type=service');
    exit;
}

// Include header
include_once 'views/includes/header.php';

// Access variables passed from controller
$provider = $service_provider;
$provider_id = $provider['id'];

// Initialize variables to prevent warnings
if (!isset($booking_stats)) {
    $booking_stats = [
        'total_bookings' => 0,
        'pending_bookings' => 0,
        'confirmed_bookings' => 0, 
        'completed_bookings' => 0,
        'cancelled_bookings' => 0
    ];
}

if (!isset($bookings)) {
    $bookings = [];
}

if (!isset($status_filter)) {
    $status_filter = 'all';
}

if (!isset($date_filter)) {
    $date_filter = '';
}
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2>Transport Bookings Management</h2>
            <p class="text-muted">View and manage all transport bookings</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=service&action=transport_dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <!-- Filters and Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold">Filter Bookings</h6>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="module" value="service">
                        <input type="hidden" name="action" value="transport_bookings">
                        
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="date" class="form-label">Pickup Date</label>
                            <input type="date" name="date" id="date" class="form-control" value="<?php echo $date_filter; ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold">Booking Statistics</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Total Bookings</span>
                            <span class="badge rounded-pill bg-primary"><?php echo $booking_stats['total_bookings']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Pending</span>
                            <span class="badge rounded-pill bg-warning"><?php echo $booking_stats['pending_bookings']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Confirmed</span>
                            <span class="badge rounded-pill bg-success"><?php echo $booking_stats['confirmed_bookings']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Completed</span>
                            <span class="badge rounded-pill bg-info"><?php echo $booking_stats['completed_bookings']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Cancelled</span>
                            <span class="badge rounded-pill bg-danger"><?php echo $booking_stats['cancelled_bookings']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php 
                $title = "All Bookings";
                if ($status_filter != 'all') {
                    $title = ucfirst($status_filter) . " Bookings";
                }
                if (!empty($date_filter)) {
                    $title .= " for " . date('F j, Y', strtotime($date_filter));
                }
                echo $title;
                ?>
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printBookingsList()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="index.php?module=service&action=export_bookings<?php echo ($status_filter != 'all') ? '&status=' . $status_filter : ''; ?><?php echo (!empty($date_filter)) ? '&date=' . $date_filter : ''; ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">No bookings found matching your criteria.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pickup Date</th>
                                <th>Pickup Time</th>
                                <th>Patient</th>
                                <th>Pickup Address</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($booking['pickup_time'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['patient_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['patient_phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['pickup_address']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        switch($booking['status']) {
                                            case 'pending': $status_class = 'badge bg-warning'; break;
                                            case 'confirmed': $status_class = 'badge bg-success'; break;
                                            case 'completed': $status_class = 'badge bg-primary'; break;
                                            case 'cancelled': $status_class = 'badge bg-danger'; break;
                                            default: $status_class = 'badge bg-secondary';
                                        }
                                        ?>
                                        <span class="<?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($booking['special_requirements'])): ?>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($booking['special_requirements']); ?>">
                                                <i class="fas fa-info-circle"></i> Requirements
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($booking['provider_notes'])): ?>
                                            <br>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($booking['provider_notes']); ?>">
                                                <i class="fas fa-comment-alt"></i> Provider Notes
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <a href="index.php?module=service&action=confirm_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success mb-1">
                                                <i class="fas fa-check"></i> Confirm
                                            </a>
                                            <a href="index.php?module=service&action=cancel_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger mb-1">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                                            <a href="index.php?module=service&action=complete_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                                <i class="fas fa-check-double"></i> Complete
                                            </a>
                                            <a href="index.php?module=service&action=cancel_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger mb-1">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#notesModal<?php echo $booking['id']; ?>">
                                            <i class="fas fa-edit"></i> Notes
                                        </button>
                                        
                                        <!-- Notes Modal -->
                                        <div class="modal fade" id="notesModal<?php echo $booking['id']; ?>" tabindex="-1" aria-labelledby="notesModalLabel<?php echo $booking['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="notesModalLabel<?php echo $booking['id']; ?>">Update Notes for Booking #<?php echo $booking['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="index.php?module=service&action=update_booking_notes" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="provider_notes<?php echo $booking['id']; ?>" class="form-label">Provider Notes</label>
                                                                <textarea name="provider_notes" id="provider_notes<?php echo $booking['id']; ?>" class="form-control" rows="4"><?php echo htmlspecialchars($booking['provider_notes'] ?? ''); ?></textarea>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Special Requirements (Patient Notes)</label>
                                                                <p class="form-control-plaintext"><?php echo htmlspecialchars($booking['special_requirements'] ?? 'None'); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Save Notes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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

<script>
function printBookingsList() {
    const printContents = document.getElementById('bookingsTable').outerHTML;
    const originalContents = document.body.innerHTML;
    
    const printHead = `
        <html>
        <head>
            <title>Transport Bookings - <?php echo htmlspecialchars($provider['company_name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .print-header { text-align: center; margin-bottom: 20px; }
                .print-date { text-align: right; margin-bottom: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h2>Transport Bookings Report</h2>
                <h3><?php echo htmlspecialchars($provider['company_name']); ?></h3>
            </div>
            <div class="print-date">
                <p>Generated: ${new Date().toLocaleString()}</p>
                <p>Status: <?php echo ($status_filter == 'all') ? 'All Statuses' : ucfirst($status_filter); ?></p>
                <?php if (!empty($date_filter)): ?>
                <p>Date: <?php echo date('F j, Y', strtotime($date_filter)); ?></p>
                <?php endif; ?>
            </div>
    `;
    
    const printFoot = `
        </body>
        </html>
    `;
    
    document.body.innerHTML = printHead + printContents + printFoot;
    window.print();
    document.body.innerHTML = originalContents;
}

// Initialize tooltips and modals
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize modals
    var modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(function(modalEl) {
        new bootstrap.Modal(modalEl);
    });
    
    // Fix any modal backdrop issues
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Remove any lingering backdrop
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.parentNode.removeChild(backdrop);
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });
    });
});
</script>

<?php include_once 'views/includes/footer.php'; ?> 