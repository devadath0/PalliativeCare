<?php
/**
 * Patient Issue Reporting View
 * Palliative Care System
 */

// Set page title
$page_title = "Report an Issue";

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>
<!-- Include custom CSS for issue reporting -->
<link href="assets/css/issue-reporting.css" rel="stylesheet">

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Report an Issue</h2>
            <p class="text-muted">Use this form to report any problems with services</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Report a New Issue</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <form action="index.php?module=patient&action=submit_issue" method="post" class="issue-form">
                        <div class="mb-3">
                            <label for="issue_type" class="form-label">Issue Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="issue_type" name="issue_type" required>
                                <option value="">Select issue type</option>
                                <option value="medicine_delivery">Medicine Delivery</option>
                                <option value="cab_service">Cab Service</option>
                                <option value="appointment">Appointment</option>
                                <option value="pharmacy">Pharmacy Service</option>
                                <option value="doctor">Doctor Service</option>
                                <option value="other">Other Issue</option>
                            </select>
                        </div>

                        <!-- Dynamic reference selection based on issue type -->
                        <div class="mb-3" id="reference_container" style="display: none;">
                            <label for="reference_id" class="form-label">Related Service</label>
                            <select class="form-select" id="reference_id" name="reference_id">
                                <option value="">Select related service</option>
                                <!-- Will be populated dynamically via JavaScript -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="issue_title" name="title" required placeholder="Brief description of the issue">
                        </div>

                        <div class="mb-3">
                            <label for="issue_description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="issue_description" name="description" rows="5" required placeholder="Please provide details about the issue you're experiencing"></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-secondary btn-report">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-report">
                                <i class="fas fa-paper-plane"></i> Submit Issue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 issue-card mb-4">
                <div class="card-header issue-header">
                    <h5 class="mb-0">My Recent Issues</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <?php if (empty($recent_issues)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list text-muted fa-2x mb-3"></i>
                            <p class="text-muted">No issues reported yet</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush issues-list mt-2">
                            <?php foreach ($recent_issues as $issue): ?>
                                <a href="index.php?module=patient&action=view_issue&id=<?= $issue['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($issue['title']) ?></h6>
                                        <span class="status-badge status-badge-sm <?php
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
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('M j, Y', strtotime($issue['created_at'])) ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <a href="index.php?module=patient&action=my_issues" class="btn btn-outline-primary btn-sm w-100 btn-report">View All Issues</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 issue-card help-card">
                <div class="card-header issue-header">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body p-4 issue-body">
                    <p class="text-muted">For urgent matters, please contact us directly:</p>
                    <div class="d-grid gap-2">
                        <a href="tel:+1234567890" class="btn btn-outline-primary btn-report">
                            <i class="fas fa-phone-alt"></i> Call Support
                        </a>
                        <a href="mailto:support@palliativecare.com" class="btn btn-outline-secondary btn-report">
                            <i class="fas fa-envelope"></i> Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const issueTypeSelect = document.getElementById('issue_type');
    const referenceContainer = document.getElementById('reference_container');
    const referenceSelect = document.getElementById('reference_id');
    
    // Show/hide and populate the reference dropdown based on selected issue type
    issueTypeSelect.addEventListener('change', function() {
        const issueType = this.value;
        
        // Clear the reference dropdown
        referenceSelect.innerHTML = '<option value="">Select related service</option>';
        
        if (!issueType || issueType === 'other') {
            referenceContainer.style.display = 'none';
            return;
        }
        
        // Show the reference container and fetch appropriate options
        referenceContainer.style.display = 'block';
        
        // Fetch reference options based on the issue type
        fetch(`index.php?module=patient&action=get_reference_options&type=${issueType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.options.length > 0) {
                    data.options.forEach(option => {
                        const optEl = document.createElement('option');
                        optEl.value = option.id;
                        optEl.textContent = option.name;
                        referenceSelect.appendChild(optEl);
                    });
                } else {
                    referenceContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching reference options:', error);
                referenceContainer.style.display = 'none';
            });
    });
});
</script>

<?php 
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 