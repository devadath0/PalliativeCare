<?php
/**
 * Admin Patient Issues View
 * Palliative Care System
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Set page title and current page for navigation
$page_title = 'Patient Reported Issues';
$current_page = 'patient_issues';

// Include header
require_once 'modules/admin/views/includes/header.php';
?>
<!-- Include custom CSS for issue reporting -->
<link href="assets/css/issue-reporting.css" rel="stylesheet">

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Patient Reported Issues</h1>
            <p class="text-muted">Manage and respond to issues reported by patients</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="index.php?module=admin&action=patient_issues&filter=pending" class="btn btn-<?= $filter === 'pending' ? 'warning' : 'outline-warning' ?> btn-report">
                    <i class="fas fa-clock"></i> Pending
                    <?php if (isset($counts['pending']) && $counts['pending'] > 0): ?>
                        <span class="badge rounded-pill bg-danger"><?= $counts['pending'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="index.php?module=admin&action=patient_issues&filter=in_progress" class="btn btn-<?= $filter === 'in_progress' ? 'info' : 'outline-info' ?> btn-report">
                    <i class="fas fa-spinner"></i> In Progress
                </a>
                <a href="index.php?module=admin&action=patient_issues&filter=resolved" class="btn btn-<?= $filter === 'resolved' ? 'success' : 'outline-success' ?> btn-report">
                    <i class="fas fa-check-circle"></i> Resolved
                </a>
                <a href="index.php?module=admin&action=patient_issues" class="btn btn-<?= !$filter ? 'primary' : 'outline-primary' ?> btn-report">
                    <i class="fas fa-list"></i> All
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 issue-card">
        <div class="card-body p-0 issue-body">
            <?php if (empty($issues)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check text-muted fa-3x mb-3"></i>
                    <p class="text-muted">No issues found<?= $filter ? ' with status: ' . ucfirst($filter) : '' ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 issues-table">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 180px;">Date</th>
                                <th style="width: 200px;">Patient</th>
                                <th style="width: 140px;">Type</th>
                                <th>Issue</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issues as $issue): ?>
                                <tr>
                                    <td>#<?= $issue['id'] ?></td>
                                    <td><?= date('M j, Y, g:i a', strtotime($issue['created_at'])) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($issue['patient_profile_image'])): ?>
                                                <img src="<?= htmlspecialchars($issue['patient_profile_image']) ?>" class="rounded-circle me-2" width="32" height="32" alt="Patient">
                                            <?php else: ?>
                                                <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center text-secondary" style="width:32px;height:32px">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($issue['patient_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($issue['patient_email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        switch ($issue['issue_type']) {
                                            case 'medicine_delivery':
                                                echo '<span class="issue-type medicine-delivery"><i class="fas fa-pills"></i> Medicine</span>';
                                                break;
                                            case 'cab_service':
                                                echo '<span class="issue-type cab-service"><i class="fas fa-taxi"></i> Cab</span>';
                                                break;
                                            case 'appointment':
                                                echo '<span class="issue-type appointment"><i class="fas fa-calendar-check"></i> Appointment</span>';
                                                break;
                                            case 'pharmacy':
                                                echo '<span class="issue-type pharmacy"><i class="fas fa-mortar-pestle"></i> Pharmacy</span>';
                                                break;
                                            case 'doctor':
                                                echo '<span class="issue-type doctor"><i class="fas fa-user-md"></i> Doctor</span>';
                                                break;
                                            default:
                                                echo '<span class="issue-type other"><i class="fas fa-question-circle"></i> Other</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($issue['title']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">
                                            <?= htmlspecialchars(substr($issue['description'], 0, 100)) . (strlen($issue['description']) > 100 ? '...' : '') ?>
                                        </div>
                                    </td>
                                    <td>
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
                                        
                                        <?php if ($issue['status'] === 'resolved' && $issue['resolved_at']): ?>
                                            <div class="small text-muted mt-1">
                                                <?= date('M j, Y', strtotime($issue['resolved_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="index.php?module=admin&action=view_patient_issue&id=<?= $issue['id'] ?>" class="btn btn-sm btn-outline-primary me-1 btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($issue['status'] === 'pending'): ?>
                                            <a href="index.php?module=admin&action=update_patient_issue_status&id=<?= $issue['id'] ?>&status=in_progress" class="btn btn-sm btn-outline-info btn-action">
                                                <i class="fas fa-spinner"></i>
                                            </a>
                                        <?php endif; ?>
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

<?php
// Include footer
require_once 'modules/admin/views/includes/footer.php';
?> 