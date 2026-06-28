<?php
/**
 * Palliative Care System - Doctor Module
 * Doctor Profile View
 */

// Set page title
$page_title = isset($page_title) ? $page_title : 'Doctor Profile';

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
                                    <i class="fas fa-calendar-check"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=doctor&action=prescriptions">
                                    <i class="fas fa-prescription"></i> Prescriptions
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
            <h2>My Profile</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=doctor&action=edit_profile" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="index.php?module=doctor&action=dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (!isset($doctor) || empty($doctor)): ?>
        <div class="alert alert-danger">Doctor profile not found.</div>
    <?php else: ?>
        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <?php if (!empty($doctor['profile_image'])): ?>
                                <img src="<?= htmlspecialchars($doctor['profile_image'] ?? '') ?>" 
                                     alt="Doctor Profile" class="rounded-circle img-fluid" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="text-center p-3 bg-light rounded-circle" style="width: 150px; height: 150px;">
                                    <i class="fas fa-user-md fa-5x text-secondary mt-3"></i>
                                </div>
                            <?php endif; ?>
                            <h4 class="mt-3"><?php echo htmlspecialchars($doctor['name'] ?? 'Doctor'); ?></h4>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($doctor['specialization'] ?? 'Specialization not specified'); ?>
                            </p>
                            <?php if (!empty($hospital_name)): ?>
                            <p class="text-muted">
                                <i class="fas fa-hospital"></i> <?php echo htmlspecialchars($hospital_name); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email'] ?? 'Not provided'); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone'] ?? 'Not provided'); ?></p>
                            <p><strong>License Number:</strong> <?php echo htmlspecialchars($doctor['license_number'] ?? 'Not provided'); ?></p>
                            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($doctor['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h5><?php echo $stats['total_appointments'] ?? 0; ?></h5>
                                <p class="text-muted">Total Appointments</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h5><?php echo $stats['total_patients'] ?? 0; ?></h5>
                                <p class="text-muted">Total Patients</p>
                            </div>
                            <div class="col-6">
                                <h5><?php echo $stats['total_prescriptions'] ?? 0; ?></h5>
                                <p class="text-muted">Prescriptions</p>
                            </div>
                            <div class="col-6">
                                <h5><?php echo $stats['pending_appointments'] ?? 0; ?></h5>
                                <p class="text-muted">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="index.php?module=doctor&action=update_profile">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($doctor['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($doctor['license_number'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="qualification" class="form-label">Qualification</label>
                                    <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo htmlspecialchars($doctor['qualification'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3">Change Password</h5>
                            <p class="text-muted mb-3">Leave blank if you don't want to change your password</p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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