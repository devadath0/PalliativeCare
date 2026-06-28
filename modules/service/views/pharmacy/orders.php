<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'Manage Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Pharmacy Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?module=service&action=pharmacy_dashboard">
                <i class="fas fa-prescription-bottle-alt me-2"></i> Pharmacy Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pharmacyNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="pharmacyNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_dashboard">
                           <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_inventory">
                           <i class="fas fa-pills"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?module=service&action=pharmacy_orders">
                           <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_stock_history">
                           <i class="fas fa-history"></i> Stock History
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Manage Medicine Orders</h1>
                    <div>
                        <a href="index.php?module=service&action=pharmacy_export_orders" class="btn btn-outline-primary">
                            <i class="fas fa-file-export me-1"></i> Export Orders
                        </a>
                    </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filter Orders</h5>
                    </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="module" value="service">
                        <input type="hidden" name="action" value="pharmacy_orders">
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                           placeholder="Order #, patient name, phone...">
                                </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?php echo ($current_status === 'all') ? 'selected' : ''; ?>>All Orders</option>
                                <option value="pending" <?php echo ($current_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($current_status === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($current_status === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($current_status === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($current_status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                            
                            <div class="col-md-2">
                                <label for="payment_status" class="form-label">Payment Status</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <option value="all" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'all') ? 'selected' : ''; ?>>All Payments</option>
                                    <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending Payment</option>
                                    <option value="completed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'completed') ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                        
                        <div class="col-md-2">
                            <label for="date_range" class="form-label">Date Range</label>
                            <select class="form-select" id="date_range" name="date_range">
                                <option value="">All Time</option>
                                <option value="today" <?php echo ($_GET['date_range'] ?? '') === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo ($_GET['date_range'] ?? '') === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo ($_GET['date_range'] ?? '') === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                        </div>
                        
                            <div class="col-md-2">
                                <label for="prescription" class="form-label">Prescription</label>
                            <select class="form-select" id="prescription" name="prescription">
                                <option value="">All Orders</option>
                                <option value="with" <?php echo ($_GET['prescription'] ?? '') === 'with' ? 'selected' : ''; ?>>With Prescription</option>
                                <option value="without" <?php echo ($_GET['prescription'] ?? '') === 'without' ? 'selected' : ''; ?>>Without Prescription</option>
                            </select>
                        </div>
                        
                            <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

                <?php if ($current_status === 'pending'): ?>
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading mb-1">Pending Orders</h5>
                            <p class="mb-0">These orders need your attention! Process them quickly to ensure timely delivery to patients.</p>
                        </div>
                        <div class="ms-auto">
                            <a href="#" class="btn btn-outline-dark" id="processAllPending">
                                <i class="fas fa-check-double me-1"></i> Process All Selected
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <?php if ($current_status === 'pending'): ?>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <?php endif; ?>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                        <th>Delivery</th>
                                    <th>Status</th>
                                        <th>Payment</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                            <tr class="<?php echo $this->getOrderRowClass($order['order_status']); ?>">
                                                <?php if ($current_status === 'pending'): ?>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input order-checkbox" type="checkbox" value="<?php echo $order['id']; ?>">
                                                    </div>
                                                </td>
                                                <?php endif; ?>
                                                <td>
                                                    <strong>#<?php echo htmlspecialchars($order['id']); ?></strong>
                                                    <?php if ($order['order_number']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($order['order_number']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($order['patient_name']); ?>
                                                    <?php if (isset($order['patient_phone'])): ?>
                                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($order['patient_phone']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link" 
                                                        onclick="viewOrderItems(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-pills me-1"></i> View Items
                                                </button>
                                            </td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                    <?php if (!empty($order['delivery_address'])): ?>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-truck me-1"></i> Delivery
                                                        </span>
                                                <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-store me-1"></i> Pickup
                                                        </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" 
                                                        onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)"
                                                        <?php echo $order['order_status'] === 'cancelled' ? 'disabled' : ''; ?>>
                                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                        echo date('M d, Y', strtotime($order['created_at'])); 
                                                        echo '<br><small class="text-muted">' . date('h:i A', strtotime($order['created_at'])) . '</small>';
                                                    ?>
                                                </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=service&action=pharmacy_order_details&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if ($order['order_status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'processing')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="if(confirm('Are you sure you want to cancel this order?')) updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                            <td colspan="<?php echo ($current_status === 'pending') ? '10' : '9'; ?>" class="text-center">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Items Modal -->
<div class="modal fade" id="orderItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsTableBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="viewFullOrderBtn" class="btn btn-primary">View Full Order</a>
            </div>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <form id="statusUpdateForm" action="index.php?module=service&action=pharmacy_update_order_status" method="post" style="display: none;">
        <input type="hidden" name="order_id" id="order_id">
        <input type="hidden" name="status" id="status">
    </form>

    <!-- Bulk Process Form -->
    <form id="bulkProcessForm" action="index.php?module=service&action=pharmacy_bulk_process_orders" method="post" style="display: none;">
        <input type="hidden" name="order_ids" id="selected_order_ids">
    </form>

<script>
    // View order items
function viewOrderItems(orderId) {
    fetch(`index.php?module=service&action=pharmacy_get_order_items&id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('orderItemsTableBody');
            tbody.innerHTML = '';
                
                let totalAmount = 0;
            
            data.items.forEach(item => {
                    const itemTotal = parseFloat(item.total_price || 0);
                    totalAmount += itemTotal;
                    
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.medicine_name || 'Unknown'}</td>
                            <td>${item.quantity}</td>
                            <td>$${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td>$${itemTotal.toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                // Add total row
                tbody.innerHTML += `
                    <tr class="table-light">
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$${totalAmount.toFixed(2)}</strong></td>
                    </tr>
                `;
                
                // Set the view full order button link
                document.getElementById('viewFullOrderBtn').href = `index.php?module=service&action=pharmacy_order_details&id=${orderId}`;
            
            new bootstrap.Modal(document.getElementById('orderItemsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order items');
        });
}

    // Update order status
    function updateOrderStatus(orderId, status) {
        document.getElementById('order_id').value = orderId;
        document.getElementById('status').value = status;
        document.getElementById('statusUpdateForm').submit();
    }

    // Select all checkboxes
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Process all selected orders
    const processAllBtn = document.getElementById('processAllPending');
    if (processAllBtn) {
        processAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const checkboxes = document.querySelectorAll('.order-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one order to process');
        return;
    }

            const orderIds = Array.from(checkboxes).map(cb => cb.value);
            
            if (confirm(`Are you sure you want to process ${orderIds.length} selected order(s)?`)) {
                document.getElementById('selected_order_ids').value = orderIds.join(',');
                document.getElementById('bulkProcessForm').submit();
            }
    });
}
</script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 