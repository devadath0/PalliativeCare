<?php
/**
 * Patient Health Records View
 * Palliative Care System
 */

// Set page title
$page_title = 'Health Records';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Health Records</h2>
            <p class="lead">View your medical history and health information</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=patient&action=dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Patient Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($patient['name'] ?? 'N/A') ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?? 'N/A') ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date of Birth:</strong> <?= !empty($patient['dob']) ? date('F j, Y', strtotime($patient['dob'])) : 'N/A' ?></p>
                            <p><strong>Gender:</strong> <?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'N/A' ?></p>
                            <p><strong>Blood Group:</strong> <?= htmlspecialchars($patient['blood_group'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Medical History</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($patient['medical_history'])): ?>
                        <div class="mb-4">
                            <h6 class="text-muted">General Medical History</h6>
                            <p><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No general medical history information available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Medical History Records</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($medical_records)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Condition</th>
                                        <th>Notes</th>
                                        <th>Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medical_records as $record): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($record['recorded_date'])) ?></td>
                                            <td><?= htmlspecialchars($record['condition']) ?></td>
                                            <td><?= nl2br(htmlspecialchars($record['notes'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($record['recorded_by_name'] ?? 'System') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No medical history records found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?> 