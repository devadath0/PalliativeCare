<?php require_once __DIR__ . '/../../../views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><?php echo $page_title; ?></h2>
                <a href="index.php?module=patient&action=order_medicine" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Order Date:</th>
                                    <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <?php if ($order['order_status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($order['order_status'] == 'processing'): ?>
                                            <span class="badge bg-info">Processing</span>
                                        <?php elseif ($order['order_status'] == 'shipped'): ?>
                                            <span class="badge bg-primary">Shipped</span>
                                        <?php elseif ($order['order_status'] == 'delivered'): ?>
                                            <span class="badge bg-success">Delivered</span>
                                        <?php elseif ($order['order_status'] == 'cancelled'): ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <?php if ($order['payment_status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($order['payment_status'] == 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif ($order['payment_status'] == 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php elseif ($order['payment_status'] == 'refunded'): ?>
                                            <span class="badge bg-info">Refunded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($order['delivery_address'])): ?>
                                <tr>
                                    <th>Delivery Address:</th>
                                    <td><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($order['notes'])): ?>
                                <tr>
                                    <th>Notes:</th>
                                    <td><?php echo nl2br(htmlspecialchars($order['notes'])); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Pharmacy Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Name:</th>
                                    <td><?php echo htmlspecialchars($order['pharmacy_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo htmlspecialchars($order['pharmacy_phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td><?php echo htmlspecialchars($order['pharmacy_address'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                            
                            <?php if (!empty($order['prescription_diagnosis'])): ?>
                            <h6 class="mt-3">Prescription Information</h6>
                            <div class="card">
                                <div class="card-body">
                                    <?php echo nl2br(htmlspecialchars($order['prescription_diagnosis'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6 class="mt-4">Order Items</h6>
                    <?php if (empty($items)): ?>
                        <div class="alert alert-info">No items found for this order.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Medicine</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($item['medicine_name'] ?? 'Medicine #' . $item['medicine_id']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($order['order_status'], ['pending', 'processing'])): ?>
                        <div class="mt-4">
                            <?php if ($order['payment_status'] == 'pending'): ?>
                                <a href="index.php?module=patient&action=payment&type=medicine_order&id=<?php echo $order['id']; ?>" 
                                   class="btn btn-primary me-2">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </a>
                            <?php endif; ?>
                            <a href="index.php?module=patient&action=cancel_medicine_order&id=<?php echo $order['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to cancel this order?');">
                                <i class="fas fa-times"></i> Cancel Order
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?> 