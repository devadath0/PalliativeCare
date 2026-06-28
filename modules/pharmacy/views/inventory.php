<?php include_once 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Medicine Inventory</h1>
                <a href="index.php?module=pharmacy&action=add_medicine" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Medicine
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="module" value="pharmacy">
                        <input type="hidden" name="action" value="inventory">
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                   placeholder="Search medicines...">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <option value="tablets" <?php echo ($_GET['category'] ?? '') === 'tablets' ? 'selected' : ''; ?>>Tablets</option>
                                <option value="capsules" <?php echo ($_GET['category'] ?? '') === 'capsules' ? 'selected' : ''; ?>>Capsules</option>
                                <option value="syrups" <?php echo ($_GET['category'] ?? '') === 'syrups' ? 'selected' : ''; ?>>Syrups</option>
                                <option value="injections" <?php echo ($_GET['category'] ?? '') === 'injections' ? 'selected' : ''; ?>>Injections</option>
                                <option value="topical" <?php echo ($_GET['category'] ?? '') === 'topical' ? 'selected' : ''; ?>>Topical</option>
                                <option value="other" <?php echo ($_GET['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="stock" class="form-label">Stock Status</label>
                            <select class="form-select" id="stock" name="stock">
                                <option value="">All</option>
                                <option value="low" <?php echo ($_GET['stock'] ?? '') === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                                <option value="out" <?php echo ($_GET['stock'] ?? '') === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                                <option value="available" <?php echo ($_GET['stock'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="prescription" class="form-label">Prescription</label>
                            <select class="form-select" id="prescription" name="prescription">
                                <option value="">All</option>
                                <option value="1" <?php echo ($_GET['prescription'] ?? '') === '1' ? 'selected' : ''; ?>>Required</option>
                                <option value="0" <?php echo ($_GET['prescription'] ?? '') === '0' ? 'selected' : ''; ?>>Not Required</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Prescription</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($medicines)): ?>
                                    <?php foreach ($medicines as $medicine): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($medicine['id']); ?></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong><?php echo htmlspecialchars($medicine['name']); ?></strong>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr($medicine['description'], 0, 50)) . 
                                                            (strlen($medicine['description']) > 50 ? '...' : ''); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td><?php echo ucfirst(htmlspecialchars($medicine['category'])); ?></td>
                                            <td><?php echo htmlspecialchars($medicine['unit']); ?></td>
                                            <td>$<?php echo number_format($medicine['price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $medicine['stock_quantity'] <= 0 ? 'danger' : 
                                                        ($medicine['stock_quantity'] <= 10 ? 'warning' : 'success'); 
                                                ?>">
                                                    <?php echo $medicine['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $medicine['requires_prescription'] ? 'info' : 'secondary'; ?>">
                                                    <?php echo $medicine['requires_prescription'] ? 'Required' : 'Not Required'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($medicine['status']) {
                                                        'active' => 'success',
                                                        'discontinued' => 'danger',
                                                        default => 'warning'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst(htmlspecialchars($medicine['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=pharmacy&action=edit_medicine&id=<?php echo $medicine['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-success" title="Update Stock"
                                                            onclick="updateStock(<?php echo $medicine['id']; ?>)">
                                                        <i class="fas fa-boxes"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No medicines found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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