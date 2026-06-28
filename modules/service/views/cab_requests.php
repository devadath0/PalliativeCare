<?php
/**
 * Cab Requests Management View
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
    
    // Check if this provider offers cab services
    if ($provider['service_type'] != 'cab' && $provider['service_type'] != 'both') {
        throw new Exception("You are not authorized to manage cab requests");
    }
    
    $provider_id = $provider['id'];
    
    // Process request status updates
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
        $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
        
        // Validate status
        $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            throw new Exception("Invalid status");
        }
        
        // Update request
        $stmt = $conn->prepare("
            UPDATE cab_requests 
            SET status = :status, provider_notes = :notes, updated_at = NOW()
            WHERE id = :id AND provider_id = :provider_id
        ");
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $request_id);
        $stmt->bindParam(':provider_id', $provider_id);
        
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $success = "Request status updated successfully";
        } else {
            $error = "Failed to update request or request not found";
        }
    }
    
    // Get all cab requests for this provider
    $stmt = $conn->prepare("
        SELECT cr.*, 
               p.first_name as patient_first_name, 
               p.last_name as patient_last_name,
               p.phone as patient_phone
        FROM cab_requests cr
        INNER JOIN patients p ON cr.patient_id = p.id
        WHERE cr.provider_id = :provider_id
        ORDER BY 
            CASE 
                WHEN cr.status = 'pending' THEN 1
                WHEN cr.status = 'confirmed' THEN 2
                WHEN cr.status = 'completed' THEN 3
                WHEN cr.status = 'cancelled' THEN 4
            END,
            cr.pickup_date, cr.pickup_time
    ");
    $stmt->bindParam(':provider_id', $provider_id);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Cab Requests Management</h2>
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
            <ul class="nav nav-tabs card-header-tabs" id="requestTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="confirmed-tab" data-toggle="tab" href="#confirmed" role="tab">Confirmed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="completed-tab" data-toggle="tab" href="#completed" role="tab">Completed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cancelled-tab" data-toggle="tab" href="#cancelled" role="tab">Cancelled</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="requestTabContent">
                <!-- Pending Requests -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <?php
                    $hasPending = false;
                    foreach ($requests as $request) {
                        if ($request['status'] == 'pending') {
                            $hasPending = true;
                            break;
                        }
                    }
                    
                    if (!$hasPending): 
                    ?>
                        <div class="alert alert-info">You have no pending cab requests.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pickup Date</th>
                                        <th>Pickup Time</th>
                                        <th>Patient</th>
                                        <th>Pickup Address</th>
                                        <th>Destination</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <?php if ($request['status'] == 'pending'): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($request['pickup_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($request['pickup_time'])); ?></td>
                                                <td>
                                                    <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $request['patient_phone']; ?></small>
                                                </td>
                                                <td><?php echo $request['pickup_address']; ?></td>
                                                <td><?php echo $request['destination']; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#confirmModal<?php echo $request['id']; ?>">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $request['id']; ?>">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                    
                                                    <!-- Confirm Modal -->
                                                    <div class="modal fade" id="confirmModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirm Cab Request</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to confirm this cab request from <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?> on <?php echo date('M d, Y', strtotime($request['pickup_date'])); ?> at <?php echo date('h:i A', strtotime($request['pickup_time'])); ?>?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="notes<?php echo $request['id']; ?>">Additional Notes (Optional)</label>
                                                                            <textarea name="notes" id="notes<?php echo $request['id']; ?>" class="form-control" rows="3"><?php echo $request['provider_notes']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                        <input type="hidden" name="status" value="confirmed">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-success">Confirm Request</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Reject Modal -->
                                                    <div class="modal fade" id="rejectModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Cab Request</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to reject this cab request from <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?>?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="reject_notes<?php echo $request['id']; ?>">Reason for Rejection</label>
                                                                            <textarea name="notes" id="reject_notes<?php echo $request['id']; ?>" class="form-control" rows="3" required></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                        <input type="hidden" name="status" value="cancelled">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-danger">Reject Request</button>
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
                
                <!-- Confirmed Requests -->
                <div class="tab-pane fade" id="confirmed" role="tabpanel">
                    <?php
                    $hasConfirmed = false;
                    foreach ($requests as $request) {
                        if ($request['status'] == 'confirmed') {
                            $hasConfirmed = true;
                            break;
                        }
                    }
                    
                    if (!$hasConfirmed): 
                    ?>
                        <div class="alert alert-info">You have no confirmed cab requests.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pickup Date</th>
                                        <th>Pickup Time</th>
                                        <th>Patient</th>
                                        <th>Pickup Address</th>
                                        <th>Destination</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <?php if ($request['status'] == 'confirmed'): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($request['pickup_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($request['pickup_time'])); ?></td>
                                                <td>
                                                    <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $request['patient_phone']; ?></small>
                                                </td>
                                                <td><?php echo $request['pickup_address']; ?></td>
                                                <td><?php echo $request['destination']; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#completeModal<?php echo $request['id']; ?>">
                                                        <i class="fas fa-check-circle"></i> Mark as Completed
                                                    </button>
                                                    
                                                    <!-- Complete Modal -->
                                                    <div class="modal fade" id="completeModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Complete Cab Request</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form method="post" action="">
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to mark this cab request as completed?</p>
                                                                        
                                                                        <div class="form-group">
                                                                            <label for="complete_notes<?php echo $request['id']; ?>">Additional Notes (Optional)</label>
                                                                            <textarea name="notes" id="complete_notes<?php echo $request['id']; ?>" class="form-control" rows="3"><?php echo $request['provider_notes']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                        <input type="hidden" name="status" value="completed">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-primary">Complete Request</button>
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
                
                <!-- Completed Requests -->
                <div class="tab-pane fade" id="completed" role="tabpanel">
                    <?php
                    $hasCompleted = false;
                    foreach ($requests as $request) {
                        if ($request['status'] == 'completed') {
                            $hasCompleted = true;
                            break;
                        }
                    }
                    
                    if (!$hasCompleted): 
                    ?>
                        <div class="alert alert-info">You have no completed cab requests.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pickup Date</th>
                                        <th>Pickup Time</th>
                                        <th>Patient</th>
                                        <th>Pickup Address</th>
                                        <th>Destination</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <?php if ($request['status'] == 'completed'): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($request['pickup_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($request['pickup_time'])); ?></td>
                                                <td>
                                                    <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $request['patient_phone']; ?></small>
                                                </td>
                                                <td><?php echo $request['pickup_address']; ?></td>
                                                <td><?php echo $request['destination']; ?></td>
                                                <td><?php echo $request['provider_notes']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Cancelled Requests -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <?php
                    $hasCancelled = false;
                    foreach ($requests as $request) {
                        if ($request['status'] == 'cancelled') {
                            $hasCancelled = true;
                            break;
                        }
                    }
                    
                    if (!$hasCancelled): 
                    ?>
                        <div class="alert alert-info">You have no cancelled cab requests.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pickup Date</th>
                                        <th>Pickup Time</th>
                                        <th>Patient</th>
                                        <th>Pickup Address</th>
                                        <th>Destination</th>
                                        <th>Reason for Cancellation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <?php if ($request['status'] == 'cancelled'): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($request['pickup_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($request['pickup_time'])); ?></td>
                                                <td>
                                                    <?php echo $request['patient_first_name'] . ' ' . $request['patient_last_name']; ?><br>
                                                    <small class="text-muted">Phone: <?php echo $request['patient_phone']; ?></small>
                                                </td>
                                                <td><?php echo $request['pickup_address']; ?></td>
                                                <td><?php echo $request['destination']; ?></td>
                                                <td><?php echo $request['provider_notes']; ?></td>
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
