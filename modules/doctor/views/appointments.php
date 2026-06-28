<?php
/**
 * Doctor Appointments
 * Palliative Care System - Doctor Module
 */

// Set page title
$page_title = isset($page_title) ? $page_title : 'Manage Appointments';

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

<?php
// Use the database connection passed from the controller
if (!isset($db)) {
    die('Database connection not available');
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Manage Appointments</h2>
            <p class="lead">View and manage your appointments</p>
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
    
    <!-- Appointment Tabs -->
    <ul class="nav nav-tabs mb-4" id="appointmentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo (!isset($filter) || $filter === null) ? 'active' : ''; ?>" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="<?php echo (!isset($filter) || $filter === null) ? 'true' : 'false'; ?>">
                All Appointments
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo (isset($filter) && $filter === 'pending') ? 'active' : ''; ?>" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="<?php echo (isset($filter) && $filter === 'pending') ? 'true' : 'false'; ?>">
                Pending 
                <?php if (!empty($grouped_appointments['scheduled'])): ?>
                    <span class="badge bg-warning"><?php echo count($grouped_appointments['scheduled']); ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo (isset($filter) && $filter === 'today') ? 'active' : ''; ?>" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirmed" type="button" role="tab" aria-controls="confirmed" aria-selected="<?php echo (isset($filter) && $filter === 'today') ? 'true' : 'false'; ?>">
                Confirmed 
                <?php if (!empty($grouped_appointments['confirmed'])): ?>
                    <span class="badge bg-success"><?php echo count($grouped_appointments['confirmed']); ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab" aria-controls="completed" aria-selected="false">
                Completed 
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab" aria-controls="cancelled" aria-selected="false">
                Cancelled 
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="appointmentTabsContent">
        <!-- All Appointments Tab -->
        <div class="tab-pane fade <?php echo (!isset($filter) || $filter === null) ? 'show active' : ''; ?>" id="all" role="tabpanel" aria-labelledby="all-tab">
            <?php if (empty($appointments)): ?>
                <div class="alert alert-info">You have no appointments yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                        <?php if (!empty($appointment['hospital_name'])): ?>
                                        <br><small class="text-secondary"><i class="fas fa-hospital"></i> 
                                        <?php echo htmlspecialchars($appointment['hospital_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
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
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            
                                            <?php if ($appointment['status'] == 'scheduled'): ?>
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Confirm
                                                </button>
                                            <?php elseif ($appointment['status'] == 'confirmed'): ?>
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-check-circle"></i> Complete
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <?php if ($appointment['status'] == 'scheduled'): ?>
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-danger">
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
        
        <!-- Pending Appointments Tab -->
        <div class="tab-pane fade <?php echo (isset($filter) && $filter === 'pending') ? 'show active' : ''; ?>" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            <?php if (empty($grouped_appointments['scheduled'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You have no pending appointments at this time.
                    <p class="small mt-2 mb-0">When patients request appointments, they will appear here for your approval.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_appointments['scheduled'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                        <?php if (!empty($appointment['hospital_name'])): ?>
                                        <br><small class="text-secondary"><i class="fas fa-hospital"></i> 
                                        <?php echo htmlspecialchars($appointment['hospital_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?><br>
                                        <small class="text-muted">Phone: <?php echo $appointment['patient_phone']; ?></small>
                                    </td>
                                    <td>
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Confirm
                                            </button>
                                        </form>
                                        
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                        
                                        <!-- Reschedule button for scheduled or confirmed appointments -->
                                        <?php if (in_array($appointment['status'], ['scheduled', 'confirmed'])): ?>
                                        <a href="index.php?module=doctor&action=reschedule_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </a>
                                        <?php endif; ?>
                                        
                                        <!-- Debug info -->
                                        <div class="small text-muted mt-1">
                                            ID: <?php echo $appointment['id']; ?>
                                        </div>
                                        
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
        
        <!-- Confirmed Appointments -->
        <div class="tab-pane fade <?php echo (isset($filter) && $filter === 'today') ? 'show active' : ''; ?>" id="confirmed" role="tabpanel" aria-labelledby="confirmed-tab">
            <?php if (empty($grouped_appointments['confirmed'])): ?>
                <div class="alert alert-info">You have no confirmed appointments.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_appointments['confirmed'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                    </td>
                                    <td><?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?></td>
                                    <td><?php echo $appointment['patient_phone']; ?></td>
                                    <td><?php echo $appointment['notes'] ?? 'No notes'; ?></td>
                                    <td>
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-check-circle"></i> Complete
                                            </button>
                                        </form>
                                        
                                        <form method="post" action="index.php?module=doctor&action=update_appointment_status" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                        
                                        <!-- Reschedule button -->
                                        <a href="index.php?module=doctor&action=reschedule_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </a>
                                        
                                        <a href="index.php?module=doctor&action=view_patient&id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-user"></i> Patient Info
                                        </a>
                                        
                                        <a href="index.php?module=doctor&action=create_prescription&patient_id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-prescription"></i> Prescribe
                                        </a>
                                        
                                        <a href="index.php?module=doctor&action=chat_with_patient&appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary mt-1">
                                            <i class="fas fa-comment-medical"></i> Chat
                                            <?php if (isset($appointment['unread_count']) && $appointment['unread_count'] > 0): ?>
                                                <span class="badge bg-danger"><?php echo $appointment['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Completed Appointments -->
        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
            <?php if (empty($grouped_appointments['completed'])): ?>
                <div class="alert alert-info">You have no completed appointments.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_appointments['completed'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?><br>
                                        <small class="text-muted">Phone: <?php echo $appointment['patient_phone'] ?? 'N/A'; ?></small>
                                    </td>
                                    <td><?php echo $appointment['reason'] ?? 'Not specified'; ?></td>
                                    <td>
                                        <a href="index.php?module=doctor&action=create_prescription&patient_id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-prescription"></i> Create Prescription
                                        </a>
                                        
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
        
        <!-- Cancelled Appointments -->
        <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
            <?php if (empty($grouped_appointments['cancelled'])): ?>
                <div class="alert alert-info">You have no cancelled appointments.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped_appointments['cancelled'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']; ?><br>
                                        <small class="text-muted">Phone: <?php echo $appointment['patient_phone'] ?? 'N/A'; ?></small>
                                    </td>
                                    <td><?php echo $appointment['reason'] ?? 'Not specified'; ?></td>
                                    <td>
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

<!-- Debug information (only visible to admins) -->
<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<div class="container mt-5 border-top pt-3">
    <h6 class="text-muted">Debug Information</h6>
    <div class="small">
        <p>Filter: <?php echo isset($filter) ? htmlspecialchars($filter) : 'None'; ?></p>
        <p>Total Appointments: <?php echo count($appointments); ?></p>
        <p>Grouped Appointments:</p>
        <ul>
            <li>Scheduled: <?php echo count($grouped_appointments['scheduled']); ?></li>
            <li>Confirmed: <?php echo count($grouped_appointments['confirmed']); ?></li>
            <li>Completed: <?php echo count($grouped_appointments['completed']); ?></li>
            <li>Cancelled: <?php echo count($grouped_appointments['cancelled']); ?></li>
        </ul>
    </div>
</div>
<?php endif; ?>

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
    
    // Add confirmation for reject buttons
    const rejectButtons = document.querySelectorAll('button[type="submit"][class*="btn-danger"]');
    rejectButtons.forEach(function(button) {
        console.log('Found reject button:', button);
        button.addEventListener('click', function(e) {
            console.log('Reject button clicked');
            try {
                // Prevent the default form submission
                e.preventDefault();
                
                if (confirm('Are you sure you want to reject this appointment? This action cannot be undone.')) {
                    console.log('Rejection confirmed, submitting form');
                    // Manually submit the form
                    const form = button.closest('form');
                    console.log('Submitting form:', form);
                    form.submit();
                } else {
                    console.log('Rejection cancelled by user');
                }
            } catch (error) {
                console.error('Error in confirmation dialog:', error);
            }
        });
    });
});
</script>
</body>
</html>
