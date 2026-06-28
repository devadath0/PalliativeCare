<?php
/**
 * Order Details View
 * Palliative Care System - Pharmacy Module
 */

// Set page title
$page_title = 'Order Details';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?module=pharmacy&action=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=pharmacy&action=orders">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order Details</li>
                </ol>
            </nav>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
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
                            <p><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                            <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Delivery Information</h6>
                            <?php if ($order['delivery_address']): ?>
                                <p><strong>Delivery Address:</strong> <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                            <?php else: ?>
                                <p><em>No delivery requested</em></p>
                            <?php endif; ?>
                            
                            <?php if ($order['notes']): ?>
                                <h6 class="mt-3">Notes</h6>
                                <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6>Order Items</h6>
                    <form action="index.php?module=pharmacy&action=update_medicine_prices" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>
                                                <input type="hidden" name="item_id[]" value="<?php echo $item['id']; ?>">
                                                <input type="number" class="form-control" name="price[]" value="<?php echo $item['unit_price']; ?>" min="0" step="0.01">
                                            </td>
                                            <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Grand Total:</th>
                                        <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">Update Prices</button>
                            </div>
                            <div>
                                <a href="index.php?module=pharmacy&action=update_order_status&id=<?php echo $order['id']; ?>&status=processing" class="btn btn-info">Mark as Processing</a>
                                <a href="index.php?module=pharmacy&action=update_order_status&id=<?php echo $order['id']; ?>&status=shipped" class="btn btn-primary">Mark as Shipped</a>
                                <a href="index.php?module=pharmacy&action=update_order_status&id=<?php echo $order['id']; ?>&status=delivered" class="btn btn-success">Mark as Delivered</a>
                                <a href="index.php?module=pharmacy&action=update_order_status&id=<?php echo $order['id']; ?>&status=cancelled" class="btn btn-danger">Cancel Order</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 