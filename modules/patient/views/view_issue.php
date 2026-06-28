<?php
/**
 * View Single Issue Details
 * Palliative Care System
 */

// Set page title
$page_title = 'View Issue #' . $issue['id'];

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>
<!-- Include custom CSS for issue reporting -->
<link href="assets/css/issue-reporting.css" rel="stylesheet">

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?module=patient&action=dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php?module=patient&action=my_issues">My Issues</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Issue #<?= $issue['id'] ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0">Issue Details</h4>
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
                    <h5 class="card-title"><?= htmlspecialchars($issue['title']) ?></h5>
                    
                    <div class="mb-4">
                        <div class="row text-muted small mb-3">
                            <div class="col-md-6">
                                <i class="far fa-calendar-alt me-1"></i> Created: <?= date('F j, Y, g:i a', strtotime($issue['created_at'])) ?>
                            </div>
                            <div class="col-md-6">
                                <i class="fas fa-tag me-1"></i> Type: <?php
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
                        </div>
                        
                        <?php if (!empty($reference_details)): ?>
                            <div class="alert alert-light mb-3">
                                <small class="text-muted d-block mb-1">Related Service:</small>
                                <div class="d-flex align-items-center">
                                    <?php if ($issue['issue_type'] === 'medicine_delivery'): ?>
                                        <i class="fas fa-pills text-primary me-2"></i>
                                        <span>Order #<?= $reference_details['order_number'] ?> - <?= $reference_details['pharmacy_name'] ?? 'Unknown Pharmacy' ?></span>
                                    <?php elseif ($issue['issue_type'] === 'cab_service'): ?>
                                        <i class="fas fa-taxi text-warning me-2"></i>
                                        <span>Booking #<?= $reference_details['id'] ?> - <?= date('M j, Y', strtotime($reference_details['pickup_datetime'])) ?></span>
                                    <?php elseif ($issue['issue_type'] === 'appointment'): ?>
                                        <i class="fas fa-calendar-check text-info me-2"></i>
                                        <span>Appointment with <?= $reference_details['doctor_name'] ?> - <?= date('M j, Y', strtotime($reference_details['appointment_date'])) ?></span>
                                    <?php else: ?>
                                        <i class="fas fa-info-circle text-secondary me-2"></i>
                                        <span>Reference #<?= $issue['reference_id'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card issue-card mb-4">
                        <div class="card-header bg-light issue-header">
                            <h6 class="mb-0">Description</h6>
                        </div>
                        <div class="card-body issue-body">
                            <p class="card-text"><?= nl2br(htmlspecialchars($issue['description'])) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($issue['admin_response'])): ?>
                        <div class="card border-success mb-4 issue-card">
                            <div class="card-header bg-success bg-opacity-10 issue-header">
                                <h6 class="mb-0 text-success">Response from Management</h6>
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

                        <!-- Patient response form -->
                        <?php if ($issue['status'] !== 'resolved' && $issue['status'] !== 'closed'): ?>
                            <div class="card border-primary mb-4 issue-card">
                                <div class="card-header bg-primary bg-opacity-10 issue-header">
                                    <h6 class="mb-0 text-primary">Your Response</h6>
                                </div>
                                <div class="card-body issue-body">
                                    <form action="index.php?module=patient&action=reply_to_issue" method="post" class="issue-form">
                                        <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label for="patient_response" class="form-label">Reply to Management</label>
                                            <textarea class="form-control" id="patient_response" name="patient_response" rows="4" required placeholder="Type your response to management here..."><?= htmlspecialchars($issue['patient_response'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" id="is_resolved" name="is_resolved">
                                            <label class="form-check-label" for="is_resolved">
                                                Consider this issue resolved after sending response
                                            </label>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary btn-report">
                                                <i class="fas fa-paper-plane"></i> Send Response
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Show previous responses if any -->
                        <?php if (!empty($issue['patient_response'])): ?>
                            <div class="card border-info mb-4 issue-card">
                                <div class="card-header bg-info bg-opacity-10 issue-header">
                                    <h6 class="mb-0 text-info">Your Previous Response</h6>
                                </div>
                                <div class="card-body issue-body">
                                    <p class="card-text"><?= nl2br(htmlspecialchars($issue['patient_response'])) ?></p>
                                    <div class="text-muted small mt-2">
                                        <i class="far fa-clock me-1"></i> Sent on <?= date('F j, Y, g:i a', strtotime($issue['patient_response_at'] ?? $issue['updated_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 justify-content-between">
                        <a href="index.php?module=patient&action=my_issues" class="btn btn-outline-secondary btn-report">
                            <i class="fas fa-arrow-left"></i>Back to Issues
                        </a>
                        
                        <?php if ($issue['status'] !== 'resolved' && $issue['status'] !== 'closed'): ?>
                            <button type="button" class="btn btn-success btn-report" data-bs-toggle="modal" data-bs-target="#closeIssueModal">
                                <i class="fas fa-check-circle"></i>Mark as Resolved
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Status Timeline</h5>
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
                                    <small class="text-muted">Issue is being reviewed by management</small>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($issue['admin_response'])): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Management Response</h6>
                                    <small class="text-muted"><?= date('F j, Y', strtotime($issue['admin_response_at'] ?? $issue['updated_at'])) ?></small>
                                </div>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($issue['patient_response'])): ?>
                            <li class="timeline-item">
                                <div class="timeline-marker marker-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Your Response</h6>
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
            
            <div class="card shadow-sm border-0 issue-card help-card">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Need More Help?</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <p class="text-muted small">If your issue requires immediate attention, please contact us directly:</p>
                    <div class="d-grid gap-2">
                        <a href="tel:+1234567890" class="btn btn-outline-primary btn-report">
                            <i class="fas fa-phone-alt"></i>Call Support
                        </a>
                        <a href="mailto:support@palliativecare.com" class="btn btn-outline-secondary btn-report">
                            <i class="fas fa-envelope"></i>Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for closing issue -->
<div class="modal fade" id="closeIssueModal" tabindex="-1" aria-labelledby="closeIssueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closeIssueModalLabel">Mark Issue as Resolved</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark this issue as resolved?</p>
                <p class="text-muted small">This will notify our management team that you consider your issue resolved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="index.php?module=patient&action=resolve_issue" method="post">
                    <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                    <button type="submit" class="btn btn-success">Mark as Resolved</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    list-style: none;
    margin: 0;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    left: -1.5rem;
    top: 0.25rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.18rem;
    top: 1rem;
    width: 2px;
    height: 100%;
    background-color: #e9ecef;
}

.timeline-content {
    padding-bottom: 1rem;
}

/* Marker colors */
.marker-success {
    background-color: #10b981;
}

.marker-info {
    background-color: #3b82f6;
}

.marker-warning {
    background-color: #f59e0b;
}

.marker-pending {
    background-color: #e5e7eb;
}

/* Background opacity classes for Bootstrap 5 */
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
</style>

<?php 
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 