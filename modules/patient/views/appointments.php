<?php
/**
 * Patient Appointments View
 * Palliative Care System
 */

// Set page title
$page_title = 'My Appointments';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Appointments</h2>
        <a href="index.php?module=patient&action=book_appointment" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Book New Appointment
        </a>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You don't have any appointments yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($appointment['status']) {
                                        'confirmed' => 'success',
                                        'pending' => 'warning',
                                        'cancelled' => 'danger',
                                        'completed' => 'info',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?module=patient&action=view_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (isset($appointment['payment_status']) && $appointment['payment_status'] == 'pending'): ?>
                                    <a href="index.php?module=patient&action=payment&type=appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                                    <a href="index.php?module=patient&action=cancel_appointment&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($appointment['status'] === 'confirmed'): ?>
                                    <a href="index.php?module=patient&action=chat_with_doctor&appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-comment-medical"></i> Chat
                                        <?php if (isset($appointment['unread_count']) && $appointment['unread_count'] > 0): ?>
                                            <span class="badge bg-danger"><?php echo $appointment['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../../views/includes/footer.php'; ?>
