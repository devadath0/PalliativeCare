<?php
/**
 * Patient Prescriptions View
 * Palliative Care System
 */

// Set page title
$page_title = 'My Prescriptions';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">My Prescriptions</h2>

    <?php if (empty($prescriptions)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You don't have any prescriptions yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($prescriptions as $prescription): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?></h5>
                            <span class="text-muted small">
                                <?php echo date('M d, Y', strtotime($prescription['created_at'])); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($prescription['diagnosis'])): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted">Diagnosis:</h6>
                                    <p><?php echo htmlspecialchars($prescription['diagnosis']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($prescription['notes'])): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted">Notes:</h6>
                                    <p><?php echo htmlspecialchars($prescription['notes']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="index.php?module=patient&action=view_prescription&id=<?php echo $prescription['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?>
