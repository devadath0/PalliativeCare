<?php
/**
 * Edit Patient Profile View
 * Palliative Care System
 */

// Set page title
$page_title = 'Edit Profile';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Edit Profile</h2>
            <p class="lead">Update your personal information</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=profile" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="index.php?module=patient&action=edit_profile" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($patient['name'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" 
                               value="<?= htmlspecialchars($patient['email'] ?? '') ?>" readonly>
                        <div class="form-text">Email cannot be changed. Contact support if needed.</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3">
                                <?php if (!empty($patient['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars(SITE_URL . $patient['profile_image']) ?>" 
                                         class="img-thumbnail rounded-circle" alt="Profile Image" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-center p-3 bg-light rounded-circle" style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-5x text-secondary mt-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg, image/png, image/gif">
                                <div class="form-text">Upload a profile picture (JPG, PNG, or GIF, max 2MB)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($patient['phone'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your phone number.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" 
                               value="<?= !empty($patient['dob']) ? date('Y-m-d', strtotime($patient['dob'])) : '' ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">-- Select Gender --</option>
                            <option value="male" <?= ($patient['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($patient['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($patient['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="blood_group" class="form-label">Blood Group</label>
                        <select class="form-select" id="blood_group" name="blood_group">
                            <option value="">-- Select Blood Group --</option>
                            <option value="A+" <?= ($patient['blood_group'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= ($patient['blood_group'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= ($patient['blood_group'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= ($patient['blood_group'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= ($patient['blood_group'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= ($patient['blood_group'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= ($patient['blood_group'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= ($patient['blood_group'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="emergency_contact" class="form-label">Emergency Contact</label>
                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                           value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                    <div class="form-text">Name and phone number of someone we can contact in case of emergency.</div>
                </div>

                <hr class="my-4">
                <h5>Medical Information</h5>

                <div class="mb-3">
                    <label for="medical_history" class="form-label">Medical History</label>
                    <textarea class="form-control" id="medical_history" name="medical_history" rows="4"><?= htmlspecialchars($patient['medical_history'] ?? '') ?></textarea>
                    <div class="form-text">Provide information about your medical history, chronic conditions, surgeries, allergies, etc.</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php?module=patient&action=profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 