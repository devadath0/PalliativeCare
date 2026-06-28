<?php
/**
 * Transport Booking History View
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
        'completed_bookings' => 0,
        'cancelled_bookings' => 0,
        'oldest_booking' => null,
        'newest_booking' => null
    ];
}

if (!isset($bookings)) {
    $bookings = [];
}

if (!isset($pagination)) {
    $pagination = [
        'total_records' => 0,
        'total_pages' => 1,
        'current_page' => 1,
        'limit' => 20
    ];
}

if (!isset($filters)) {
    $filters = [
        'status' => '',
        'date_start' => '',
        'date_end' => '',
        'patient' => ''
    ];
}
?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2>Transport Booking History</h2>
            <p class="text-muted">View complete history of transport bookings</p>
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
                    <h6 class="m-0 font-weight-bold">Filter Booking History</h6>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="module" value="service">
                        <input type="hidden" name="action" value="transport_history">
                        
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="" <?php echo (empty($filters['status'])) ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($filters['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo ($filters['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($filters['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="date_start" class="form-label">Date From</label>
                            <input type="date" name="date_start" id="date_start" class="form-control" value="<?php echo $filters['date_start']; ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="date_end" class="form-label">Date To</label>
                            <input type="date" name="date_end" id="date_end" class="form-control" value="<?php echo $filters['date_end']; ?>">
                        </div>
                        
                        <div class="col-md-8">
                            <label for="patient" class="form-label">Patient Name/ID</label>
                            <input type="text" name="patient" id="patient" class="form-control" placeholder="Enter patient name or ID" value="<?php echo htmlspecialchars($filters['patient']); ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Apply Filters
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
                            <span>Completed</span>
                            <span class="badge rounded-pill bg-success"><?php echo $booking_stats['completed_bookings']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Cancelled</span>
                            <span class="badge rounded-pill bg-danger"><?php echo $booking_stats['cancelled_bookings']; ?></span>
                        </li>
                        <?php if (!empty($booking_stats['oldest_booking'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Date Range</span>
                            <span class="text-muted">
                                <?php 
                                $oldest = date('M d, Y', strtotime($booking_stats['oldest_booking']));
                                $newest = date('M d, Y', strtotime($booking_stats['newest_booking']));
                                echo $oldest . " - " . $newest; 
                                ?>
                            </span>
                        </li>
                        <?php endif; ?>
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
                $title = "Booking History";
                if (!empty($filters['status'])) {
                    $title = ucfirst($filters['status']) . " Bookings";
                }
                if (!empty($filters['date_start']) || !empty($filters['date_end'])) {
                    $title .= " - Date Range: ";
                    if (!empty($filters['date_start'])) {
                        $title .= date('M d, Y', strtotime($filters['date_start']));
                    } else {
                        $title .= "All";
                    }
                    $title .= " to ";
                    if (!empty($filters['date_end'])) {
                        $title .= date('M d, Y', strtotime($filters['date_end']));
                    } else {
                        $title .= "Present";
                    }
                }
                if (!empty($filters['patient'])) {
                    $title .= " - Patient: " . htmlspecialchars($filters['patient']);
                }
                echo $title;
                ?>
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printBookingHistory()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="index.php?module=service&action=export_booking_history<?php 
                    echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                    echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                    echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                    echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info">No booking history found matching your criteria.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                                <th>Payment</th>
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
                                        <?php if (!empty($booking['fare'])): ?>
                                        <span class="fw-bold"><?php echo '$' . number_format($booking['fare'], 2); ?></span>
                                        <?php if (!empty($booking['payment_date'])): ?>
                                            <br><small class="text-success">Paid: <?php echo date('M d, Y', strtotime($booking['payment_date'])); ?></small>
                                        <?php elseif ($booking['status'] == 'completed'): ?>
                                            <br><small class="text-danger">Unpaid</small>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?module=service&action=booking_details&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info mb-1">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                        
                                        <?php if ($booking['status'] == 'completed' && empty($booking['payment_date']) && !empty($booking['fare'])): ?>
                                            <a href="index.php?module=service&action=mark_payment&id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success mb-1">
                                                <i class="fas fa-dollar-sign"></i> Mark Paid
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing <?php echo min(($pagination['current_page'] - 1) * $pagination['limit'] + 1, $pagination['total_records']); ?> 
                        to <?php echo min($pagination['current_page'] * $pagination['limit'], $pagination['total_records']); ?> 
                        of <?php echo $pagination['total_records']; ?> records
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?module=service&action=transport_history&page=<?php echo $pagination['current_page'] - 1; ?><?php 
                                        echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                                        echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                                        echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                                        echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                                    ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $pagination['current_page'] - 2);
                            $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="index.php?module=service&action=transport_history&page=1';
                                echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                                echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                                echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                                echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                                echo '">1</a></li>';
                                
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active = ($i == $pagination['current_page']) ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="index.php?module=service&action=transport_history&page=' . $i;
                                echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                                echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                                echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                                echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                                echo '">' . $i . '</a></li>';
                            }
                            
                            if ($end_page < $pagination['total_pages']) {
                                if ($end_page < $pagination['total_pages'] - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                
                                echo '<li class="page-item"><a class="page-link" href="index.php?module=service&action=transport_history&page=' . $pagination['total_pages'];
                                echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                                echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                                echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                                echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                                echo '">' . $pagination['total_pages'] . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?module=service&action=transport_history&page=<?php echo $pagination['current_page'] + 1; ?><?php 
                                        echo (!empty($filters['status'])) ? '&status=' . $filters['status'] : ''; 
                                        echo (!empty($filters['date_start'])) ? '&date_start=' . $filters['date_start'] : '';
                                        echo (!empty($filters['date_end'])) ? '&date_end=' . $filters['date_end'] : '';
                                        echo (!empty($filters['patient'])) ? '&patient=' . urlencode($filters['patient']) : '';
                                    ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function printBookingHistory() {
    const printContents = document.getElementById('bookingsTable').outerHTML;
    const originalContents = document.body.innerHTML;
    
    const printHead = `
        <html>
        <head>
            <title>Booking History - <?php echo htmlspecialchars($provider['company_name']); ?></title>
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
                <h2>Transport Booking History Report</h2>
                <h3><?php echo htmlspecialchars($provider['company_name']); ?></h3>
            </div>
            <div class="print-date">
                <p>Generated: ${new Date().toLocaleString()}</p>
                <?php if (!empty($filters['status'])): ?>
                <p>Status: <?php echo ucfirst($filters['status']); ?></p>
                <?php endif; ?>
                <?php if (!empty($filters['date_start']) || !empty($filters['date_end'])): ?>
                <p>Date Range: 
                    <?php echo !empty($filters['date_start']) ? date('M d, Y', strtotime($filters['date_start'])) : 'All'; ?> 
                    to 
                    <?php echo !empty($filters['date_end']) ? date('M d, Y', strtotime($filters['date_end'])) : 'Present'; ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($filters['patient'])): ?>
                <p>Patient: <?php echo htmlspecialchars($filters['patient']); ?></p>
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
</script>

<?php include_once 'views/includes/footer.php'; ?> 