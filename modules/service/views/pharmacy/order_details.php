<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Order Details View
 * Palliative Care System - Pharmacy Module
 */

// Set page title
$page_title = 'Order Details';
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
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_orders">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order Details</li>
                </ol>
            </nav>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order #<?php echo $order['order_number'] ?? $order['id']; ?></h5>
                    <div>
                        <span class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                            <p><strong>Patient:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                                <p><strong>Payment Status:</strong> 
                                    <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </p>
                            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Delivery Information</h6>
                            <?php if ($order['delivery_address']): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-truck me-2"></i> <strong>Delivery Requested</strong>
                                    </div>
                                    <p><strong>Delivery Address:</strong><br> <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? 'No delivery address provided')); ?></p>
                            <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-store me-2"></i> <strong>Pickup at Pharmacy</strong>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($order['prescription_id'])): ?>
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-prescription me-2"></i> <strong>Prescription Required</strong>
                                        <div class="mt-2">
                                            <a href="index.php?module=service&action=pharmacy_view_prescription&id=<?php echo $order['prescription_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-eye me-1"></i> View Prescription
                                            </a>
                                        </div>
                                    </div>
                            <?php endif; ?>
                            
                            <?php if ($order['notes']): ?>
                                    <h6 class="mt-3">Patient Notes</h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                                        </div>
                                    </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6>Order Items</h6>
                    <form action="index.php?module=service&action=pharmacy_update_medicine_prices" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                            <th>Stock Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        <?php 
                                        $all_in_stock = true;
                                        foreach ($order_items as $item): 
                                            $in_stock = isset($item['in_stock']) ? $item['in_stock'] : true;
                                            $stock_qty = isset($item['stock_quantity']) ? $item['stock_quantity'] : 'Unknown';
                                            if (!$in_stock) $all_in_stock = false;
                                        ?>
                                            <tr class="<?php echo !$in_stock ? 'table-danger' : ''; ?>">
                                            <td><?php echo htmlspecialchars($item['medicine_name'] ?? 'Unknown Medicine'); ?></td>
                                            <td><?php echo $item['quantity'] ?? 0; ?></td>
                                            <td>
                                                <input type="hidden" name="item_id[]" value="<?php echo $item['id'] ?? 0; ?>">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" name="price[]" value="<?php echo $item['unit_price'] ?? 0; ?>" min="0" step="0.01">
                                                    </div>
                                            </td>
                                            <td>$<?php echo number_format($item['total_price'] ?? 0, 2); ?></td>
                                                <td>
                                                    <?php if ($in_stock): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i> In Stock (<?php echo $stock_qty; ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle me-1"></i> Out of Stock (<?php echo $stock_qty; ?>)
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Grand Total:</th>
                                            <th colspan="2">$<?php echo number_format($order['total_amount'], 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">Order Actions</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <?php if ($order['order_status'] === 'pending'): ?>
                                                    <?php if ($all_in_stock): ?>
                                                        <button type="button" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'processing')" class="btn btn-info">
                                                            <i class="fas fa-check-double me-1"></i> Accept & Start Processing
                                                        </button>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-1"></i> Some items are out of stock
                                                        </div>
                                                        <button type="button" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'processing')" class="btn btn-outline-info">
                                                            Process Anyway
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" onclick="if(confirm('Are you sure you want to cancel this order?')) updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')" class="btn btn-outline-danger">
                                                        <i class="fas fa-times me-1"></i> Cancel Order
                                                    </button>
                                                <?php elseif ($order['order_status'] === 'processing'): ?>
                                                    <button type="button" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'shipped')" class="btn btn-primary">
                                                        <i class="fas fa-shipping-fast me-1"></i> Mark as Shipped
                                                    </button>
                                                <?php elseif ($order['order_status'] === 'shipped'): ?>
                                                    <button type="button" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'delivered')" class="btn btn-success">
                                                        <i class="fas fa-check-circle me-1"></i> Mark as Delivered
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0">Pricing</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-1"></i> You can adjust the price of each medicine if needed.
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-dollar-sign me-1"></i> Update Prices
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($order['delivery_address'] && ($order['order_status'] === 'processing' || $order['order_status'] === 'shipped')): ?>
                                    <div class="card mt-3">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Delivery Options</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid">
                                                <a href="#" class="btn btn-outline-info" id="printAddressLabel">
                                                    <i class="fas fa-print me-1"></i> Print Address Label
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <form id="statusUpdateForm" action="index.php?module=service&action=pharmacy_update_order_status" method="post" style="display: none;">
        <input type="hidden" name="order_id" id="update_order_id">
        <input type="hidden" name="status" id="update_status">
    </form>

    <!-- Address Label Modal -->
    <div class="modal fade" id="addressLabelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Address Label</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                <div class="modal-body" id="printableLabel">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-center">DELIVERY ADDRESS</h6>
                            <hr>
                            <p><strong>To:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                            <p><strong>Address:</strong><br> <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? 'No delivery address provided')); ?></p>
                            <hr>
                            <p><strong>Order:</strong> #<?php echo $order['order_number'] ?? $order['id']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printLabel()">Print</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateOrderStatus(orderId, status) {
        document.getElementById('update_order_id').value = orderId;
        document.getElementById('update_status').value = status;
        document.getElementById('statusUpdateForm').submit();
    }

    document.getElementById('printAddressLabel').addEventListener('click', function(e) {
        e.preventDefault();
        const addressModal = new bootstrap.Modal(document.getElementById('addressLabelModal'));
        addressModal.show();
    });

    function printLabel() {
        const printContent = document.getElementById('printableLabel').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        
        // Reload the page to restore all functionality
        window.location.reload();
    }
    </script>

<?php
// Helper function to get badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?> 

    <?php include_once 'includes/footer.php'; ?>
</body>
</html> 