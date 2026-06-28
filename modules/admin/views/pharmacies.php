<?php 
// Set current page for navbar highlighting
$current_page = 'pharmacies';
require_once 'includes/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <!-- Removed sidebar include that was causing the error -->
        
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?module=admin&action=add_pharmacy" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New Pharmacy
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>License Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pharmacies)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No pharmacies found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <tr>
                                    <td><?php echo $pharmacy['id']; ?></td>
                                    <td><?php echo htmlspecialchars($pharmacy['name']); ?></td>
                                    <td><?php echo htmlspecialchars($pharmacy['email']); ?></td>
                                    <td><?php echo htmlspecialchars($pharmacy['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($pharmacy['license_number']); ?></td>
                                    <td>
                                        <?php if ($pharmacy['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($pharmacy['status'] == 'inactive'): ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?module=admin&action=edit_pharmacy&id=<?php echo $pharmacy['id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?module=admin&action=reset_pharmacy_password&id=<?php echo $pharmacy['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               onclick="return confirm('Are you sure you want to reset the password for this pharmacy?');">
                                                <i class="fas fa-key"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 