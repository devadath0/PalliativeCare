<?php
/**
 * Admin Dashboard
 * Palliative Care System - Admin Module
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Set page title and current page for navigation
$page_title = 'Admin Dashboard';
$current_page = 'dashboard';

// Include header
require_once 'modules/admin/views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">System Overview and Management</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Patients</h5>
                    <h2 class="card-text"><?php echo $counts['patients']; ?></h2>
                    <a href="index.php?module=admin&action=patients" class="text-white">View Details →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Doctors</h5>
                    <h2 class="card-text"><?php echo $counts['doctors']; ?></h2>
                    <a href="index.php?module=admin&action=doctors" class="text-white">View Details →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Service Providers</h5>
                    <h2 class="card-text"><?php echo $counts['service_providers']; ?></h2>
                    <a href="index.php?module=admin&action=services" class="text-white">View Details →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Services</h5>
                    <h2 class="card-text"><?php echo $counts['active_services']; ?></h2>
                    <a href="index.php?module=admin&action=services" class="text-white">View Details →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Options Section (for super admins) -->
    <?php if (isset($_SESSION['admin_level']) && $_SESSION['admin_level'] === 'super'): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Advanced Options</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-key"></i> Admin Token Management
                                    </h5>
                                    <p class="card-text">Generate and manage registration tokens for new admin accounts.</p>
                                    <a href="index.php?module=admin&action=admin_tokens" class="btn btn-primary">
                                        Manage Tokens
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Additional advanced options can be added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td>
                                        <?php if ($activity['type'] === 'patient'): ?>
                                            <span class="badge bg-primary">Patient</span>
                                        <?php elseif ($activity['type'] === 'doctor'): ?>
                                            <span class="badge bg-success">Doctor</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'modules/admin/views/includes/footer.php';
?>
