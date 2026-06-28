<?php
/**
 * Doctor Dashboard
 * Palliative Care System - Doctor Module
 */

// Set page title
$page_title = 'Doctor Dashboard';

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
                        <?php elseif ($_SESSION['user_type'] === 'patient'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=appointments">
                                    Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=prescriptions">
                                    Prescriptions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=order_medicine">
                                    <i class="fas fa-pills"></i> Order Medicine
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?module=patient&action=book_cab">
                                    <i class="fas fa-taxi"></i> Book Cab
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
            <h2>Doctor Dashboard</h2>
            <p class="lead">Welcome, Dr. <?php echo $doctor['doctor_name']; ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=doctor&action=profile" class="btn btn-secondary">
                <i class="fas fa-user-md"></i> My Profile
            </a>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Today's Appointments</h5>
                    <h2 class="display-4"><?php echo $stats['today_appointments']; ?></h2>
                    <p class="card-text">Appointments scheduled for today</p>
                    <a href="index.php?module=doctor&action=appointments&filter=today" class="btn btn-light">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Appointments</h5>
                    <h2 class="display-4"><?php echo $stats['pending_appointments']; ?></h2>
                    <p class="card-text">Appointments awaiting confirmation</p>
                    <a href="index.php?module=doctor&action=appointments&filter=pending" class="btn btn-light">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Prescriptions</h5>
                    <h2 class="display-4"><?php echo $prescription_stats['active_prescriptions']; ?></h2>
                    <p class="card-text">Currently active prescriptions</p>
                    <a href="index.php?module=doctor&action=prescriptions" class="btn btn-light">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Appointments</h5>
                    <h2 class="display-4"><?php echo $stats['total_appointments']; ?></h2>
                    <p class="card-text">All-time appointments</p>
                    <a href="index.php?module=doctor&action=appointments" class="btn btn-light">View All</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="btn-group">
                        <a href="index.php?module=doctor&action=appointments" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Manage Appointments
                        </a>
                        <a href="index.php?module=doctor&action=create_prescription" class="btn btn-success">
                            <i class="fas fa-prescription"></i> Create Prescription
                        </a>
                        <a href="index.php?module=doctor&action=patients" class="btn btn-info">
                            <i class="fas fa-users"></i> View Patients
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Today's Appointments -->
        <div class="col-md-8">
            <div class="card" id="today-appointments">
                <div class="card-header">
                    <h5>Today's Appointments (<?php echo date('F d, Y'); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($today_appointments)): ?>
                        <div class="alert alert-info">You have no appointments scheduled for today.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($today_appointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></td>
                                            <td>
                                                <?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?><br>
                                                <small class="text-muted">Phone: <?php echo $appointment['patient_phone']; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($appointment['status'] == 'scheduled'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($appointment['status'] == 'confirmed'): ?>
                                                    <span class="badge bg-success">Confirmed</span>
                                                <?php elseif ($appointment['status'] == 'completed'): ?>
                                                    <span class="badge bg-primary">Completed</span>
                                                <?php elseif ($appointment['status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger">Cancelled</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Unknown</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    
                                                    <?php if ($appointment['status'] == 'scheduled'): ?>
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="this.form.submit(); return false;">
                                                            <i class="fas fa-check"></i> Confirm
                                                        </button>
                                                    <?php elseif ($appointment['status'] == 'confirmed'): ?>
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-primary" onclick="this.form.submit(); return false;">
                                                            <i class="fas fa-check-circle"></i> Complete
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                                
                                                <?php if ($appointment['status'] == 'scheduled'): ?>
                                                <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="if(confirm('Are you sure you want to reject this appointment? This action cannot be undone.')) { this.form.submit(); } return false;">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                
                                                <!-- Reschedule button for scheduled or confirmed appointments -->
                                                <?php if (in_array($appointment['status'], ['scheduled', 'confirmed'])): ?>
                                                <a href="index.php?module=doctor&action=reschedule_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-calendar-alt"></i> Reschedule
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="index.php?module=doctor&action=view_patient&id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-user"></i> Patient Info
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
        
        <!-- Pending Appointments -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Pending Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_appointments)): ?>
                        <div class="alert alert-info">You have no pending appointments.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($pending_appointments as $appointment): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></strong>
                                            <span class="ms-2"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></span>
                                            <div>
                                                <?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="this.form.submit(); return false;">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            </form>
                                            <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline mt-1">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="if(confirm('Are you sure you want to reject this appointment? This action cannot be undone.')) { this.form.submit(); } return false;">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                            <!-- Debug info -->
                                            <div class="small text-muted mt-1">
                                                ID: <?php echo $appointment['id']; ?>
                                            </div>
                                            
                                            <!-- Reschedule button -->
                                            <a href="index.php?module=doctor&action=reschedule_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning mt-1">
                                                <i class="fas fa-calendar-alt"></i> Reschedule
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-3">
                            <a href="index.php?module=doctor&action=appointments" class="btn btn-outline-primary btn-sm">View All Appointments</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Prescriptions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Recent Prescriptions</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_prescriptions)): ?>
                        <div class="alert alert-info">You have not created any prescriptions yet.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($recent_prescriptions as $prescription): ?>
                                <li class="list-group-item">
                                    <div>
                                        <strong><?php echo $prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']; ?></strong>
                                        <div>
                                            <small class="text-muted">
                                                <?php echo $prescription['medication']; ?> - 
                                                <?php echo $prescription['dosage']; ?> - 
                                                <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-3">
                            <a href="index.php?module=doctor&action=prescriptions" class="btn btn-outline-primary btn-sm">View All Prescriptions</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
    
    // Log form submission for debugging
    const forms = document.querySelectorAll('form[action*="update_appointment_status"]');
    forms.forEach(function(form) {
        console.log('Found appointment form:', form);
    });
});
</script>
</body>
</html>
