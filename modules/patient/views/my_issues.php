<?php
/**
 * Patient Issues View
 * Palliative Care System
 */

// Set page title
$page_title = 'My Reported Issues';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>
<!-- Include custom CSS for issue reporting -->
<link href="assets/css/issue-reporting.css" rel="stylesheet">

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="mb-0">My Reported Issues</h2>
            <p class="text-muted">Track and manage your reported issues</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="index.php?module=patient&action=report_issue" class="btn btn-primary btn-report">
                <i class="fas fa-plus"></i>Report New Issue
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 issue-card">
                <div class="card-header bg-white issue-header">
                    <h5 class="mb-0">All Issues</h5>
                </div>
                <div class="card-body p-0 issue-body">
                    <?php if (empty($issues)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list text-muted fa-3x mb-3"></i>
                            <p class="text-muted">You haven't reported any issues yet</p>
                            <a href="index.php?module=patient&action=report_issue" class="btn btn-primary btn-report">
                                <i class="fas fa-plus"></i>Report an Issue
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 issues-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($issues as $issue): ?>
                                        <tr>
                                            <td>#<?= $issue['id'] ?></td>
                                            <td>
                                                <div class="issue-title"><?= htmlspecialchars($issue['title']) ?></div>
                                                <div class="issue-desc"><?= htmlspecialchars(substr($issue['description'], 0, 100)) . (strlen($issue['description']) > 100 ? '...' : '') ?></div>
                                            </td>
                                            <td>
                                                <?php
                                                switch ($issue['issue_type']) {
                                                    case 'medicine_delivery':
                                                        echo '<span class="badge bg-primary">Medicine Delivery</span>';
                                                        break;
                                                    case 'cab_service':
                                                        echo '<span class="badge bg-warning text-dark">Cab Service</span>';
                                                        break;
                                                    case 'appointment':
                                                        echo '<span class="badge bg-info">Appointment</span>';
                                                        break;
                                                    case 'pharmacy':
                                                        echo '<span class="badge bg-success">Pharmacy</span>';
                                                        break;
                                                    case 'doctor':
                                                        echo '<span class="badge bg-secondary">Doctor</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-dark">Other</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td><?= date('M j, Y', strtotime($issue['created_at'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($issue['updated_at'])) ?></td>
                                            <td>
                                                <a href="index.php?module=patient&action=view_issue&id=<?= $issue['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
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
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 