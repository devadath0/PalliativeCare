<?php
/**
 * Admin - Add Doctor
 */

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php?module=auth&action=login&type=admin');
    exit;
}

// Set page title and current page for navigation
$page_title = 'Add New Doctor';
$current_page = 'doctors';

// Include header
require_once 'modules/admin/views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Add New Doctor</h2>
            <p class="text-muted">Register a new doctor in the system</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=admin&action=doctors" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Doctors
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="index.php?module=admin&action=process_add_doctor" method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Please enter the doctor's name.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                        <div class="invalid-feedback">Please enter a phone number.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="specialization" class="form-label">Specialization</label>
                        <input type="text" class="form-control" id="specialization" name="specialization" required>
                        <div class="invalid-feedback">Please enter the doctor's specialization.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="license_number" class="form-label">License Number</label>
                        <input type="text" class="form-control" id="license_number" name="license_number" required>
                        <div class="invalid-feedback">Please enter the license number.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="experience_years" class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" required>
                        <div class="invalid-feedback">Please enter years of experience.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="qualification" class="form-label">Qualification</label>
                        <input type="text" class="form-control" id="qualification" name="qualification" required>
                        <div class="invalid-feedback">Please enter the qualification.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="availability_status" class="form-label">Availability Status</label>
                        <select class="form-select" id="availability_status" name="availability_status" required>
                            <option value="">Choose...</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                        <div class="invalid-feedback">Please select the availability status.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="consultation_fee" class="form-label">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" min="0" step="0.01" required>
                            <div class="invalid-feedback">Please enter the consultation fee.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Doctor
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset Form
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
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php
// Include footer
require_once 'modules/admin/views/includes/footer.php';
?> 