<?php
/**
 * Palliative Care System - Doctor Module
 * View Patient Details
 */

// Set page title
$page_title = isset($page_title) ? $page_title : 'Patient Details';

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
            <h2><?php echo htmlspecialchars($patient['name']); ?>'s Profile</h2>
        </div>
        <div class="col-md-4 text-end">
                <a href="index.php?module=doctor&action=dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
        </div>
    </div>

    <div class="row">
        <!-- Patient Information -->
        <div class="col-md-4">
            <!-- Patient Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Patient Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="text-center p-3 bg-light rounded-circle mx-auto" style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-5x text-secondary mt-2"></i>
                        </div>
                        <h4 class="mt-3"><?php echo htmlspecialchars($patient['name']); ?></h4>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope me-2"></i> Email</span>
                            <span class="text-muted"><?php echo htmlspecialchars($patient['email']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-phone me-2"></i> Phone</span>
                            <span class="text-muted"><?php echo htmlspecialchars($patient['phone'] ?? 'Not provided'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-birthday-cake me-2"></i> Date of Birth</span>
                            <span class="text-muted">
                                <?php 
                                if (!empty($patient['date_of_birth'])) {
                                    echo date('M d, Y', strtotime($patient['date_of_birth']));
                                    $dob = new DateTime($patient['date_of_birth']);
                                    $now = new DateTime();
                                    $age = $now->diff($dob)->y;
                                    echo ' (' . $age . ' years)';
                                } else {
                                    echo 'Not provided';
                                }
                                ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-venus-mars me-2"></i> Gender</span>
                            <span class="text-muted"><?php echo htmlspecialchars(ucfirst($patient['gender'] ?? 'Not specified')); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tint me-2"></i> Blood Group</span>
                            <span class="text-muted"><?php echo htmlspecialchars($patient['blood_group'] ?? 'Not specified'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-map-marker-alt me-2"></i> Address</span>
                            <span class="text-muted"><?php echo htmlspecialchars($patient['address'] ?? 'Not provided'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Medical Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Medical Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-allergies me-2"></i> Allergies</h6>
                        <p><?php echo !empty($patient['allergies']) ? htmlspecialchars($patient['allergies']) : 'No known allergies'; ?></p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-heartbeat me-2"></i> Medical Conditions</h6>
                        <p><?php echo !empty($patient['medical_conditions']) ? htmlspecialchars($patient['medical_conditions']) : 'No known medical conditions'; ?></p>
                    </div>
                    <div>
                        <h6><i class="fas fa-pills me-2"></i> Current Medications</h6>
                        <p><?php echo !empty($patient['current_medications']) ? htmlspecialchars($patient['current_medications']) : 'No current medications'; ?></p>
                        </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                        <a href="index.php?module=doctor&action=create_prescription&patient_id=<?php echo $patient['id']; ?>" class="btn btn-success">
                            <i class="fas fa-prescription"></i> Create Prescription
                        </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Appointments and Prescriptions -->
            <ul class="nav nav-tabs mb-4" id="patientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="true">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab" aria-controls="prescriptions" aria-selected="false">
                        <i class="fas fa-prescription"></i> Prescriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="medical-history-tab" data-bs-toggle="tab" data-bs-target="#medical-history" type="button" role="tab" aria-controls="medical-history" aria-selected="false">
                        <i class="fas fa-notes-medical"></i> Medical History
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="patientTabsContent">
                <!-- Appointments Tab -->
                <div class="tab-pane fade show active" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                    <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Appointment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($appointments)): ?>
                                <div class="alert alert-info">No appointment history found for this patient.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                                <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?><br>
                                                        <small class="text-muted">
                                                            <?php 
                                                            if (isset($appointment['appointment_time'])) {
                                                                echo date('h:i A', strtotime($appointment['appointment_time']));
                                                            }
                                                            ?>
                                                        </small>
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
                                                    <td><?php echo htmlspecialchars($appointment['reason'] ?? 'Not specified'); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['notes'] ?? 'No notes'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                </div>
                
                <!-- Prescriptions Tab -->
                <div class="tab-pane fade" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Prescription History</h5>
                            <a href="index.php?module=doctor&action=create_prescription&patient_id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> New Prescription
                            </a>
                </div>
                <div class="card-body">
                    <?php if (empty($prescriptions)): ?>
                                <div class="alert alert-info">No prescription history found for this patient.</div>
                    <?php else: ?>
                        <div class="accordion" id="prescriptionAccordion">
                            <?php foreach ($prescriptions as $index => $prescription): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                        <span>
                                                            <i class="fas fa-prescription me-2"></i>
                                                            <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?>
                                                        </span>
                                                        <span class="badge bg-primary"><?php echo isset($prescription['diagnosis']) ? 'Diagnosis Available' : 'No Diagnosis'; ?></span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#prescriptionAccordion">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                            <h6>Diagnosis</h6>
                                                            <p>
                                                                <?php 
                                                                if (!empty($prescription['diagnosis'])) {
                                                                    $diagnosis_lines = explode("\n", $prescription['diagnosis']);
                                                                    foreach ($diagnosis_lines as $line) {
                                                                        echo htmlspecialchars($line) . "<br>";
                                                                    }
                                                                } else {
                                                                    echo "Not specified";
                                                                }
                                                                ?>
                                                            </p>
                                                </div>
                                                <div class="col-md-6">
                                                            <h6>Notes</h6>
                                                            <p>
                                                                <?php 
                                                                if (!empty($prescription['notes'])) {
                                                                    echo nl2br(htmlspecialchars($prescription['notes']));
                                                                } else {
                                                                    echo "No special instructions";
                                                                }
                                                                ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Medical History Tab -->
                <div class="tab-pane fade" id="medical-history" role="tabpanel" aria-labelledby="medical-history-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5>Medical History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($medical_history)): ?>
                                <div class="alert alert-info">No medical history records found for this patient.</div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($medical_history as $record): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-date">
                                                <?php echo date('M d, Y', strtotime($record['recorded_date'])); ?>
                                            </div>
                                            <div class="timeline-content">
                                                <h6><?php echo htmlspecialchars($record['title'] ?? 'No Title'); ?></h6>
                                                <p><?php echo htmlspecialchars($record['description'] ?? 'No description available'); ?></p>
                                                <?php if (!empty($record['treatment'])): ?>
                                                    <p><strong>Treatment:</strong> <?php echo htmlspecialchars($record['treatment']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
});
</script>
</body>
</html>
