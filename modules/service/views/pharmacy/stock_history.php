<?php include_once 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Stock Movement History</h1>
                <a href="index.php?module=service&action=pharmacy_inventory" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="module" value="service">
                        <input type="hidden" name="action" value="pharmacy_stock_history">
                        
                        <div class="col-md-4">
                            <label for="medicine_id" class="form-label">Filter by Medicine</label>
                            <select class="form-select" id="medicine_id" name="medicine_id">
                                <option value="">All Medicines</option>
                                <?php foreach ($medicines as $medicine): ?>
                                    <option value="<?php echo $medicine['id']; ?>" 
                                            <?php echo ($_GET['medicine_id'] ?? '') == $medicine['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($medicine['name']); ?>
                                    </option>
                                <?php endforeach; ?>
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

            <!-- Stock Movements Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Medicine</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                    <th>Notes</th>
                                    <th>Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($movements)): ?>
                                    <?php foreach ($movements as $movement): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($movement['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($movement['medicine_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $movement['movement_type'] === 'in' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($movement['movement_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $movement['quantity']; ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo ucfirst($movement['reference_type']); ?>
                                                    <?php if ($movement['reference_id']): ?>
                                                        #<?php echo $movement['reference_id']; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($movement['notes'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($movement['user_name']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No stock movements found</td>
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

<?php include_once 'includes/footer.php'; ?> 