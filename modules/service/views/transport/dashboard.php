<?php
/**
 * Transport Service Dashboard View
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
        'cancelled_bookings' => 0,
        'today_bookings' => 0
    ];
}

if (!isset($recent_requests)) {
    $recent_requests = [];
}

if (!isset($today_bookings)) {
    $today_bookings = [];
}
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2>Transport Dashboard</h2>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($provider['company_name']); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="index.php?module=service&action=transport_bookings" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> All Bookings
                </a>
                <a href="index.php?module=service&action=transport_history" class="btn btn-outline-secondary">
                    <i class="fas fa-history"></i> History
                </a>
            </div>
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
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['total_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['pending_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Confirmed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['confirmed_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['completed_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag-checkered fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Cancelled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['cancelled_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Today's Trips</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $booking_stats['today_bookings']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row">
        <!-- Recent Cab Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Cab Requests</h6>
                    <a href="index.php?module=service&action=transport_bookings" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_requests)): ?>
                        <div class="alert alert-info">No recent cab requests found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date & Time</th>
                                        <th>Patient</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_requests as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?><br>
                                                <small><?php echo date('h:i A', strtotime($booking['pickup_time'])); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($booking['patient_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['patient_phone']); ?></small>
                                            </td>
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
                                                <?php endif; ?>
                                                
                                                <a href="index.php?module=service&action=view_booking&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
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

        <!-- Today's Bookings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Bookings</h6>
                    <a href="index.php?module=service&action=transport_bookings&date=<?php echo date('Y-m-d'); ?>" class="btn btn-sm btn-primary">
                        View All Today
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($today_bookings)): ?>
                        <div class="alert alert-info">No bookings scheduled for today.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_bookings as $booking): ?>
                                        <tr>
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links and Reference Information -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="index.php?module=service&action=transport_bookings&status=pending" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-hourglass-half"></i> View Pending Bookings
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?module=service&action=transport_bookings&status=confirmed" class="btn btn-success btn-block w-100">
                                <i class="fas fa-check-circle"></i> View Confirmed Bookings
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?module=service&action=transport_history" class="btn btn-info btn-block w-100">
                                <i class="fas fa-history"></i> Booking History
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?module=service&action=profile" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-user-cog"></i> Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Service Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="font-weight-bold">Operating Hours</h5>
                        <p><?php echo !empty($provider['operating_hours']) ? htmlspecialchars($provider['operating_hours']) : '9:00 AM - 5:00 PM Monday to Friday'; ?></p>
                    </div>
                    <div class="mb-3">
                        <h5 class="font-weight-bold">Contact Information</h5>
                        <p>
                            <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($provider['phone'] ?? ''); ?><br>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($provider['email'] ?? ''); ?>
                        </p>
                    </div>
                    <div>
                        <h5 class="font-weight-bold">Address</h5>
                        <p><?php echo nl2br(htmlspecialchars($provider['address'] ?? '')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/includes/footer.php'; ?> 