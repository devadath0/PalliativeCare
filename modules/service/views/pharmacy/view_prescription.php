<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'View Prescription';
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
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-container {
                padding: 20px;
                max-width: 100%;
            }
        }
        .prescription-card {
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .prescription-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        .prescription-stamp {
            font-size: 14px;
            color: #6c757d;
            border: 1px dashed #6c757d;
            border-radius: 5px;
            padding: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 no-print">
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
                        <a class="nav-link" href="index.php?module=service&action=pharmacy_inventory">
                           <i class="fas fa-pills"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?module=service&action=pharmacy_orders">
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
        <div class="alert alert-success alert-dismissible fade show mx-3 no-print">
            <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-3 no-print">
            <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="container py-4 print-container">
        <div class="row no-print mb-3">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php?module=service&action=pharmacy_orders">Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Prescription</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">
                        <i class="fas fa-prescription me-2"></i> Prescription #<?php echo htmlspecialchars($prescription['id']); ?>
                    </h3>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Print
                        </button>
                        <a href="<?php echo $_SERVER['HTTP_REFERER'] ?? 'index.php?module=service&action=pharmacy_orders'; ?>" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card prescription-card mb-4">
            <div class="card-body">
                <div class="row prescription-header mb-4">
                    <div class="col-md-6">
                        <h4><?php echo htmlspecialchars($prescription['doctor_name']); ?></h4>
                        <p class="mb-0"><i class="fas fa-user-md me-2"></i> Prescribing Doctor</p>
                        <p><small class="text-muted">Doctor ID: <?php echo htmlspecialchars($prescription['doctor_id']); ?></small></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0"><strong>Prescription Date:</strong> <?php echo date('F j, Y', strtotime($prescription['created_at'])); ?></p>
                        <p><small class="text-muted">Prescription ID: <?php echo htmlspecialchars($prescription['id']); ?></small></p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Patient Information</h5>
                        <div class="card card-body bg-light mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
                                    <p class="mb-1"><strong>ID:</strong> <?php echo htmlspecialchars($patient['id']); ?></p>
                                    <p class="mb-1"><strong>Gender:</strong> <?php echo ucfirst(htmlspecialchars($patient['gender'] ?? 'Not specified')); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>DOB:</strong> <?php echo $patient['dob'] ? date('F j, Y', strtotime($patient['dob'])) : 'Not specified'; ?></p>
                                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone'] ?? 'Not specified'); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email'] ?? 'Not specified'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Prescribed Medications</h5>
                            <div class="prescription-stamp">
                                <i class="fas fa-check-circle me-1"></i> Valid Prescription
                            </div>
                        </div>
                        
                        <?php if (!empty($prescription_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Medicine</th>
                                            <th>Dosage</th>
                                            <th>Frequency</th>
                                            <th>Duration</th>
                                            <th>Instructions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prescription_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($item['medicine'] ?? $item['medicine_name'] ?? ''); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['dosage'] ?? ''); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['frequency'] ?? ''); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['duration'] ?? ''); ?>
                                                </td>
                                                <td>
                                                    <?php echo nl2br(htmlspecialchars($item['instructions'] ?? '')); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif (!empty($prescription['diagnosis'])): ?>
                            <div class="card card-body">
                                <h6>Prescription Details:</h6>
                                <p><?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No medication details available for this prescription.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($prescription['notes'])): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Doctor's Notes</h5>
                            <div class="card card-body">
                                <?php echo nl2br(htmlspecialchars($prescription['notes'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="mb-4 mt-2 border-top pt-2">
                            <small class="text-muted">Patient Signature</small>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="mb-4 mt-2 border-top pt-2">
                            <small class="text-muted">Doctor's Signature</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row no-print">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Pharmacy Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Process Prescription</h6>
                                <p>Check your inventory for the prescribed medications and prepare them for the patient.</p>
                                <a href="index.php?module=service&action=pharmacy_inventory" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i> Check Inventory
                                </a>
                            </div>
                            <div class="col-md-6">
                                <h6>Related Orders</h6>
                                <p>View orders related to this prescription.</p>
                                <a href="index.php?module=service&action=pharmacy_orders" class="btn btn-info">
                                    <i class="fas fa-shopping-cart me-2"></i> View Orders
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 