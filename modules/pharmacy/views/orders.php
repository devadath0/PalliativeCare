<?php include_once 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Manage Orders</h1>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="module" value="pharmacy">
                        <input type="hidden" name="action" value="orders">
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                   placeholder="Search by order ID or patient name...">
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
                            <label for="date_range" class="form-label">Date Range</label>
                            <select class="form-select" id="date_range" name="date_range">
                                <option value="">All Time</option>
                                <option value="today" <?php echo ($_GET['date_range'] ?? '') === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo ($_GET['date_range'] ?? '') === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo ($_GET['date_range'] ?? '') === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="prescription" class="form-label">Prescription Status</label>
                            <select class="form-select" id="prescription" name="prescription">
                                <option value="">All Orders</option>
                                <option value="with" <?php echo ($_GET['prescription'] ?? '') === 'with' ? 'selected' : ''; ?>>With Prescription</option>
                                <option value="without" <?php echo ($_GET['prescription'] ?? '') === 'without' ? 'selected' : ''; ?>>Without Prescription</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Prescription</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link" 
                                                        onclick="viewOrderItems(<?php echo $order['id']; ?>)">
                                                    View Items
                                                </button>
                                            </td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <?php if ($order['prescription_id']): ?>
                                                    <a href="index.php?module=doctor&action=view_prescription&id=<?php echo $order['prescription_id']; ?>" 
                                                       class="btn btn-sm btn-info" target="_blank">
                                                        View Prescription
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Prescription</span>
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
                                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=pharmacy&action=get_order_details&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if ($order['order_status'] === 'pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No orders found</td>
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
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderItems(orderId) {
    fetch(`index.php?module=pharmacy&action=get_order_items&id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('orderItemsTableBody');
            tbody.innerHTML = '';
            
            data.items.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.medicine_name}</td>
                        <td>${item.quantity}</td>
                        <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td>$${parseFloat(item.total_price).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            new bootstrap.Modal(document.getElementById('orderItemsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order items');
        });
}

function viewOrderDetails(orderId) {
    fetch(`index.php?module=pharmacy&action=get_order_details&id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p><strong>Order ID:</strong> #${data.id}</p>
                        <p><strong>Order Date:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> ${data.order_status}</p>
                        <p><strong>Total Amount:</strong> $${parseFloat(data.total_amount).toFixed(2)}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Patient Information</h6>
                        <p><strong>Name:</strong> ${data.patient_name}</p>
                        <p><strong>Phone:</strong> ${data.patient_phone}</p>
                        <p><strong>Email:</strong> ${data.patient_email}</p>
                        <p><strong>Address:</strong> ${data.delivery_address}</p>
                    </div>
                </div>
                ${data.notes ? `
                    <div class="mt-3">
                        <h6>Notes</h6>
                        <p>${data.notes}</p>
                    </div>
                ` : ''}
            `;
            
            new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading order details');
        });
}

function updateOrderStatus(orderId, newStatus) {
    if (!confirm('Are you sure you want to update the order status?')) {
        return;
    }

    fetch('index.php?module=pharmacy&action=update_order_status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    });
}

function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }

    fetch('index.php?module=pharmacy&action=update_order_status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=cancelled`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error cancelling order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error cancelling order');
    });
}
</script>

<?php include_once 'includes/footer.php'; ?> 