<?php
/**
 * Patient Services View
 * Palliative Care System
 */

// Set page title
$page_title = 'Service Requests';

// Include header
include_once __DIR__ . '/../../views/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Service Requests</h2>
        <p class="lead">Request and manage medical transport, equipment, and medicine delivery services</p>
    </div>
    <div class="col-md-4 text-right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newServiceModal">
            <i class="fas fa-plus"></i> New Service Request
        </button>
    </div>
</div>

<!-- Service Categories -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-ambulance fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Medical Transport</h5>
                <p class="card-text">
                    Book transportation for medical appointments, treatments, or hospital visits.
                </p>
                <button type="button" class="btn btn-outline-primary" 
                        onclick="preSelectService('transport')" 
                        data-toggle="modal" 
                        data-target="#newServiceModal">
                    Book Transport
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-wheelchair fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Medical Equipment</h5>
                <p class="card-text">
                    Request medical equipment rentals or purchases for home care needs.
                </p>
                <button type="button" class="btn btn-outline-warning" 
                        onclick="preSelectService('equipment')" 
                        data-toggle="modal" 
                        data-target="#newServiceModal">
                    Request Equipment
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-pills fa-3x text-success mb-3"></i>
                <h5 class="card-title">Medicine Delivery</h5>
                <p class="card-text">
                    Get your prescribed medications delivered to your doorstep.
                </p>
                <button type="button" class="btn btn-outline-success" 
                        onclick="preSelectService('medicine')" 
                        data-toggle="modal" 
                        data-target="#newServiceModal">
                    Order Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Active Service Requests -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Active Service Requests</h5>
    </div>
    <div class="card-body">
        <?php
        $active_found = false;
        foreach ($service_requests as $request):
            if ($request['status'] != 'completed' && $request['status'] != 'cancelled'):
                $active_found = true;
        ?>
            <div class="service-request mb-4 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">
                            <?php 
                            $icon = '';
                            switch ($request['service_type']) {
                                case 'transport':
                                    $icon = 'fa-ambulance text-primary';
                                    break;
                                case 'equipment':
                                    $icon = 'fa-wheelchair text-warning';
                                    break;
                                case 'medicine':
                                    $icon = 'fa-pills text-success';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?> mr-2"></i>
                            <?php echo ucfirst($request['service_type']); ?> Service
                        </h5>
                        <p class="mb-1">
                            <strong>Provider:</strong> <?php echo htmlspecialchars($request['provider_name']); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-<?php 
                            switch($request['status']) {
                                case 'confirmed':
                                    echo 'success';
                                    break;
                                case 'pending':
                                    echo 'warning';
                                    break;
                                default:
                                    echo 'info';
                            }
                        ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="service-details mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="far fa-calendar"></i>
                                <strong>Requested Date:</strong>
                                <?php echo date('F j, Y', strtotime($request['date'])); ?>
                            </p>
                            <p class="mb-2">
                                <i class="far fa-clock"></i>
                                <strong>Time:</strong>
                                <?php echo date('g:i A', strtotime($request['time'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($request['notes'])): ?>
                                <p class="mb-2">
                                    <i class="fas fa-comment"></i>
                                    <strong>Notes:</strong>
                                    <?php echo htmlspecialchars($request['notes']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($request['status'] == 'pending'): ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-danger" 
                                onclick="cancelRequest(<?php echo $request['id']; ?>)">
                            <i class="fas fa-times"></i> Cancel Request
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php 
            endif;
        endforeach;
        
        if (!$active_found):
        ?>
            <div class="text-center text-muted py-4">
                <p>No active service requests at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Service Request History -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Request History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($service_requests as $request): ?>
                        <tr>
                            <td>
                                <?php echo date('M j, Y', strtotime($request['date'])); ?>
                                <small class="d-block text-muted">
                                    <?php echo date('g:i A', strtotime($request['time'])); ?>
                                </small>
                            </td>
                            <td>
                                <?php 
                                $icon = '';
                                switch ($request['service_type']) {
                                    case 'transport':
                                        $icon = 'fa-ambulance text-primary';
                                        break;
                                    case 'equipment':
                                        $icon = 'fa-wheelchair text-warning';
                                        break;
                                    case 'medicine':
                                        $icon = 'fa-pills text-success';
                                        break;
                                }
                                ?>
                                <i class="fas <?php echo $icon; ?> mr-1"></i>
                                <?php echo ucfirst($request['service_type']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($request['provider_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    switch($request['status']) {
                                        case 'confirmed':
                                        case 'completed':
                                            echo 'success';
                                            break;
                                        case 'pending':
                                            echo 'warning';
                                            break;
                                        case 'cancelled':
                                            echo 'danger';
                                            break;
                                        default:
                                            echo 'secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="viewDetails(<?php echo $request['id']; ?>)"
                                        data-toggle="tooltip" 
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($request['status'] == 'completed'): ?>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="bookAgain(<?php echo $request['id']; ?>)"
                                            data-toggle="tooltip" 
                                            title="Book Again">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Service Request Modal -->
<div class="modal fade" id="newServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Service Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="index.php?module=patient&action=services" method="post" class="needs-validation" novalidate>
                <div class="modal-body">
                    <!-- Service Type -->
                    <div class="form-group">
                        <label for="service_type">Service Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="service_type" name="service_type" required>
                            <option value="">Select a service...</option>
                            <option value="transport">Medical Transport</option>
                            <option value="equipment">Medical Equipment</option>
                            <option value="medicine">Medicine Delivery</option>
                        </select>
                        <div class="invalid-feedback">Please select a service type.</div>
                    </div>

                    <!-- Service Provider -->
                    <div class="form-group">
                        <label for="provider_id">Service Provider <span class="text-danger">*</span></label>
                        <select class="form-control" id="provider_id" name="provider_id" required>
                            <option value="">Select a provider...</option>
                            <?php foreach ($providers as $provider): ?>
                                <option value="<?php echo $provider['id']; ?>" 
                                        data-type="<?php echo $provider['service_type']; ?>">
                                    <?php echo htmlspecialchars($provider['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a service provider.</div>
                    </div>

                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requested_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="requested_date" name="requested_date"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback">Please select a valid date.</div>
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="requested_time">Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="requested_time" name="requested_time" required>
                                <div class="invalid-feedback">Please select a valid time.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Any special requirements or instructions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Service Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="serviceDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Pre-select service type
function preSelectService(type) {
    $('#service_type').val(type).trigger('change');
}

// Filter providers based on service type
$('#service_type').change(function() {
    var selectedType = $(this).val();
    var $providerSelect = $('#provider_id');
    
    $providerSelect.find('option').show();
    if (selectedType) {
        $providerSelect.find('option').not('[data-type="' + selectedType + '"]').hide();
    }
    $providerSelect.val('');
});

// Cancel service request
function cancelRequest(id) {
    if (confirm('Are you sure you want to cancel this service request?')) {
        window.location.href = 'index.php?module=patient&action=cancel_service&id=' + id;
    }
}

// View service request details
function viewDetails(id) {
    // TODO: Load service details via AJAX
    $('#viewDetailsModal').modal('show');
}

// Book service again
function bookAgain(id) {
    // TODO: Pre-fill form with previous request details
    $('#newServiceModal').modal('show');
}

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../../views/includes/footer.php';
?>
