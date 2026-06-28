<?php 
// Set current page for navbar highlighting
$current_page = 'pharmacies';
require_once 'includes/header.php'; 
?>

<div class="container-fluid">
    <div class="row">
        <!-- Removed sidebar include that was causing the error -->
        
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?module=admin&action=pharmacies" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Pharmacies
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="index.php?module=admin&action=process_add_pharmacy" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Pharmacy Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? ''); ?>">
                                <small class="text-muted">This will be used for login</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="license_number" name="license_number" required
                                       value="<?php echo htmlspecialchars($_SESSION['form_data']['license_number'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_SESSION['form_data']['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="operating_hours" class="form-label">Operating Hours</label>
                            <input type="text" class="form-control" id="operating_hours" name="operating_hours"
                                   placeholder="e.g., Mon-Fri: 9:00 AM - 6:00 PM, Sat: 10:00 AM - 4:00 PM"
                                   value="<?php echo htmlspecialchars($_SESSION['form_data']['operating_hours'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="delivery_available" name="delivery_available"
                                   <?php echo (isset($_SESSION['form_data']) && isset($_SESSION['form_data']['delivery_available'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="delivery_available">Delivery Available</label>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Add Pharmacy</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php 
                // Clear form data after displaying
                if (isset($_SESSION['form_data'])) {
                    unset($_SESSION['form_data']);
                }
            ?>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 