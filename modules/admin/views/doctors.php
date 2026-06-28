<?php
/**
 * Admin - Doctor Management
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Set page title and current page for navigation
$page_title = 'Doctor Management';
$current_page = 'doctors';

// Include header
require_once 'modules/admin/views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Doctor Management</h2>
            <p class="text-muted">View and manage registered doctors</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=admin&action=add_doctor" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Doctor
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Doctor List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Specialization</th>
                            <th>License Number</th>
                            <th>Experience</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doctor['id']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($doctor['license_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($doctor['experience_years'] ?? '0'); ?> years</td>
                            <td>$<?php echo number_format($doctor['consultation_fee'] ?? 0, 2); ?></td>
                            <td>
                                <?php if ($doctor['availability_status'] === 'available'): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php elseif ($doctor['availability_status'] === 'unavailable'): ?>
                                    <span class="badge bg-danger">Unavailable</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">On Leave</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <a href="index.php?module=admin&action=edit_doctor&id=<?php echo $doctor['id']; ?>" 
                                   class="btn btn-xs btn-warning me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button" class="btn btn-xs btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal<?php echo $doctor['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal<?php echo $doctor['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Doctor</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete Dr. <?php echo htmlspecialchars($doctor['name']); ?>? This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <a href="index.php?module=admin&action=delete_doctor&id=<?php echo $doctor['id']; ?>" 
                                                   class="btn btn-danger">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'modules/admin/views/includes/footer.php';
?>
