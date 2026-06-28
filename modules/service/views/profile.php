<?php require_once 'views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Service Provider Profile</h3>
                    <div>
                        <a href="index.php?module=service&action=dashboard" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="index.php?module=service&action=edit_profile" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-image-container mb-3">
                                <?php if (!empty($provider['profile_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($provider['profile_image']); ?>" alt="Profile Image" class="img-fluid rounded-circle profile-image" style="width: 200px; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="text-center p-3 bg-light rounded-circle mx-auto" style="width: 200px; height: 200px;">
                                        <i class="fas fa-building fa-5x text-secondary mt-4"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h4><?php echo htmlspecialchars($provider['company_name'] ?? ''); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($provider['service_type'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h5>Contact Information</h5>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($provider['email'] ?? ''); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($provider['phone'] ?? ''); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($provider['address'] ?? ''); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5>Service Details</h5>
                                    <p><strong>Service Area:</strong> <?php echo htmlspecialchars($provider['service_area'] ?? ''); ?></p>
                                    <p><strong>Operating Hours:</strong> <?php echo htmlspecialchars($provider['operating_hours'] ?? ''); ?></p>
                                    <p><strong>License Number:</strong> <?php echo htmlspecialchars($provider['license_number'] ?? ''); ?></p>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5>Description</h5>
                                    <p><?php echo nl2br(htmlspecialchars($provider['description'] ?? '')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>
