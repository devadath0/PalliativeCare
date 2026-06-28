<?php include_once 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Medicine</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($medicine) && !empty($medicine)): ?>
                        <form method="POST" action="index.php?module=pharmacy&action=edit_medicine&id=<?php echo $medicine['id']; ?>" 
                              class="needs-validation" novalidate>
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Basic Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Medicine Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($medicine['name']); ?>" required>
                                        <div class="invalid-feedback">Please provide a medicine name.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description *</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="3" required><?php echo htmlspecialchars($medicine['description']); ?></textarea>
                                        <div class="invalid-feedback">Please provide a description.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category *</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="tablets" <?php echo $medicine['category'] === 'tablets' ? 'selected' : ''; ?>>Tablets</option>
                                            <option value="capsules" <?php echo $medicine['category'] === 'capsules' ? 'selected' : ''; ?>>Capsules</option>
                                            <option value="syrups" <?php echo $medicine['category'] === 'syrups' ? 'selected' : ''; ?>>Syrups</option>
                                            <option value="injections" <?php echo $medicine['category'] === 'injections' ? 'selected' : ''; ?>>Injections</option>
                                            <option value="topical" <?php echo $medicine['category'] === 'topical' ? 'selected' : ''; ?>>Topical</option>
                                            <option value="other" <?php echo $medicine['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a category.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="manufacturer" class="form-label">Manufacturer</label>
                                        <input type="text" class="form-control" id="manufacturer" name="manufacturer"
                                               value="<?php echo htmlspecialchars($medicine['manufacturer'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- Pricing and Stock -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">Pricing and Stock</h5>

                                    <div class="mb-3">
                                        <label for="unit" class="form-label">Unit *</label>
                                        <input type="text" class="form-control" id="unit" name="unit" required
                                               value="<?php echo htmlspecialchars($medicine['unit']); ?>"
                                               placeholder="e.g., tablet, bottle, box">
                                        <div class="invalid-feedback">Please specify the unit.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price per Unit *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   step="0.01" min="0" required
                                                   value="<?php echo number_format($medicine['price'], 2, '.', ''); ?>">
                                            <div class="invalid-feedback">Please enter a valid price.</div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="stock_quantity" class="form-label">Current Stock Quantity *</label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                               min="0" required value="<?php echo $medicine['stock_quantity']; ?>" readonly>
                                        <div class="form-text">Use the stock update button to change quantity.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reorder_level" class="form-label">Reorder Level</label>
                                        <input type="number" class="form-control" id="reorder_level" name="reorder_level" 
                                               min="0" value="<?php echo $medicine['reorder_level'] ?? ''; ?>">
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
                                                <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                                       value="<?php echo $medicine['expiry_date'] ?? ''; ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label for="batch_number" class="form-label">Batch Number</label>
                                                <input type="text" class="form-control" id="batch_number" name="batch_number"
                                                       value="<?php echo htmlspecialchars($medicine['batch_number'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="requires_prescription" 
                                                           name="requires_prescription" value="1"
                                                           <?php echo $medicine['requires_prescription'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="requires_prescription">
                                                        Requires Prescription
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="storage_instructions" class="form-label">Storage Instructions</label>
                                                <textarea class="form-control" id="storage_instructions" 
                                                          name="storage_instructions" rows="2"><?php echo htmlspecialchars($medicine['storage_instructions'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active" <?php echo $medicine['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="discontinued" <?php echo $medicine['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                            <option value="out_of_stock" <?php echo $medicine['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a status.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Update Medicine</button>
                                <button type="button" class="btn btn-success" onclick="updateStock(<?php echo $medicine['id']; ?>)">
                                    Update Stock
                                </button>
                                <a href="index.php?module=pharmacy&action=inventory" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Medicine not found. <a href="index.php?module=pharmacy&action=inventory">Return to inventory</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStockForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="medicine_id" id="medicineId">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity to Add/Remove</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                        <div class="form-text">Use positive numbers to add stock, negative to remove.</div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
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

function updateStock(medicineId) {
    document.getElementById('medicineId').value = medicineId;
    var modal = new bootstrap.Modal(document.getElementById('updateStockModal'));
    modal.show();
}

document.getElementById('updateStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Submit form via AJAX
    fetch('index.php?module=pharmacy&action=update_stock', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating stock');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating stock');
    });
});
</script>

<?php include_once 'includes/footer.php'; ?> 