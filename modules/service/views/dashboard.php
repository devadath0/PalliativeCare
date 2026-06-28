<?php
/**
 * Service Provider Dashboard
 * Palliative Care System - Service Provider Module
 */

// Check if user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'service') {
    header('Location: index.php?module=auth&action=login&type=service');
    exit;
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'Service Provider Dashboard';

// Get service provider information
try {
    // Get database connection
    require_once __DIR__ . '/../../../classes/Database.php';
    $db = Database::getInstance();
    
    $stmt = $db->prepare("
        SELECT sp.*, u.email 
        FROM service_providers sp
        INNER JOIN users u ON sp.user_id = u.id
        WHERE sp.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$provider) {
        throw new Exception("Service provider record not found");
    }
    
    $provider_id = $provider['id'];
    
    // Check if provider is a pharmacy service
    $is_pharmacy = ($provider['service_type'] == 'pharmacy' || $provider['service_type'] == 'medicine');
    
    // For pharmacy service providers, get pharmacy data
    if ($is_pharmacy) {
        // Get pharmacy ID from the database based on provider ID
        $stmt = $db->prepare("SELECT id FROM pharmacies WHERE service_provider_id = ?");
        $stmt->execute([$provider_id]);
        $pharmacy_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $pharmacy_id = $pharmacy_data['id'] ?? null;
        
        if ($pharmacy_id) {
            // Get pharmacy information
            $stmt = $db->prepare("
                SELECT p.*, u.email 
                FROM pharmacies p
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$pharmacy_id]);
            $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get service provider information
            $service_provider = $provider;
            
            // Get recent service requests if service provider exists
            if ($service_provider) {
                $stmt = $db->prepare("
                    SELECT sr.*, p.name as patient_name, p.phone as patient_phone
                    FROM service_requests sr
                    JOIN patients p ON sr.patient_id = p.id
                    WHERE sr.service_provider_id = ? 
                    AND sr.request_type = 'medicine_delivery'
                    ORDER BY sr.created_at DESC
                    LIMIT 5
                ");
                $stmt->execute([$provider_id]);
                $service_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Get order statistics
            $stats = [];
            
            // Total orders
            $stmt = $db->prepare("
                SELECT COUNT(*) as total,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM medicine_orders 
                WHERE pharmacy_id = ?
            ");
            $stmt->execute([$pharmacy_id]);
            $stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Today's orders
            $stmt = $db->prepare("
                SELECT COUNT(*) as count, SUM(total_amount) as revenue
                FROM medicine_orders 
                WHERE pharmacy_id = ? 
                AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$pharmacy_id]);
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get recent orders
            $stmt = $db->prepare("
                SELECT mo.*, p.name as patient_name
                FROM medicine_orders mo
                JOIN patients p ON mo.patient_id = p.id
                WHERE mo.pharmacy_id = ?
                ORDER BY mo.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$pharmacy_id]);
            $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get low stock medicines
            $stmt = $db->prepare("
                SELECT *
                FROM medicines
                WHERE pharmacy_id = ?
                AND stock_quantity <= 10
                AND status != 'discontinued'
                ORDER BY stock_quantity ASC
                LIMIT 5
            ");
            $stmt->execute([$pharmacy_id]);
            $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // If pharmacy provider, redirect to pharmacy dashboard
    if ($is_pharmacy) {
        //header('Location: index.php?module=pharmacy&action=dashboard');
        //exit;
    }
    
    // Initialize transportation stats
    $booking_stats = [
        'total_bookings' => 0,
        'pending_bookings' => 0,
        'confirmed_bookings' => 0,
        'today_bookings' => 0
    ];
    
    // Get cab request statistics if provider is a cab service
    if ($provider['service_type'] == 'transportation') {
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_bookings
            FROM cab_bookings
            WHERE provider_id = :provider_id
        ");
        $stmt->bindParam(':provider_id', $provider_id);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Initialize stats to 0 if null
        $booking_stats['total_bookings'] = $stats['total_bookings'] ?? 0;
        $booking_stats['pending_bookings'] = $stats['pending_bookings'] ?? 0;
        $booking_stats['confirmed_bookings'] = $stats['confirmed_bookings'] ?? 0;
        $booking_stats['today_bookings'] = $stats['today_bookings'] ?? 0;
        
        // Get recent cab bookings
        $stmt = $db->prepare("
            SELECT cb.*, p.name as patient_name, p.phone as patient_phone
            FROM cab_bookings cb
            INNER JOIN patients p ON cb.patient_id = p.id 
            WHERE cb.provider_id = :provider_id 
            ORDER BY cb.created_at DESC
            LIMIT 5
        ");
        $stmt->bindParam(':provider_id', $provider_id);
        $stmt->execute();
        $cab_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo $is_pharmacy ? 'Pharmacy' : 'Service Provider'; ?> Portal</title>
    <!-- Bootstrap CSS -->
    <link href="http://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo str_replace('https://', 'http://', SITE_URL); ?>assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
<div class="container">
            <a class="navbar-brand" href="index.php?module=service&action=dashboard">
                <?php if ($is_pharmacy): ?>
                <i class="fas fa-prescription-bottle-alt me-2"></i> Pharmacy Portal
                <?php else: ?>
                <i class="fas fa-hands-helping me-2"></i> Service Provider Portal
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#serviceNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="serviceNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?module=service&action=dashboard">
                           <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    
                    <?php if ($is_pharmacy): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_inventory">
                           <i class="fas fa-pills"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_orders">
                           <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_stock_history">
                           <i class="fas fa-history"></i> Stock History
                        </a>
                    </li>
                    <?php elseif ($provider['service_type'] == 'transportation'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=transport_bookings">
                           <i class="fas fa-taxi"></i> Cab Requests
                        </a>
                    </li>
    <?php endif; ?>
    
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=profile">
                           <i class="fas fa-user-circle"></i> Profile
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?module=service&action=profile">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php?module=auth&action=logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mx-3">
            <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3">
            <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?php echo $is_pharmacy ? 'Pharmacy' : 'Service Provider'; ?> Dashboard</h1>
            
            <!-- Provider Info -->
            <div class="card mb-4">
                    <div class="card-body">
                    <h5 class="card-title"><?php echo $is_pharmacy ? 'Pharmacy' : 'Provider'; ?> Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($is_pharmacy): ?>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($pharmacy['name'] ?? $provider['company_name'] ?? 'N/A'); ?></p>
                            <?php else: ?>
                            <p><strong>Company Name:</strong> <?php echo htmlspecialchars($provider['company_name'] ?? 'N/A'); ?></p>
                            <?php endif; ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($provider['email'] ?? 'N/A'); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($provider['address'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($provider['phone'] ?? 'N/A'); ?></p>
                            <p><strong>License Number:</strong> <?php echo htmlspecialchars($provider['license_number'] ?? 'N/A'); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php echo isset($provider['status']) && $provider['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($provider['status'] ?? 'Unknown')); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_pharmacy): ?>
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Today's Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['today']['count'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Today's Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        $<?php echo number_format($stats['today']['revenue'] ?? 0, 2); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['orders']['pending'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                    </div>
                </div>
            </div>
        </div>
        
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['orders']['total'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="index.php?module=service&action=pharmacy_orders" class="btn btn-sm btn-primary">View All Orders</a>
            </div>
            <div class="card-body">
                    <?php if (!empty($recent_orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($order['order_status'] ?? '') {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($order['order_status'] ?? 'unknown')); ?>
                                        </span>
                                        </td>
                                    <td><?php echo isset($order['created_at']) ? date('M d, Y', strtotime($order['created_at'])) : 'N/A'; ?></td>
                                    <td>
                                        <a href="index.php?module=service&action=get_order_details&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center mb-0">No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>
            
            <!-- Low Stock Medicines -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Low Stock Alert</h5>
                    <a href="index.php?module=service&action=pharmacy_inventory" class="btn btn-sm btn-primary">View Inventory</a>
                </div>
                    <div class="card-body">
                    <?php if (!empty($low_stock)): ?>
                    <div class="list-group">
                        <?php foreach ($low_stock as $medicine): ?>
                        <a href="index.php?module=service&action=edit_medicine&id=<?php echo $medicine['id']; ?>" 
                            class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($medicine['name']); ?></h6>
                                <small class="text-danger">
                                    <?php echo $medicine['stock_quantity']; ?> left
                                </small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars($medicine['category'] ?? 'Uncategorized'); ?></p>
                            <small>Unit: <?php echo htmlspecialchars($medicine['unit'] ?? 'N/A'); ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-center mb-0">No low stock items found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php else: /* Transportation service provider */ ?>            

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Today's Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $booking_stats['today_bookings'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $booking_stats['pending_bookings'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                    </div>
                </div>
            </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Confirmed Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $booking_stats['confirmed_bookings'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Bookings</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $booking_stats['total_bookings'] ?? 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-taxi fa-2x text-gray-300"></i>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
        
            <!-- Recent Cab Requests Table -->
            <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Cab Bookings</h5>
                    <a href="index.php?module=service&action=transport_bookings" class="btn btn-sm btn-primary">View All Bookings</a>
            </div>
            <div class="card-body">
                    <?php if (!empty($cab_requests)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Patient</th>
                                    <th>Pickup</th>
                                    <th>Destination</th>
                                    <th>Date/Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cab_requests as $request): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($request['id']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($request['patient_name']); ?><br>
                                        <small><?php echo htmlspecialchars($request['patient_phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['pickup_address']); ?></td>
                                    <td><?php echo htmlspecialchars($request['destination'] ?? ''); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($request['pickup_datetime'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($request['status'] ?? '') {
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'completed' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($request['status'] ?? 'unknown')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?module=service&action=view_booking&id=<?php echo $request['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center mb-0">No recent cab bookings found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Transportation Services Section -->
    <?php if ($provider['service_type'] == 'transportation' || $provider['service_type'] == 'both'): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-taxi me-2"></i> Transportation Services</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tachometer-alt fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Transportation Dashboard</h5>
                                    <p class="card-text">View bookings, manage schedules, and track performance</p>
                                    <a href="index.php?module=service&action=transport_dashboard" class="btn btn-primary">Go to Dashboard</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-3x mb-3 text-success"></i>
                                    <h5 class="card-title">Manage Bookings</h5>
                                    <p class="card-text">View and manage all transportation bookings</p>
                                    <a href="index.php?module=service&action=transport_bookings" class="btn btn-success">Manage Bookings</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-3x mb-3 text-info"></i>
                                    <h5 class="card-title">Booking History</h5>
                                    <p class="card-text">View past bookings and generate reports</p>
                                    <a href="index.php?module=service&action=transport_history" class="btn btn-info">View History</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
    </div>
</div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="http://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Common scripts for all pages
        $(document).ready(function() {
            // Enable tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Enable popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });
        });
    </script>
</body>
</html>
