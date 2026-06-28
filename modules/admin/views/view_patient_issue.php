<?php
/**
 * Admin View Patient Issue Details
 * Palliative Care System
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Set page title and current page for navigation
$page_title = 'View Patient Issue #' . $issue['id'];
$current_page = 'patient_issues';

// Include header
require_once 'modules/admin/views/includes/header.php';
?>
<!-- Include custom CSS for issue reporting -->
<link href="assets/css/issue-reporting.css" rel="stylesheet">

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?module=admin&action=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=admin&action=patient_issues">Patient Issues</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Issue #<?= $issue['id'] ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Issue Details -->
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Issue #<?= $issue['id'] ?></h5>
                    <span class="status-badge <?php
                        if ($issue['status'] === 'pending') echo 'status-pending';
                        elseif ($issue['status'] === 'in_progress') echo 'status-in-progress';
                        elseif ($issue['status'] === 'resolved') echo 'status-resolved';
                        else echo 'status-closed';
                    ?>">
                        <i class="fas <?php
                            if ($issue['status'] === 'pending') echo 'fa-clock';
                            elseif ($issue['status'] === 'in_progress') echo 'fa-spinner';
                            elseif ($issue['status'] === 'resolved') echo 'fa-check-circle';
                            else echo 'fa-times-circle';
                        ?>"></i>
                        <?= ucfirst($issue['status']) ?>
                    </span>
                </div>
                <div class="card-body p-4 issue-body">
                    <h4 class="card-title"><?= htmlspecialchars($issue['title']) ?></h4>
                    
                    <div class="mb-4">
                        <div class="row text-muted mb-3">
                            <div class="col-md-6">
                                <strong>Reported By:</strong> <?= htmlspecialchars($patient['name']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($issue['created_at'])) ?>
                            </div>
                        </div>
                        <div class="row text-muted mb-3">
                            <div class="col-md-6">
                                <strong>Issue Type:</strong> 
                                <?php
                                switch ($issue['issue_type']) {
                                    case 'medicine_delivery':
                                        echo 'Medicine Delivery';
                                        break;
                                    case 'cab_service':
                                        echo 'Cab Service';
                                        break;
                                    case 'appointment':
                                        echo 'Appointment';
                                        break;
                                    case 'pharmacy':
                                        echo 'Pharmacy Service';
                                        break;
                                    case 'doctor':
                                        echo 'Doctor Service';
                                        break;
                                    default:
                                        echo 'Other';
                                }
                                ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Patient Contact:</strong> <?= htmlspecialchars($patient['email']) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($reference_details)): ?>
                            <div class="alert alert-light mb-3">
                                <strong>Related Service:</strong>
                                <div class="mt-2">
                                    <?php if ($issue['issue_type'] === 'medicine_delivery'): ?>
                                        <div><i class="fas fa-pills text-primary me-2"></i><strong>Order:</strong> #<?= $reference_details['order_number'] ?></div>
                                        <div><strong>Pharmacy:</strong> <?= $reference_details['pharmacy_name'] ?? 'Unknown Pharmacy' ?></div>
                                        <div><strong>Status:</strong> <?= ucfirst($reference_details['status']) ?></div>
                                        <div><strong>Date:</strong> <?= date('M j, Y', strtotime($reference_details['created_at'])) ?></div>
                                        <a href="index.php?module=admin&action=view_medicine_order&id=<?= $reference_details['id'] ?>" class="btn btn-sm btn-outline-primary mt-2 btn-report">
                                            <i class="fas fa-external-link-alt"></i>View Order
                                        </a>
                                    <?php elseif ($issue['issue_type'] === 'cab_service'): ?>
                                        <div><i class="fas fa-taxi text-warning me-2"></i><strong>Booking:</strong> #<?= $reference_details['id'] ?></div>
                                        <div><strong>Provider:</strong> <?= $reference_details['provider_name'] ?? 'Unknown Provider' ?></div>
                                        <div><strong>Pickup:</strong> <?= date('M j, Y, g:i a', strtotime($reference_details['pickup_datetime'])) ?></div>
                                        <div><strong>Status:</strong> <?= ucfirst($reference_details['status']) ?></div>
                                        <a href="index.php?module=admin&action=view_cab_booking&id=<?= $reference_details['id'] ?>" class="btn btn-sm btn-outline-warning mt-2 btn-report">
                                            <i class="fas fa-external-link-alt"></i>View Booking
                                        </a>
                                    <?php elseif ($issue['issue_type'] === 'appointment'): ?>
                                        <div><i class="fas fa-calendar-check text-info me-2"></i><strong>Doctor:</strong> <?= $reference_details['doctor_name'] ?></div>
                                        <div><strong>Date:</strong> <?= date('M j, Y, g:i a', strtotime($reference_details['appointment_date'])) ?></div>
                                        <div><strong>Status:</strong> <?= ucfirst($reference_details['status']) ?></div>
                                        <a href="index.php?module=admin&action=view_appointment&id=<?= $reference_details['id'] ?>" class="btn btn-sm btn-outline-info mt-2 btn-report">
                                            <i class="fas fa-external-link-alt"></i>View Appointment
                                        </a>
                                    <?php else: ?>
                                        <div><i class="fas fa-info-circle text-secondary me-2"></i><strong>Reference ID:</strong> <?= $issue['reference_id'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card issue-card mb-4">
                        <div class="card-header bg-light issue-header">
                            <h6 class="mb-0">Patient's Description</h6>
                        </div>
                        <div class="card-body issue-body">
                            <p class="card-text"><?= nl2br(htmlspecialchars($issue['description'])) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($issue['admin_response'])): ?>
                        <div class="card border-success mb-4 issue-card">
                            <div class="card-header bg-success bg-opacity-10 issue-header">
                                <h6 class="mb-0 text-success">Management Response</h6>
                            </div>
                            <div class="card-body issue-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($issue['admin_response'])) ?></p>
                                <?php if ($issue['resolved_at']): ?>
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-check-circle me-1"></i> Resolved on <?= date('F j, Y, g:i a', strtotime($issue['resolved_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($issue['patient_response'])): ?>
                        <div class="card border-info mb-4 issue-card">
                            <div class="card-header bg-info bg-opacity-10 issue-header">
                                <h6 class="mb-0 text-info">Patient Response</h6>
                            </div>
                            <div class="card-body issue-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($issue['patient_response'])) ?></p>
                                <?php if (!empty($issue['patient_response_at'])): ?>
                                    <div class="text-muted small mt-2">
                                        <i class="far fa-clock me-1"></i> Received on <?= date('F j, Y, g:i a', strtotime($issue['patient_response_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($issue['status'] !== 'resolved' && $issue['status'] !== 'closed'): ?>
                        <form action="index.php?module=admin&action=respond_to_issue" method="post" class="admin-response-form">
                            <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                            
                            <div class="mb-3">
                                <label for="admin_response" class="form-label">Response to Patient</label>
                                <textarea class="form-control" id="admin_response" name="admin_response" rows="5" required><?= htmlspecialchars($issue['admin_response'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Update Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?= $issue['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $issue['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="notify_patient" name="notify_patient" checked>
                                <label class="form-check-label" for="notify_patient">
                                    Send notification to patient
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php?module=admin&action=patient_issues" class="btn btn-outline-secondary btn-report">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-report">Submit Response</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?module=admin&action=patient_issues" class="btn btn-outline-secondary btn-report">
                                <i class="fas fa-arrow-left"></i>Back to Issues
                            </a>
                            <?php if ($issue['status'] === 'resolved' || $issue['status'] === 'closed'): ?>
                                <a href="index.php?module=admin&action=reopen_issue&id=<?= $issue['id'] ?>" class="btn btn-warning btn-report">
                                    <i class="fas fa-redo"></i>Reopen Issue
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Patient Information -->
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Patient Information</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if (!empty($patient['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($patient['profile_image']) ?>" class="rounded-circle me-3" style="width: 64px; height: 64px; object-fit: cover;" alt="Patient">
                        <?php else: ?>
                            <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center text-secondary" style="width: 64px; height: 64px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($patient['name']) ?></h5>
                            <p class="text-muted mb-0"><?= htmlspecialchars($patient['email']) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Phone</div>
                        <div><?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'N/A' ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Address</div>
                        <div><?= !empty($patient['address']) ? htmlspecialchars($patient['address']) : 'N/A' ?></div>
                    </div>
                    
                    <a href="index.php?module=admin&action=edit_patient&id=<?= $patient['id'] ?>" class="btn btn-outline-primary btn-sm w-100 btn-report">
                        <i class="fas fa-user-edit"></i>View Patient Profile
                    </a>
                </div>
            </div>
            
            <!-- Issue Timeline -->
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Issue Timeline</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <div class="timeline-marker marker-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Issue Reported</h6>
                                <small class="text-muted"><?= date('F j, Y, g:i a', strtotime($issue['created_at'])) ?></small>
                            </div>
                        </li>
                        
                        <?php if ($issue['status'] !== 'pending'): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">In Progress</h6>
                                    <small class="text-muted">Issue review started</small>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($issue['admin_response'])): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Admin Response</h6>
                                    <small class="text-muted"><?= date('F j, Y', strtotime($issue['admin_response_at'] ?? $issue['updated_at'])) ?></small>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($issue['patient_response'])): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Patient Response</h6>
                                    <small class="text-muted"><?= date('F j, Y', strtotime($issue['patient_response_at'] ?? $issue['updated_at'])) ?></small>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($issue['status'] === 'resolved' || $issue['status'] === 'closed'): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Issue Resolved</h6>
                                    <small class="text-muted"><?= $issue['resolved_at'] ? date('F j, Y, g:i a', strtotime($issue['resolved_at'])) : 'Date not recorded' ?></small>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-pending"></div>
                                <div class="timeline-content text-muted">
                                    <h6 class="mb-0">Resolution Pending</h6>
                                    <small>Waiting for resolution</small>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Other Issues -->
            <div class="card shadow-sm border-0 issue-card">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Other Issues from this Patient</h5>
                </div>
                <div class="card-body p-0 issue-body">
                    <?php if (empty($other_issues)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No other issues reported</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush issues-list">
                            <?php foreach ($other_issues as $other_issue): ?>
                                <?php if ($other_issue['id'] != $issue['id']): ?>
                                    <a href="index.php?module=admin&action=view_patient_issue&id=<?= $other_issue['id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($other_issue['title']) ?></div>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    <?= date('M j, Y', strtotime($other_issue['created_at'])) ?>
                                                </small>
                                            </div>
                                            <span class="status-badge status-badge-sm <?php
                                                if ($other_issue['status'] === 'pending') echo 'status-pending';
                                                elseif ($other_issue['status'] === 'in_progress') echo 'status-in-progress';
                                                elseif ($other_issue['status'] === 'resolved') echo 'status-resolved';
                                                else echo 'status-closed';
                                            ?>">
                                                <i class="fas <?php
                                                    if ($other_issue['status'] === 'pending') echo 'fa-clock';
                                                    elseif ($other_issue['status'] === 'in_progress') echo 'fa-spinner';
                                                    elseif ($other_issue['status'] === 'resolved') echo 'fa-check-circle';
                                                    else echo 'fa-times-circle';
                                                ?>"></i>
                                                <?= ucfirst($other_issue['status']) ?>
                                            </span>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 2rem;
    list-style: none;
    margin: 0;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    left: -2rem;
    top: 0.25rem;
    border: 3px solid white;
    box-shadow: 0 0 0 3px rgba(209, 213, 219, 0.4);
}

.marker-success {
    background-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
}

.marker-info {
    background-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.marker-warning {
    background-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
}

.marker-pending {
    background-color: #e5e7eb;
    box-shadow: 0 0 0 3px rgba(229, 231, 235, 0.5);
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.69rem;
    top: 1rem;
    width: 2px;
    height: 100%;
    background-color: #e5e7eb;
}

.timeline-content {
    padding-bottom: 1rem;
}
</style>

<?php
// Include footer
require_once 'modules/admin/views/includes/footer.php';
?> 