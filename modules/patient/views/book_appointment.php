<?php
/**
 * Book Appointment View
 * Palliative Care System
 */

// Set page title
$page_title = 'Book Appointment';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Book an Appointment</h2>
            <p class="lead">Schedule a consultation with one of our specialists</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=appointments" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($doctors)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No doctors are available at the moment. Please try again later.
                </div>
            <?php else: ?>
                <form action="index.php?module=patient&action=book_appointment" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="doctor_id" class="form-label">Select Doctor</label>
                        <?php if (isset($selected_doctor)): ?>
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-user-md"></i> You are booking an appointment with 
                            <strong>Dr. <?php echo htmlspecialchars($selected_doctor['name']); ?></strong> 
                            (<?php echo htmlspecialchars($selected_doctor['specialization']); ?>).
                        </div>
                        <?php endif; ?>
                        <select class="form-select" id="doctor_id" name="doctor_id" required>
                            <option value="">-- Select a doctor --</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>" <?php echo (isset($_GET['doctor_id']) && $_GET['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                    Dr. <?php echo htmlspecialchars($doctor['name'] ?? 'Unknown'); ?> - 
                                    <?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?> 
                                    (Fee: $<?php echo number_format($doctor['consultation_fee'] ?? 0, 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a doctor.</div>
                    </div>

                    <div class="mb-3">
                        <label for="appointment_date" class="form-label">Appointment Date & Time</label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        <div class="invalid-feedback">Please select a valid date and time for your appointment.</div>
                        <small class="form-text text-muted">Please select a date and time during working hours (9:00 AM - 5:00 PM).</small>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        <div class="invalid-feedback">Please provide a reason for your visit.</div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I understand that this is a request for an appointment and will be confirmed by the doctor.
                        </label>
                        <div class="invalid-feedback">You must agree before submitting.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Request Appointment
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Automatically focus on the appointment date field if doctor is pre-selected
        var doctorSelect = document.getElementById('doctor_id');
        if (doctorSelect && doctorSelect.value) {
            var dateField = document.getElementById('appointment_date');
            if (dateField) {
                setTimeout(function() {
                    dateField.focus();
                }, 500);
            }
        }
    }, false);
})();
</script>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?>
