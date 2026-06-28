<?php
/**
 * Doctor Patients
 * Palliative Care System - Doctor Module
 */

// Set page title
$page_title = isset($page_title) ? $page_title : 'Manage Patients';

// Include header content directly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Palliative Care System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .nav-link {
            color: #333;
        }
        .nav-link:hover {
            color: #007bff;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Palliative Care</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?module=<?php echo $_SESSION['user_type']; ?>&action=dashboard">
                                Dashboard
                            </a>
                        </li>
                        <?php if ($_SESSION['user_type'] === 'doctor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=doctor&action=appointments">
                                    Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=doctor&action=prescriptions">
                                    Prescriptions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=doctor&action=patients">
                                    <i class="fas fa-users"></i> Patients
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="index.php?module=<?php echo $_SESSION['user_type']; ?>&action=profile">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type'] === 'error' ? 'danger' : $_SESSION['flash']['type']; ?> alert-dismissible fade show m-3">
            <?php 
            echo htmlspecialchars($_SESSION['flash']['message']);
            unset($_SESSION['flash']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Manage Patients</h2>
            <p class="lead">View and manage your patients</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=doctor&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>All Patients</h5>
        </div>
        <div class="card-body">
            <?php if (empty($patients)): ?>
                <div class="alert alert-info">You have no patients assigned to you yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Age</th>
                                <th>Appointments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($patient['phone']); ?><br>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($patient['date_of_birth'])) {
                                            $dob = new DateTime($patient['date_of_birth']);
                                            $now = new DateTime();
                                            $age = $now->diff($dob)->y;
                                            echo $age . ' years';
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $patient['appointment_count']; ?> appointments
                                    </td>
                                    <td>
                                        <a href="index.php?module=doctor&action=view_patient&id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-user"></i> View Details
                                        </a>
                                        <a href="index.php?module=doctor&action=create_prescription&patient_id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-prescription"></i> Create Prescription
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

<!-- Footer -->
<footer class="footer bg-light mt-5 py-3">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    <i class="fas fa-heartbeat text-primary"></i> Palliative Care System &copy; <?php echo date('Y'); ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="mx-2">|</span>
                    <a href="logout.php" class="text-decoration-none">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.classList.add('fade');
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Enable dropdowns
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    dropdownElementList.forEach(function(dropdownToggleEl) {
        new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>
</body>
</html>