<?php
/**
 * Patient Profile View
 * Palliative Care System
 */

// Set page title
$page_title = 'My Profile';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>My Profile</h2>
            <p class="lead">View and manage your personal information</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=edit_profile" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($patient['profile_image'])): ?>
                            <img src="<?= htmlspecialchars(SITE_URL . $patient['profile_image']) ?>" 
                                 class="img-thumbnail rounded-circle" alt="Profile Image" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        <?php endif; ?>
                    </div>
                    <h4><?= htmlspecialchars($patient['name']) ?></h4>
                    <p class="text-muted">
                        <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($patient['email']) ?>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-phone me-2"></i> <?= htmlspecialchars($patient['phone'] ?? 'Not provided') ?>
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-<?= $patient['user_status'] === 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($patient['user_status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Date of Birth</div>
                        <div class="col-md-8">
                            <?= !empty($patient['dob']) ? date('F j, Y', strtotime($patient['dob'])) : 'Not provided' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Gender</div>
                        <div class="col-md-8">
                            <?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'Not provided' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Blood Group</div>
                        <div class="col-md-8">
                            <?= !empty($patient['blood_group']) ? $patient['blood_group'] : 'Not provided' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Address</div>
                        <div class="col-md-8">
                            <?= !empty($patient['address']) ? nl2br(htmlspecialchars($patient['address'])) : 'Not provided' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Emergency Contact</div>
                        <div class="col-md-8">
                            <?= !empty($patient['emergency_contact']) ? htmlspecialchars($patient['emergency_contact']) : 'Not provided' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Medical Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Allergies</div>
                        <div class="col-md-8">
                            <?php if (!empty($patient['allergies'])): ?>
                                <p><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                            <?php else: ?>
                                <p class="text-muted">No allergies recorded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 fw-bold">Medical History</div>
                        <div class="col-md-8">
                            <?php if (!empty($patient['medical_history'])): ?>
                                <p><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                            <?php else: ?>
                                <p class="text-muted">No medical history recorded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?>
