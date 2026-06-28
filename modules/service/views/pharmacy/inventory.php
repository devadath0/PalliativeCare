<?php
/**
 * Pharmacy Inventory View
 * Palliative Care System
 */

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title if not set
if (!isset($page_title)) {
    $page_title = 'Medicine Inventory';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $page_title; ?> - Pharmacy Portal</title>
    <!-- Bootstrap CSS -->
    <link href="http://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo str_replace('https://', 'http://', SITE_URL); ?>assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <link href="http://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?module=service&action=pharmacy_dashboard">
                <i class="fas fa-prescription-bottle-alt me-2"></i> Pharmacy Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pharmacyNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="pharmacyNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?module=service&action=pharmacy_inventory">
                            <i class="fas fa-pills"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_orders">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_stock_history">
                            <i class="fas fa-history"></i> Stock History
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?module=service&action=profile">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php?module=auth&action=logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3"><?php echo $page_title; ?></h1>
            <a href="index.php?module=service&action=pharmacy_add_medicine" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New Medicine
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <?php if (!empty($medicines)): ?>
                    <div class="table-responsive">
                        <table id="medicinesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medicines as $medicine): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($medicine['id']); ?></td>
                                        <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                        <td><?php echo htmlspecialchars($medicine['category'] ?? 'Uncategorized'); ?></td>
                                        <td>$<?php echo number_format($medicine['price'], 2); ?></td>
                                        <td>
                                            <?php if ($medicine['stock_quantity'] <= 10): ?>
                                                <span class="text-danger fw-bold"><?php echo $medicine['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <?php echo $medicine['stock_quantity']; ?>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($medicine['unit'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($medicine['status'] == 'active') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($medicine['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="index.php?module=service&action=pharmacy_edit_medicine&id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-success add-stock-btn" data-medicine-id="<?php echo $medicine['id']; ?>" data-medicine-name="<?php echo htmlspecialchars($medicine['name']); ?>">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">No medicines found in inventory. <a href="index.php?module=service&action=pharmacy_add_medicine" class="alert-link">Add your first medicine</a>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addStockForm" action="index.php?module=service&action=pharmacy_update_stock" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="medicine_id" id="medicineId">
                        <div class="mb-3">
                            <label for="medicineName" class="form-label">Medicine</label>
                            <input type="text" class="form-control" id="medicineName" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity to Add</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <select class="form-select" id="source" name="source" required>
                                <option value="purchase">Purchase</option>
                                <option value="return">Return</option>
                                <option value="adjustment">Inventory Adjustment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="http://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="http://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="http://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#medicinesTable').DataTable({
                "order": [[1, "asc"]],
                "pageLength": 25
            });
            
            // Handle Add Stock modal
            $('.add-stock-btn').click(function() {
                const medicineId = $(this).data('medicine-id');
                const medicineName = $(this).data('medicine-name');
                
                $('#medicineId').val(medicineId);
                $('#medicineName').val(medicineName);
                
                $('#addStockModal').modal('show');
            });
            
            // Handle Add Stock form submission
            $('#addStockForm').submit(function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    type: 'POST',
                    url: 'index.php?module=service&action=pharmacy_update_stock',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close modal and refresh page
                            $('#addStockModal').modal('hide');
                            location.reload();
                        } else {
                            alert(response.message || 'Error updating stock');
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating stock');
                    }
                });
            });
        });
    </script>
</body>
</html> 