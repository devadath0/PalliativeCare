<?php
/**
 * Order Medicine View
 * Palliative Care System
 */

// Set page title
$page_title = 'Order Medicine';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Debug data
error_log("Order Medicine View Data: " . print_r(get_defined_vars(), true));

// Initialize variables if not set
if (!isset($prescriptions)) {
    $prescriptions = [];
}
if (!isset($patient)) {
    $patient = [];
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $page_title; ?></h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Medicine</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?module=patient&action=process_order_medicine" method="post" id="orderForm">
                        <div class="mb-3">
                            <label for="pharmacy_id" class="form-label">Select Pharmacy <span class="text-danger">*</span></label>
                            <select class="form-select" id="pharmacy_id" name="pharmacy_id" required>
                                <option value="">-- Select Pharmacy --</option>
                                <?php foreach ($pharmacies as $pharmacy): ?>
                                    <option value="<?php echo $pharmacy['id']; ?>" 
                                            data-delivery="<?php echo $pharmacy['delivery_available']; ?>"
                                            <?php echo (isset($_SESSION['form_data']['pharmacy_id']) && $_SESSION['form_data']['pharmacy_id'] == $pharmacy['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pharmacy['name']); ?> 
                                        (<?php echo htmlspecialchars($pharmacy['address']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="pharmacyDetails" class="mt-2 d-none">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="pharmacy-name"></h6>
                                        <p class="pharmacy-address mb-1"></p>
                                        <p class="pharmacy-phone mb-1"></p>
                                        <p class="pharmacy-hours mb-1"></p>
                                        <p class="pharmacy-delivery mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prescription_id" class="form-label">Based on Prescription (Optional)</label>
                            <select class="form-select" id="prescription_id" name="prescription_id">
                                <option value="">-- No Prescription --</option>
                                <?php foreach ($prescriptions as $prescription): ?>
                                    <option value="<?php echo $prescription['id']; ?>"
                                            <?php echo (isset($_SESSION['form_data']['prescription_id']) && $_SESSION['form_data']['prescription_id'] == $prescription['id']) ? 'selected' : ''; ?>>
                                        <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?> - 
                                        Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="prescriptionDetails" class="mb-3 d-none">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Prescription Details</h6>
                                    <div id="prescriptionContent"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Medicines</label>
                            <div id="medicines">
                                <div class="medicine-item row mb-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="medicine[]" placeholder="Medicine name">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" min="1" value="1">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="price[]" placeholder="Price" min="0" step="0.01" value="0.00" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control total-price" readonly value="$0.00">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-medicine" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addMedicine" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-plus"></i> Add Medicine
                            </button>
                            <div class="text-end mt-3">
                                <strong>Grand Total: <span id="grandTotal">$0.00</span></strong>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="delivery_requested" name="delivery_requested" value="1"
                                   <?php echo (isset($_SESSION['form_data']['delivery_requested'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="delivery_requested">Request Delivery</label>
                        </div>
                        
                        <div id="deliveryAddressGroup" class="mb-3 <?php echo (isset($_SESSION['form_data']['delivery_requested'])) ? '' : 'd-none'; ?>">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3"><?php echo htmlspecialchars($_SESSION['form_data']['delivery_address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($_SESSION['form_data']['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($previous_orders)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Previous Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Pharmacy</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($previous_orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($order['pharmacy_name']); ?></td>
                                            <td>
                                                <?php if ($order['order_status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($order['order_status'] == 'processing'): ?>
                                                    <span class="badge bg-info">Processing</span>
                                                <?php elseif ($order['order_status'] == 'shipped'): ?>
                                                    <span class="badge bg-primary">Shipped</span>
                                                <?php elseif ($order['order_status'] == 'delivered'): ?>
                                                    <span class="badge bg-success">Delivered</span>
                                                <?php elseif ($order['order_status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=patient&action=view_medicine_order&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if (in_array($order['order_status'], ['pending', 'processing'])): ?>
                                                        <a href="index.php?module=patient&action=cancel_medicine_order&id=<?php echo $order['id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to cancel this order?');">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="views/assets/js/order_medicine.js"></script>
<script src="views/assets/js/pharmacy.js"></script>

<?php 
    // Clear form data after displaying
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }
?>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?>
