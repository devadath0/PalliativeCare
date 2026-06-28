<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <main class="col-md-12 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?module=doctor&action=prescriptions" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Prescriptions
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
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Patient: <?php echo htmlspecialchars($prescription['patient_name']); ?></h5>
                </div>
                <div class="card-body">
                    <form action="index.php?module=doctor&action=update_prescription" method="post" id="prescriptionForm">
                        <input type="hidden" name="prescription_id" value="<?php echo $prescription['id']; ?>">
                        <input type="hidden" name="patient_id" value="<?php echo $prescription['patient_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis/Notes</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="4" required><?php echo htmlspecialchars($prescription['diagnosis']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($prescription['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Medications</h5>
                        
                        <div id="medications">
                            <?php if (empty($items)): ?>
                                <div class="medication-item row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Medicine</label>
                                        <input type="text" class="form-control" name="medicine[]" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Dosage</label>
                                        <input type="text" class="form-control" name="dosage[]" placeholder="e.g., 500mg">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Frequency</label>
                                        <input type="text" class="form-control" name="frequency[]" placeholder="e.g., Twice daily">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Duration</label>
                                        <input type="text" class="form-control" name="duration[]" placeholder="e.g., 7 days">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Instructions</label>
                                        <input type="text" class="form-control" name="instructions[]" placeholder="e.g., After meals">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-medication mb-2" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $index => $item): ?>
                                    <div class="medication-item row mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Medicine</label>
                                            <input type="text" class="form-control" name="medicine[]" value="<?php echo htmlspecialchars($item['medicine']); ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Dosage</label>
                                            <input type="text" class="form-control" name="dosage[]" placeholder="e.g., 500mg" value="<?php echo htmlspecialchars($item['dosage'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Frequency</label>
                                            <input type="text" class="form-control" name="frequency[]" placeholder="e.g., Twice daily" value="<?php echo htmlspecialchars($item['frequency'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Duration</label>
                                            <input type="text" class="form-control" name="duration[]" placeholder="e.g., 7 days" value="<?php echo htmlspecialchars($item['duration'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Instructions</label>
                                            <input type="text" class="form-control" name="instructions[]" placeholder="e.g., After meals" value="<?php echo htmlspecialchars($item['instructions'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-medication mb-2" <?php echo (count($items) <= 1) ? 'style="display: none;"' : ''; ?>>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <button type="button" id="addMedication" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Add Medication
                            </button>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Update Prescription</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const medicationsContainer = document.getElementById('medications');
    const addMedicationBtn = document.getElementById('addMedication');
    
    // Add new medication field
    addMedicationBtn.addEventListener('click', function() {
        const medicationItems = document.querySelectorAll('.medication-item');
        
        // Show all remove buttons when we have more than one medication
        if (medicationItems.length === 1) {
            medicationItems[0].querySelector('.remove-medication').style.display = 'block';
        }
        
        const newItem = document.createElement('div');
        newItem.className = 'medication-item row mb-3';
        newItem.innerHTML = `
            <div class="col-md-3">
                <label class="form-label">Medicine</label>
                <input type="text" class="form-control" name="medicine[]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dosage</label>
                <input type="text" class="form-control" name="dosage[]" placeholder="e.g., 500mg">
            </div>
            <div class="col-md-2">
                <label class="form-label">Frequency</label>
                <input type="text" class="form-control" name="frequency[]" placeholder="e.g., Twice daily">
            </div>
            <div class="col-md-2">
                <label class="form-label">Duration</label>
                <input type="text" class="form-control" name="duration[]" placeholder="e.g., 7 days">
            </div>
            <div class="col-md-2">
                <label class="form-label">Instructions</label>
                <input type="text" class="form-control" name="instructions[]" placeholder="e.g., After meals">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-medication mb-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        medicationsContainer.appendChild(newItem);
        
        // Add event listener to the new remove button
        newItem.querySelector('.remove-medication').addEventListener('click', removeMedication);
    });
    
    // Remove medication field
    function removeMedication() {
        const medicationItems = document.querySelectorAll('.medication-item');
        
        if (medicationItems.length > 1) {
            this.closest('.medication-item').remove();
            
            // Hide the remove button if only one medication is left
            if (medicationItems.length === 2) {
                document.querySelector('.medication-item .remove-medication').style.display = 'none';
            }
        }
    }
    
    // Add event listeners to existing remove buttons
    document.querySelectorAll('.remove-medication').forEach(button => {
        button.addEventListener('click', removeMedication);
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>