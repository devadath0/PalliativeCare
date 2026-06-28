<?php include_once 'includes/header.php'; ?>

<div class="container my-4">
    <h2>Add New Medicine</h2>
    <div class="row">
        <div class="col-md-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?module=service&action=pharmacy_add_medicine" class="needs-validation" novalidate>
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Basic Information</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Medicine Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Please provide a medicine name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="tablets">Tablets</option>
                                <option value="capsules">Capsules</option>
                                <option value="syrups">Syrups</option>
                                <option value="injections">Injections</option>
                                <option value="topical">Topical</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <div class="mb-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                        </div>
                    </div>

                    <!-- Pricing and Stock -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Pricing and Stock</h5>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit *</label>
                            <input type="text" class="form-control" id="unit" name="unit" required
                                   placeholder="e.g., tablet, bottle, box">
                            <div class="invalid-feedback">Please specify the unit.</div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price per Unit *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" required>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Initial Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   min="0" required>
                            <div class="invalid-feedback">Please enter the initial stock quantity.</div>
                        </div>

                        <div class="mb-3">
                            <label for="reorder_level" class="form-label">Reorder Level</label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                   min="0">
                            <div class="form-text">Quantity at which to reorder stock.</div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3">Additional Information</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                </div>

                                <div class="mb-3">
                                    <label for="batch_number" class="form-label">Batch Number</label>
                                    <input type="text" class="form-control" id="batch_number" name="batch_number">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requires_prescription" 
                                               name="requires_prescription" value="1">
                                        <label class="form-check-label" for="requires_prescription">
                                            Requires Prescription
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="storage_instructions" class="form-label">Storage Instructions</label>
                                    <textarea class="form-control" id="storage_instructions" 
                                              name="storage_instructions" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary">Add Medicine</button>
                    <a href="index.php?module=service&action=pharmacy_inventory" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'

    var forms = document.querySelectorAll('.needs-validation')

    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include_once 'includes/footer.php'; ?> 