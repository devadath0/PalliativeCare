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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>assets/css/auth.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #36b9cc;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .register-container {
            padding: 2rem 0;
        }
        
        .register-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background-color: #fff;
        }
        
        .register-header {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .register-sidebar {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .register-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.2) 100%);
            opacity: 0.3;
        }
        
        .register-sidebar h2 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .register-sidebar p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .register-sidebar .features {
            position: relative;
        }
        
        .register-sidebar .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .register-sidebar .feature-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--accent-color);
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
        }
        
        .btn-register {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background-color: #3a5ccc;
            border-color: #3a5ccc;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: var(--dark-color);
            margin-top: 0.5rem;
        }
        
        .password-requirements ul {
            padding-left: 1.5rem;
            margin-bottom: 0;
        }
        
        .password-requirements li {
            margin-bottom: 0.25rem;
        }
        
        .password-requirements li.valid {
            color: var(--success-color);
        }
        
        .password-requirements li.invalid {
            color: var(--danger-color);
        }
        
        .register-footer {
            text-align: center;
            padding: 1.5rem;
            background-color: #f8f9fc;
            border-top: 1px solid #e3e6f0;
        }
        
        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .register-footer a:hover {
            color: #3a5ccc;
            text-decoration: underline;
        }
        
        .form-check {
            margin-bottom: 1.5rem;
        }
        
        .alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .user-type-selector {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .user-type-selector .btn-group {
            width: 100%;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .user-type-selector .btn {
            flex: 1;
            padding: 1rem;
            border: 1px solid #e3e6f0;
            background-color: white;
            color: var(--dark-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .user-type-selector .btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .user-type-selector .btn:hover:not(.active) {
            background-color: #f8f9fc;
        }
        
        .user-type-selector .btn i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        @media (max-width: 991.98px) {
            .register-sidebar {
                display: none;
            }
        }
        
        @media (max-width: 767.98px) {
            .register-body {
                padding: 1.5rem;
            }
            
            .register-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card register-card">
                    <div class="row g-0">
                        <div class="col-lg-5">
                            <div class="register-sidebar h-100">
                                <h2>Welcome to Palliative Care</h2>
                                <p>Join our platform to access quality healthcare services tailored to your needs.</p>
                                
                                <div class="features">
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>Connect with specialized doctors</div>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>Schedule appointments easily</div>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-file-medical"></i>
                                        </div>
                                        <div>Access your medical records</div>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                        <div>Order medications online</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="register-body">
                                <?php
                                $userType = htmlspecialchars($_GET['type'] ?? 'patient');
                                $userTypeDisplay = ucfirst($userType);
                                ?>
                                
                                <h3 class="text-center mb-4">Create Your Account</h3>
                                
                                <div class="user-type-selector">
                                    <div class="btn-group" role="group">
                                        <a href="index.php?module=auth&action=register&type=patient" class="btn <?php echo $userType === 'patient' ? 'active' : ''; ?>">
                                            <i class="fas fa-user"></i>
                                            Patient
                                        </a>
                                        <a href="index.php?module=auth&action=register&type=doctor" class="btn <?php echo $userType === 'doctor' ? 'active' : ''; ?>">
                                            <i class="fas fa-user-md"></i>
                                            Doctor
                                        </a>
                                        <a href="index.php?module=auth&action=register&type=service" class="btn <?php echo $userType === 'service' ? 'active' : ''; ?>">
                                            <i class="fas fa-briefcase-medical"></i>
                                            Service Provider
                                        </a>
                                    </div>
                                </div>
                                
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php 
                                        echo $_SESSION['error'];
                                        unset($_SESSION['error']);
                                        ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form action="index.php?module=auth&action=<?php 
                                    if ($userType === 'patient') {
                                        echo 'process_patient_register';
                                    } elseif ($userType === 'doctor') {
                                        echo 'process_doctor_register';
                                    } elseif ($userType === 'service') {
                                        echo 'process_service_register';
                                    } else {
                                        echo 'register';
                                    }
                                ?>&type=<?php echo $userType; ?>" method="post" class="needs-validation" novalidate>
                                    
                                    <?php if ($userType === 'patient' || $userType === 'doctor'): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                                                <label for="first_name">First Name</label>
                                                <div class="invalid-feedback">Please enter your first name</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                                                <label for="last_name">Last Name</label>
                                                <div class="invalid-feedback">Please enter your last name</div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php elseif ($userType === 'service'): ?>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Name" required>
                                        <label for="company_name">Company Name</label>
                                        <div class="invalid-feedback">Please enter your company name</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="service_type" name="service_type" required>
                                            <option value="" selected disabled>Select Service Type</option>
                                            <option value="pharmacy">Pharmacy</option>
                                            <option value="equipment">Medical Equipment</option>
                                            <option value="transportation">Transportation</option>
                                            <option value="homecare">Home Care</option>
                                        </select>
                                        <label for="service_type">Service Type</label>
                                        <div class="invalid-feedback">Please select your service type</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="address" name="address" placeholder="Address" style="height: 100px" required></textarea>
                                        <label for="address">Address</label>
                                        <div class="invalid-feedback">Please enter your address</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="operating_hours" name="operating_hours" placeholder="Operating Hours" required>
                                        <label for="operating_hours">Operating Hours</label>
                                        <div class="invalid-feedback">Please enter your operating hours</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="service_area" name="service_area" placeholder="Service Area" required>
                                        <label for="service_area">Service Area</label>
                                        <div class="invalid-feedback">Please enter your service area</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="license_number" name="license_number" placeholder="License Number" required>
                                        <label for="license_number">License Number</label>
                                        <div class="invalid-feedback">Please enter your license number</div>
                                    </div>
                                    <?php else: ?>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
                                        <label for="name">Full Name</label>
                                        <div class="invalid-feedback">Please enter your full name</div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                                        <label for="email">Email Address</label>
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" required>
                                        <label for="phone">Phone Number</label>
                                        <div class="invalid-feedback">Please enter your phone number</div>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" 
                                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                        <label for="password">Password</label>
                                        <div class="invalid-feedback">Password must meet the requirements below</div>
                                    </div>
                                    
                                    <div class="password-requirements mb-3">
                                        <p class="mb-1">Password must contain:</p>
                                        <ul>
                                            <li id="length" class="invalid">At least 8 characters</li>
                                            <li id="uppercase" class="invalid">At least one uppercase letter</li>
                                            <li id="lowercase" class="invalid">At least one lowercase letter</li>
                                            <li id="number" class="invalid">At least one number</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                        <label for="confirm_password">Confirm Password</label>
                                        <div class="invalid-feedback">Passwords do not match</div>
                                    </div>

                                    <?php if ($userType === 'patient'): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                                <label for="date_of_birth">Date of Birth</label>
                                                <div class="invalid-feedback">Please select your date of birth</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="gender" name="gender" required>
                                                    <option value="" selected disabled>Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                    <option value="other">Other</option>
                                                </select>
                                                <label for="gender">Gender</label>
                                                <div class="invalid-feedback">Please select your gender</div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($userType === 'doctor'): ?>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="specialization" name="specialization" placeholder="Specialization" required>
                                        <label for="specialization">Specialization</label>
                                        <div class="invalid-feedback">Please enter your specialization</div>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="qualification" name="qualification" placeholder="Qualification" required>
                                        <label for="qualification">Qualification</label>
                                        <div class="invalid-feedback">Please enter your qualification</div>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="license_number" name="license_number" placeholder="License Number" required>
                                        <label for="license_number">License Number</label>
                                        <div class="invalid-feedback">Please enter your license number</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" placeholder="Years of Experience" required>
                                                <label for="experience_years">Years of Experience</label>
                                                <div class="invalid-feedback">Please enter your years of experience</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" min="0" step="0.01" placeholder="Consultation Fee" required>
                                                <label for="consultation_fee">Consultation Fee</label>
                                                <div class="invalid-feedback">Please enter your consultation fee</div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($userType === 'service'): ?>
                                    <!-- Service provider form fields are already defined above -->
                                    <?php endif; ?>
                                    
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                        </label>
                                        <div class="invalid-feedback">
                                            You must agree to the terms and conditions
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-register">
                                            <i class="fas fa-user-plus me-2"></i> Create Account
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="register-footer">
                                <p class="mb-0">
                                    Already have an account? 
                                    <a href="index.php?module=auth&action=login&type=<?php echo $userType; ?>">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptance of Terms</h6>
                    <p>By registering for an account on our Palliative Care System, you agree to be bound by these Terms and Conditions.</p>
                    
                    <h6>2. User Accounts</h6>
                    <p>You are responsible for maintaining the confidentiality of your account information and password.</p>
                    
                    <h6>3. Medical Disclaimer</h6>
                    <p>The information provided through our platform is not intended to replace professional medical advice.</p>
                    
                    <h6>4. Privacy</h6>
                    <p>Your use of our service is also governed by our Privacy Policy.</p>
                    
                    <h6>5. Termination</h6>
                    <p>We reserve the right to terminate or suspend your account at our sole discretion, without notice.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Information We Collect</h6>
                    <p>We collect personal information that you provide to us, including but not limited to your name, email address, and medical information.</p>
                    
                    <h6>2. How We Use Your Information</h6>
                    <p>We use your information to provide and improve our services, communicate with you, and comply with legal obligations.</p>
                    
                    <h6>3. Information Sharing</h6>
                    <p>We may share your information with healthcare providers as necessary for your care.</p>
                    
                    <h6>4. Data Security</h6>
                    <p>We implement appropriate security measures to protect your personal information.</p>
                    
                    <h6>5. Your Rights</h6>
                    <p>You have the right to access, correct, or delete your personal information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                // Check if passwords match
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                
                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else if (confirmPassword) {
                    confirmPassword.setCustomValidity('');
                }
                
                form.classList.add('was-validated');
            }, false);
        });
        
        // Password strength validation
        const passwordInput = document.getElementById('password');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Check length
                const lengthValid = password.length >= 8;
                document.getElementById('length').className = lengthValid ? 'valid' : 'invalid';
                
                // Check uppercase
                const uppercaseValid = /[A-Z]/.test(password);
                document.getElementById('uppercase').className = uppercaseValid ? 'valid' : 'invalid';
                
                // Check lowercase
                const lowercaseValid = /[a-z]/.test(password);
                document.getElementById('lowercase').className = lowercaseValid ? 'valid' : 'invalid';
                
                // Check number
                const numberValid = /[0-9]/.test(password);
                document.getElementById('number').className = numberValid ? 'valid' : 'invalid';
            });
        }
        
        // Confirm password validation
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (confirmPasswordInput && passwordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    });
    </script>
</body>
</html>
