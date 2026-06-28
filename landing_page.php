<?php
/**
 * Landing Page
 * Palliative Care System
 */

// Ensure this file is not accessed directly
if (!defined('SITE_URL')) {
    require_once 'config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add custom styles -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('images/slider/slider_2.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0;
            text-align: center;
        }
        .service-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .service-card:hover {
            transform: translateY(-10px);
        }
        .service-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        .gallery-img {
            height: 200px;
            object-fit: cover;
            margin-bottom: 20px;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .gallery-img:hover {
            transform: scale(1.05);
        }
        .why-us {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('images/why.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
        }
        .user-type-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        .user-type-card:hover {
            transform: translateY(-10px);
        }
        .user-type-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>images/logo.png" height="40" alt="Logo" class="me-2">
                <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            Login
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=patient">
                                <i class="fas fa-user-injured me-2"></i> Patient Login
                            </a>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=doctor">
                                <i class="fas fa-user-md me-2"></i> Doctor Login
                            </a>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=service">
                                <i class="fas fa-building me-2"></i> Service Provider Login
                            </a>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=admin">
                                <i class="fas fa-user-shield me-2"></i> Admin Login
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-3 mb-4">Compassionate Care for Every Journey</h1>
            <p class="lead mb-5">Providing comprehensive palliative care services with dignity and respect</p>
            <a href="#contact" class="btn btn-primary btn-lg me-3">Contact Us</a>
            <a href="#services" class="btn btn-outline-light btn-lg">Our Services</a>
        </div>
    </section>

    <!-- User Types Section -->
    <section class="container my-5" id="about">
        <h2 class="text-center mb-5">Who We Serve</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="user-type-card shadow">
                    <img src="<?php echo SITE_URL; ?>images/patient.jpg" alt="Patient" class="user-type-img">
                    <h3>Patients</h3>
                    <p>Access your medical records, appointments, and care plans all in one place.</p>
                    <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=patient" class="btn btn-primary">Patient Portal</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="user-type-card shadow">
                    <img src="<?php echo SITE_URL; ?>images/doctor.jpg" alt="Doctor" class="user-type-img">
                    <h3>Doctors</h3>
                    <p>Manage your patients, schedules, and medical records efficiently.</p>
                    <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=doctor" class="btn btn-primary">Doctor Portal</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="user-type-card shadow">
                    <img src="<?php echo SITE_URL; ?>images/admin.jpg" alt="Service Provider" class="user-type-img">
                    <h3>Service Providers</h3>
                    <p>Coordinate services, manage resources, and track care delivery.</p>
                    <a href="<?php echo SITE_URL; ?>index.php?module=auth&action=login&type=service" class="btn btn-primary">Provider Portal</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="bg-light py-5" id="services">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="service-card text-center p-4">
                        <i class="fas fa-user-md service-icon"></i>
                        <h4>Medical Care</h4>
                        <p>Expert medical care from experienced healthcare professionals</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card text-center p-4">
                        <i class="fas fa-hands-helping service-icon"></i>
                        <h4>Support Services</h4>
                        <p>Comprehensive support for patients and their families</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card text-center p-4">
                        <i class="fas fa-home service-icon"></i>
                        <h4>Home Care</h4>
                        <p>Professional care services in the comfort of your home</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us" id="why">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">Why Choose Us?</h2>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> 24/7 Professional Support</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Experienced Healthcare Team</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Personalized Care Plans</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Modern Healthcare Facilities</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Family-Centered Approach</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="container my-5" id="gallery">
        <h2 class="text-center mb-5">Our Facility</h2>
        <div class="row">
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-md-4">
                    <img src="<?php echo SITE_URL; ?>images/gallery/gallery_<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>.jpg" 
                         alt="Gallery Image <?php echo $i; ?>" class="img-fluid gallery-img">
                </div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="bg-light py-5" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mb-4">Contact Us</h2>
                    <form action="<?php echo SITE_URL; ?>index.php?module=contact&action=submit" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" autocomplete="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" autocomplete="off" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h2 class="mb-4">Location</h2>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Healthcare Street, Medical District</p>
                    <p><i class="fas fa-phone me-2"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope me-2"></i> info@palliativecare.com</p>
                    <div class="mt-4">
                        <h4>Emergency Contact</h4>
                        <p class="text-danger"><i class="fas fa-phone-alt me-2"></i> Emergency: (123) 456-7899</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Providing compassionate palliative care services to improve quality of life for patients and their families.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#about" class="text-white">About</a></li>
                        <li><a href="#services" class="text-white">Services</a></li>
                        <li><a href="#gallery" class="text-white">Gallery</a></li>
                        <li><a href="#contact" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo SITE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const targetElement = document.querySelector(href);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Initialize Bootstrap dropdowns
    $(document).ready(function() {
        $('.dropdown-toggle').dropdown();
    });
    </script>
</body>
</html> 