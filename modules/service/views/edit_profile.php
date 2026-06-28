<?php require_once 'views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit Profile</h3>
                    <a href="index.php?module=service&action=profile" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="index.php?module=service&action=edit_profile" method="POST" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <div class="profile-image-container mb-3">
                                        <?php if (!empty($service['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($service['profile_image']); ?>" alt="Profile Image" class="img-fluid rounded-circle profile-image" style="width: 150px; height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="text-center p-3 bg-light rounded-circle mx-auto" style="width: 150px; height: 150px;">
                                                <i class="fas fa-building fa-4x text-secondary mt-3"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profile_image" class="form-label">Profile Image</label>
                                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg, image/png, image/gif">
                                        <small class="text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($service['email'] ?? ''); ?>" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($service['company_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($service['phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_type" class="form-label">Service Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value="">Select Service Type</option>
                                        <option value="Pharmacy" <?php echo (isset($service['service_type']) && $service['service_type'] == 'Pharmacy') ? 'selected' : ''; ?>>Pharmacy</option>
                                        <option value="Ambulance" <?php echo (isset($service['service_type']) && $service['service_type'] == 'Ambulance') ? 'selected' : ''; ?>>Ambulance</option>
                                        <option value="Medical Equipment" <?php echo (isset($service['service_type']) && $service['service_type'] == 'Medical Equipment') ? 'selected' : ''; ?>>Medical Equipment</option>
                                        <option value="Home Care" <?php echo (isset($service['service_type']) && $service['service_type'] == 'Home Care') ? 'selected' : ''; ?>>Home Care</option>
                                        <option value="Transportation" <?php echo (isset($service['service_type']) && $service['service_type'] == 'Transportation') ? 'selected' : ''; ?>>Transportation</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($service['license_number'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="operating_hours" class="form-label">Operating Hours</label>
                                    <input type="text" class="form-control" id="operating_hours" name="operating_hours" value="<?php echo htmlspecialchars($service['operating_hours'] ?? ''); ?>" placeholder="e.g., Mon-Fri: 9AM-5PM, Sat: 10AM-2PM">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($service['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="service_area" class="form-label">Service Area</label>
                                    <input type="text" class="form-control" id="service_area" name="service_area" value="<?php echo htmlspecialchars($service['service_area'] ?? ''); ?>" placeholder="e.g., Downtown, North Side, etc.">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php?module=service&action=profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?> 