<?php
/**
 * View Prescription Details
 * Palliative Care System
 */

// Set page title
$page_title = 'Prescription Details';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';

// Check if prescription exists
if (empty($prescription)) {
    $_SESSION['error'] = "Prescription not found.";
    header("Location: index.php?module=patient&action=prescriptions");
    exit;
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Prescription Details</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=prescriptions" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Prescriptions
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Prescription from Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></h5>
                <span class="text-muted"><?php echo date('F j, Y', strtotime($prescription['created_at'])); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Diagnosis</h6>
                    <p><?php echo htmlspecialchars($prescription['diagnosis']); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($prescription['notes'])): ?>
                        <h6 class="text-muted">Notes</h6>
                        <p><?php echo htmlspecialchars($prescription['notes']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($medications)): ?>
                <h6 class="text-muted mt-4">Medications</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medications as $medication): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($medication['medicine']); ?></td>
                                    <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                    <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                    <td><?php echo htmlspecialchars($medication['duration']); ?></td>
                                    <td><?php echo htmlspecialchars($medication['instructions'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> No medications have been prescribed.
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary" onclick="window.print();">
                <i class="fas fa-print"></i> Print Prescription
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?> 