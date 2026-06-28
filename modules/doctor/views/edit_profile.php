<?php
/**
 * Edit Doctor Profile View
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
            <p class="lead">Update your professional information</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=doctor&action=profile" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="index.php?module=doctor&action=edit_profile" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($doctor['name'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" 
                               value="<?= htmlspecialchars($doctor['email'] ?? '') ?>" readonly>
                        <div class="form-text">Email cannot be changed. Contact support if needed.</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3">
                                <?php if (!empty($doctor['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($doctor['profile_image'] ?? '') ?>" 
                                         class="img-thumbnail rounded-circle" alt="Profile Image" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-center p-3 bg-light rounded-circle" style="width: 150px; height: 150px;">
                                        <i class="fas fa-user-md fa-5x text-secondary mt-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg, image/png, image/gif">
                                <div class="form-text">Upload a professional profile picture (JPG, PNG, or GIF, max 2MB)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your phone number.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="specialization" class="form-label">Specialization <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="specialization" name="specialization" 
                               value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your specialization.</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="qualification" class="form-label">Qualification</label>
                        <input type="text" class="form-control" id="qualification" name="qualification" 
                               value="<?= htmlspecialchars($doctor['qualification'] ?? '') ?>">
                        <div class="form-text">E.g., MD, PhD, MBBS, etc.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="license_number" class="form-label">License Number</label>
                        <input type="text" class="form-control" id="license_number" name="license_number" 
                               value="<?= htmlspecialchars($doctor['license_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="experience_years" class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" 
                               value="<?= htmlspecialchars($doctor['experience_years'] ?? '') ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="consultation_fee" class="form-label">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" min="0" step="0.01" 
                                   value="<?= htmlspecialchars($doctor['consultation_fee'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hospital_id" class="form-label">Hospital/Clinic Location</label>
                        <select class="form-select" id="hospital_id" name="hospital_id">
                            <option value="">-- Select Hospital/Clinic --</option>
                            <?php if (isset($hospitals) && is_array($hospitals)): ?>
                                <?php foreach ($hospitals as $hospital): ?>
                                    <option value="<?= htmlspecialchars($hospital['id']) ?>" 
                                        <?= (isset($doctor['hospital_id']) && $doctor['hospital_id'] == $hospital['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($hospital['name']) ?> - <?= htmlspecialchars($hospital['address']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Select your primary hospital or clinic location</div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php?module=doctor&action=profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
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