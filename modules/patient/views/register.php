<?php
/**
 * Registration View
 * Palliative Care System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css" type="text/css">
    <!-- Add any additional CSS libraries here -->
    <style>
        .user-type-fields {
            display: none;
        }
        .user-type-fields.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box register-box">
                <h2 class="auth-title">Create an Account</h2>
                
                <?php 
                // Display flash messages
                if (function_exists('displayFlashMessage')) {
                    displayFlashMessage();
                }
                ?>
                
                <form action="<?php echo SITE_URL; ?>modules/auth.php?action=register" method="post" class="auth-form needs-validation">
                    <!-- Common fields for all user types -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <small class="form-text text-muted">Password must be at least 8 characters and contain both letters and numbers.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type">Register as</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">-- Select User Type --</option>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                            <option value="service_provider">Service Provider</option>
                        </select>
                    </div>
                    
                    <!-- Patient-specific fields -->
                    <div id="patient-fields" class="user-type-fields">
                        <h3>Patient Information</h3>
                        
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">-- Select Gender --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="emergency_contact">Emergency Contact</label>
                            <input type="tel" id="emergency_contact" name="emergency_contact">
                        </div>
                    </div>
                    
                    <!-- Doctor-specific fields -->
                    <div id="doctor-fields" class="user-type-fields">
                        <h3>Doctor Information</h3>
                        
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="doctor_first_name" name="first_name">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="doctor_last_name" name="last_name">
                        </div>
                        
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <input type="text" id="specialization" name="specialization">
                        </div>
                        
                        <div class="form-group">
                            <label for="qualification">Qualification</label>
                            <input type="text" id="qualification" name="qualification">
                        </div>
                        
                        <div class="form-group">
                            <label for="experience">Years of Experience</label>
                            <input type="number" id="experience" name="experience" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="doctor_phone">Phone Number</label>
                            <input type="tel" id="doctor_phone" name="phone">
                        </div>
                    </div>
                    
                    <!-- Service Provider-specific fields -->
                    <div id="service-provider-fields" class="user-type-fields">
                        <h3>Service Provider Information</h3>
                        
                        <div class="form-group">
                            <label for="name">Company/Organization Name</label>
                            <input type="text" id="name" name="name">
                        </div>
                        
                        <div class="form-group">
                            <label for="service_type">Service Type</label>
                            <select id="service_type" name="service_type">
                                <option value="">-- Select Service Type --</option>
                                <option value="cab">Cab Service</option>
                                <option value="medicine">Medicine Delivery</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" id="contact_person" name="contact_person">
                        </div>
                        
                        <div class="form-group">
                            <label for="provider_phone">Phone Number</label>
                            <input type="tel" id="provider_phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="provider_address">Address</label>
                            <textarea id="provider_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                    
                    <div class="auth-links">
                        <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>assets/js/main.js" type="text/javascript"></script>
    <script>
        // Show/hide fields based on user type selection
        document.getElementById('user_type').addEventListener('change', function() {
            // Hide all user type specific fields
            document.querySelectorAll('.user-type-fields').forEach(function(el) {
                el.classList.remove('active');
            });
            
            // Show fields for selected user type
            const userType = this.value;
            if (userType === 'patient') {
                document.getElementById('patient-fields').classList.add('active');
            } else if (userType === 'doctor') {
                document.getElementById('doctor-fields').classList.add('active');
            } else if (userType === 'service_provider') {
                document.getElementById('service-provider-fields').classList.add('active');
            }
        });
        
        // Form validation
        document.querySelector('form.needs-validation').addEventListener('submit', function(event) {
            const userType = document.getElementById('user_type').value;
            let isValid = true;
            
            // Validate password match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                isValid = false;
            }
            
            // Validate user type specific fields
            if (userType === 'patient') {
                const requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'address', 'phone', 'emergency_contact'];
                requiredFields.forEach(function(field) {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            } else if (userType === 'doctor') {
                const requiredFields = ['doctor_first_name', 'doctor_last_name', 'specialization', 'qualification', 'experience', 'doctor_phone'];
                requiredFields.forEach(function(field) {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            } else if (userType === 'service_provider') {
                const requiredFields = ['name', 'service_type', 'contact_person', 'provider_phone', 'provider_address'];
                requiredFields.forEach(function(field) {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
                alert('Please fill in all required fields for your user type');
            }
        });
    </script>
</body>
</html>
