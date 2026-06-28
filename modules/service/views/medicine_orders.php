<?php
/**
 * Medicine Orders Management View
 * Palliative Care System - Service Provider Module
 */

// Check if user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'service') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

// Include header
include_once 'views/includes/header.php';

// Get service provider ID from user ID
try {
    $stmt = $conn->prepare("SELECT id, service_type FROM service_providers WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$provider) {
        throw new Exception("Service provider record not found");
    }
    
    // Check if this provider offers pharmacy services
    if ($provider['service_type'] != 'pharmacy' && $provider['service_type'] != 'both') {
        throw new Exception("You are not authorized to manage medicine orders");
    }
    
    $provider_id = $provider['id'];
    
    // Process order status updates
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
        
        // Validate status
        $valid_statuses = ['pending', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            throw new Exception("Invalid status");
        }
        
        // Update order
        $stmt = $conn->prepare("
            UPDATE medicine_orders 
            SET status = :status, pharmacy_notes = :notes, updated_at = NOW()
            WHERE id = :id AND pharmacy_id = :pharmacy_id
        ");
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $order_id);
        $stmt->bindParam(':pharmacy_id', $provider_id);
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $success = "Order status updated successfully";
        } else {
            $error = "Failed to update order or order not found";
        }
    }
    
    // Get all medicine orders for this pharmacy
    $stmt = $conn->prepare("
        SELECT mo.*, 
               p.first_name as patient_first_name, 
               p.last_name as patient_last_name,
               p.phone as patient_phone,
               pr.id as prescription_id,
               d.first_name as doctor_first_name,
               d.last_name as doctor_last_name
        FROM medicine_orders mo
        INNER JOIN patients p ON mo.patient_id = p.id
        LEFT JOIN prescriptions pr ON mo.prescription_id = pr.id
        LEFT JOIN doctors d ON pr.doctor_id = d.id
        WHERE mo.pharmacy_id = :pharmacy_id
        ORDER BY 
            CASE 
                WHEN mo.status = 'pending' THEN 1
                WHEN mo.status = 'processing' THEN 2
                WHEN mo.status = 'out_for_delivery' THEN 3
                WHEN mo.status = 'delivered' THEN 4
                WHEN mo.status = 'cancelled' THEN 5
            END,
            mo.created_at DESC
    ");
    $stmt->bindParam(':pharmacy_id', $provider_id);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Medicine Orders Management</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="index.php?module=service&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="orderTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="processing-tab" data-toggle="tab" href="#processing" role="tab">Processing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="delivery-tab" data-toggle="tab" href="#delivery" role="tab">Out for Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="delivered-tab" data-toggle="tab" href="#delivered" role="tab">Delivered</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cancelled-tab" data-toggle="tab" href="#cancelled" role="tab">Cancelled</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="orderTabContent">
                <!-- Pending Orders -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <?php
                    $hasPending = false;
                    foreach ($orders as $order) {
                        if ($order['status'] == 'pending') {
                            $hasPending = true;
                            break;
                        }
                    }
                    
                    if (!$hasPending): 
                    ?>
                        <div class="alert alert-info">You have no pending medicine orders.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Patient</th>
                                        <th>Order Details</th>
                                        <th>Delivery Address</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <?php echo $order['patient_first_name'] . ' ' . $order['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $order['patient_phone']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($order['prescription_id']): ?>
                                                        <strong>Prescription Order</strong><br>
                                                        <small class="text-muted">
                                                            Dr. <?php echo $order['doctor_first_name'] . ' ' . $order['doctor_last_name']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <strong>Manual Order</strong><br>
                                                        <small class="text-muted"><?php echo $order['medication_details']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $order['delivery_address']; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#processModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-check"></i> Process Order
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                    
                                                    <!-- Process Modal -->
                                                    <div class="modal fade" id="processModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Process Order</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Start processing order #<?php echo $order['id']; ?>?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="notes<?php echo $order['id']; ?>">Processing Notes (Optional)</label>
                                                                            <textarea name="notes" id="notes<?php echo $order['id']; ?>" class="form-control" rows="3"><?php echo $order['pharmacy_notes']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                        <input type="hidden" name="status" value="processing">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-success">Start Processing</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Reject Modal -->
                                                    <div class="modal fade" id="rejectModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Order</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to reject this order?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="reject_notes<?php echo $order['id']; ?>">Reason for Rejection</label>
                                                                            <textarea name="notes" id="reject_notes<?php echo $order['id']; ?>" class="form-control" rows="3" required></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                        <input type="hidden" name="status" value="cancelled">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-danger">Reject Order</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Processing Orders -->
                <div class="tab-pane fade" id="processing" role="tabpanel">
                    <?php
                    $hasProcessing = false;
                    foreach ($orders as $order) {
                        if ($order['status'] == 'processing') {
                            $hasProcessing = true;
                            break;
                        }
                    }
                    
                    if (!$hasProcessing): 
                    ?>
                        <div class="alert alert-info">No orders are currently being processed.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Patient</th>
                                        <th>Order Details</th>
                                        <th>Delivery Address</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php if ($order['status'] == 'processing'): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <?php echo $order['patient_first_name'] . ' ' . $order['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $order['patient_phone']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($order['prescription_id']): ?>
                                                        <strong>Prescription Order</strong><br>
                                                        <small class="text-muted">
                                                            Dr. <?php echo $order['doctor_first_name'] . ' ' . $order['doctor_last_name']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <strong>Manual Order</strong><br>
                                                        <small class="text-muted"><?php echo $order['medication_details']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $order['delivery_address']; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deliveryModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-truck"></i> Send for Delivery
                                                    </button>
                                                    
                                                    <!-- Delivery Modal -->
                                                    <div class="modal fade" id="deliveryModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Send for Delivery</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Send order #<?php echo $order['id']; ?> for delivery?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="delivery_notes<?php echo $order['id']; ?>">Delivery Instructions (Optional)</label>
                                                                            <textarea name="notes" id="delivery_notes<?php echo $order['id']; ?>" class="form-control" rows="3"><?php echo $order['pharmacy_notes']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                        <input type="hidden" name="status" value="out_for_delivery">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-primary">Send for Delivery</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Out for Delivery Orders -->
                <div class="tab-pane fade" id="delivery" role="tabpanel">
                    <?php
                    $hasDelivery = false;
                    foreach ($orders as $order) {
                        if ($order['status'] == 'out_for_delivery') {
                            $hasDelivery = true;
                            break;
                        }
                    }
                    
                    if (!$hasDelivery): 
                    ?>
                        <div class="alert alert-info">No orders are currently out for delivery.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Patient</th>
                                        <th>Order Details</th>
                                        <th>Delivery Address</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php if ($order['status'] == 'out_for_delivery'): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <?php echo $order['patient_first_name'] . ' ' . $order['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $order['patient_phone']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($order['prescription_id']): ?>
                                                        <strong>Prescription Order</strong><br>
                                                        <small class="text-muted">
                                                            Dr. <?php echo $order['doctor_first_name'] . ' ' . $order['doctor_last_name']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <strong>Manual Order</strong><br>
                                                        <small class="text-muted"><?php echo $order['medication_details']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $order['delivery_address']; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#completeModal<?php echo $order['id']; ?>">
                                                        <i class="fas fa-check-circle"></i> Mark as Delivered
                                                    </button>
                                                    
                                                    <!-- Complete Modal -->
                                                    <div class="modal fade" id="completeModal<?php echo $order['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Complete Delivery</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Mark order #<?php echo $order['id']; ?> as delivered?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="complete_notes<?php echo $order['id']; ?>">Delivery Notes (Optional)</label>
                                                                            <textarea name="notes" id="complete_notes<?php echo $order['id']; ?>" class="form-control" rows="3"><?php echo $order['pharmacy_notes']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                        <input type="hidden" name="status" value="delivered">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-success">Complete Delivery</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Delivered Orders -->
                <div class="tab-pane fade" id="delivered" role="tabpanel">
                    <?php
                    $hasDelivered = false;
                    foreach ($orders as $order) {
                        if ($order['status'] == 'delivered') {
                            $hasDelivered = true;
                            break;
                        }
                    }
                    
                    if (!$hasDelivered): 
                    ?>
                        <div class="alert alert-info">No delivered orders found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Patient</th>
                                        <th>Order Details</th>
                                        <th>Delivery Address</th>
                                        <th>Delivered At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php if ($order['status'] == 'delivered'): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <?php echo $order['patient_first_name'] . ' ' . $order['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $order['patient_phone']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($order['prescription_id']): ?>
                                                        <strong>Prescription Order</strong><br>
                                                        <small class="text-muted">
                                                            Dr. <?php echo $order['doctor_first_name'] . ' ' . $order['doctor_last_name']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <strong>Manual Order</strong><br>
                                                        <small class="text-muted"><?php echo $order['medication_details']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $order['delivery_address']; ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($order['updated_at'])); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cancelled Orders -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <?php
                    $hasCancelled = false;
                    foreach ($orders as $order) {
                        if ($order['status'] == 'cancelled') {
                            $hasCancelled = true;
                            break;
                        }
                    }
                    
                    if (!$hasCancelled): 
                    ?>
                        <div class="alert alert-info">No cancelled orders found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Patient</th>
                                        <th>Order Details</th>
                                        <th>Delivery Address</th>
                                        <th>Reason for Cancellation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php if ($order['status'] == 'cancelled'): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <?php echo $order['patient_first_name'] . ' ' . $order['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $order['patient_phone']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($order['prescription_id']): ?>
                                                        <strong>Prescription Order</strong><br>
                                                        <small class="text-muted">
                                                            Dr. <?php echo $order['doctor_first_name'] . ' ' . $order['doctor_last_name']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <strong>Manual Order</strong><br>
                                                        <small class="text-muted"><?php echo $order['medication_details']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $order['delivery_address']; ?></td>
                                                <td><?php echo $order['pharmacy_notes']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'views/includes/footer.php';
?>
