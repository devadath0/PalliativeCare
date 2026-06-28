<?php
/**
 * Doctor Appointment Reschedule View
 * 
 * This view allows doctors to reschedule patient appointments
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment - Palliative Care System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Base styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        .nav-link {
            color: #333;
        }
        
        .nav-link:hover {
            color: #007bff;
        }
        
        /* Card styles */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        
        /* Avatar styles */
        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .avatar-md {
            width: 50px;
            height: 50px;
        }
        
        /* Form styles */
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Button styles */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }
        
        /* Alert styles */
        .alert {
            border-radius: 0.5rem;
        }
        
        /* Badge styles */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
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
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'doctor'): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </nav>

<!-- Page header -->
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Reschedule Appointment</h4>
        <a href="index.php?module=doctor&action=appointments" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Appointments
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Appointment Details</h5>
                </div>
                <div class="card-body">
                    <!-- Patient Information -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-primary text-white me-3">
                                <?php echo strtoupper(substr($appointment['patient_first_name'] ?? 'P', 0, 1)); ?>
                            </div>
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']); ?></h5>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Appointment Details -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Current Appointment Time:</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge bg-<?php echo ($appointment['status'] === 'confirmed') ? 'success' : 'warning'; ?> p-2">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                            <div>
                                <div class="text-primary fs-5 fw-bold">
                                    <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
                                </div>
                                <div class="text-muted">
                                    <?php echo date('h:i A', strtotime($appointment['appointment_date'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($appointment['notes'])): ?>
                        <div class="mt-2">
                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reschedule Form -->
                    <form method="post" action="index.php?module=doctor&action=reschedule_appointment" id="rescheduleForm">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                        
                        <h6 class="text-muted mb-3">Reschedule To:</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">New Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                    value="<?php echo $current_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">New Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                    value="<?php echo $current_time; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                placeholder="Add any notes about this rescheduling"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Rescheduling this appointment will notify the patient and update their calendar.
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="index.php?module=doctor&action=appointments" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Reschedule Appointment
                            </button>
                        </div>
                    </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate the form before submission
    const rescheduleForm = document.getElementById('rescheduleForm');
    if (rescheduleForm) {
        rescheduleForm.addEventListener('submit', function(e) {
            const dateInput = document.getElementById('appointment_date');
            const timeInput = document.getElementById('appointment_time');
            
            // Create date objects for validation
            const selectedDate = new Date(dateInput.value + 'T' + timeInput.value);
            const now = new Date();
            
            // Check if selected date/time is in the past
            if (selectedDate < now) {
                e.preventDefault();
                alert('Please select a future date and time for the appointment.');
                return false;
            }
            
            // Check if anything changed
            const originalDateTime = new Date('<?php echo $current_date . "T" . $current_time; ?>');
            if (selectedDate.getTime() === originalDateTime.getTime()) {
                if (!confirm('The appointment date and time have not changed. Do you still want to proceed?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    }
});
</script>
</body>
</html> 