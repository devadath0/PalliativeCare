<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'Edit Medicine';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Pharmacy Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mx-3">
            <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3">
            <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_inventory">Inventory</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Medicine</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i> Edit Medicine</h5>
                    </div>
                    <div class="card-body">
                        <form action="index.php?module=service&action=pharmacy_edit_medicine&id=<?php echo htmlspecialchars($medicine['id']); ?>" method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Medicine Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($medicine['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="tablets" <?php echo $medicine['category'] === 'tablets' ? 'selected' : ''; ?>>Tablets</option>
                                    <option value="capsules" <?php echo $medicine['category'] === 'capsules' ? 'selected' : ''; ?>>Capsules</option>
                                    <option value="syrups" <?php echo $medicine['category'] === 'syrups' ? 'selected' : ''; ?>>Syrups</option>
                                    <option value="injections" <?php echo $medicine['category'] === 'injections' ? 'selected' : ''; ?>>Injections</option>
                                    <option value="topical" <?php echo $medicine['category'] === 'topical' ? 'selected' : ''; ?>>Topical</option>
                                    <option value="other" <?php echo $medicine['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit" name="unit" value="<?php echo htmlspecialchars($medicine['unit']); ?>" required>
                                <small class="text-muted">e.g., tablet, capsule, bottle, vial</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($medicine['price']); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo htmlspecialchars($medicine['stock_quantity']); ?>">
                                <small class="text-muted">Changes will be logged to stock history</small>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($medicine['description']); ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="manufacturer" class="form-label">Manufacturer</label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($medicine['manufacturer']); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reorder_level" class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" min="0" value="<?php echo htmlspecialchars($medicine['reorder_level']); ?>">
                                <small class="text-muted">Minimum stock level before reordering</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="batch_number" class="form-label">Batch Number</label>
                                <input type="text" class="form-control" id="batch_number" name="batch_number" value="<?php echo htmlspecialchars($medicine['batch_number']); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($medicine['expiry_date']); ?>">
                            </div>
                            
                            <div class="col-12">
                                <label for="storage_instructions" class="form-label">Storage Instructions</label>
                                <textarea class="form-control" id="storage_instructions" name="storage_instructions" rows="2"><?php echo htmlspecialchars($medicine['storage_instructions']); ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo $medicine['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="out_of_stock" <?php echo $medicine['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="discontinued" <?php echo $medicine['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="requires_prescription" name="requires_prescription" <?php echo $medicine['requires_prescription'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="requires_prescription">
                                        Requires Prescription
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="index.php?module=service&action=pharmacy_inventory" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Back to Inventory
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Update Medicine
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 