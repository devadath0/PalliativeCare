<?php
/**
 * Book Cab View
 * Palliative Care System
 */

// Set page title
$page_title = 'Book Cab';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Debug data
error_log("Book Cab View Data: " . print_r(get_defined_vars(), true));

// Initialize variables if not set
if (!isset($hospitals)) {
    $hospitals = [];
}
if (!isset($patient)) {
    $patient = [];
}
if (!isset($providers)) {
    $providers = [];
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Book a Cab</h2>
            <p class="lead">Arrange transportation for your hospital visit</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="index.php?module=patient&action=book_cab" method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pickup_address" class="form-label">Pickup Address</label>
                        <textarea name="pickup_address" id="pickup_address" class="form-control" rows="3" required><?= $patient['address'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="destination" class="form-label">Destination</label>
                        <select name="destination" id="destination" class="form-select" required>
                            <option value="">-- Select destination --</option>
                            <?php if (!empty($hospitals)): ?>
                                <?php foreach ($hospitals as $hospital): ?>
                                    <option value="<?= $hospital['address'] ?>"><?= $hospital['name'] ?> - <?= $hospital['address'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="other">Other (specify in special requirements)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="pickup_time" class="form-label">Pickup Time</label>
                        <input type="time" name="pickup_time" id="pickup_time" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="provider_id" class="form-label">Transportation Provider</label>
                    <select name="provider_id" id="provider_id" class="form-select" required>
                        <option value="">-- Select a service provider --</option>
                        <?php if (!empty($providers)): ?>
                            <?php foreach ($providers as $provider): ?>
                                <option value="<?= $provider['id'] ?>"><?= $provider['company_name'] ?> - <?= $provider['service_area'] ?? 'All Areas' ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">Please select a transportation provider that serves your area.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cab Type</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cab_type" id="cab_standard" value="standard" checked>
                                <label class="form-check-label" for="cab_standard">
                                    Standard
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cab_type" id="cab_wheelchair" value="wheelchair">
                                <label class="form-check-label" for="cab_wheelchair">
                                    Wheelchair Accessible
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cab_type" id="cab_stretcher" value="stretcher">
                                <label class="form-check-label" for="cab_stretcher">
                                    Stretcher Accessible
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="special_requirements" class="form-label">Special Requirements (Optional)</label>
                    <textarea name="special_requirements" id="special_requirements" class="form-control" rows="3" 
                              placeholder="Any special needs or assistance required"></textarea>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Cab booking is subject to availability. You will receive a confirmation call once your booking is processed.
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-taxi"></i> Book Cab
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($providers)): ?>
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Available Transportation Providers</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($providers as $provider): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($provider['company_name']) ?></h5>
                            <p class="card-text mb-1"><strong>Service Area:</strong> <?= htmlspecialchars($provider['service_area'] ?? 'All Areas') ?></p>
                            <p class="card-text mb-1"><strong>Phone:</strong> <?= htmlspecialchars($provider['phone'] ?? 'N/A') ?></p>
                            <?php if (!empty($provider['operating_hours'])): ?>
                            <p class="card-text mb-1"><strong>Hours:</strong> <?= htmlspecialchars($provider['operating_hours']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Cab Service Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h5>24/7 Service</h5>
                        <p class="text-muted">Our cab service is available round the clock for your convenience.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center">
                        <i class="fas fa-wheelchair fa-3x text-primary mb-3"></i>
                        <h5>Accessibility</h5>
                        <p class="text-muted">Specially equipped vehicles for patients with mobility challenges.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                        <h5>Trained Staff</h5>
                        <p class="text-muted">Our drivers are trained in basic medical assistance and patient handling.</p>
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
