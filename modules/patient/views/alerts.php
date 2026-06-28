<?php
/**
 * Patient Alerts View
 * Palliative Care System
 */

require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center patient-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        My Alerts
                        <?php if ($unread_count > 0): ?>
                            <span class="badge badge-pill badge-danger ml-2"><?= $unread_count ?> unread</span>
                        <?php endif; ?>
                    </h6>
                    <?php if (count($alerts) > 0): ?>
                        <a href="index.php?module=patient&action=mark_all_read" class="btn btn-sm btn-outline-primary">Mark All Read</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($alerts) > 0): ?>
                        <div class="list-group alert-list">
                            <?php foreach ($alerts as $alert): ?>
                                <div class="list-group-item list-group-item-action <?= $alert['is_read'] ? '' : 'border-left-primary' ?>" 
                                     id="alert-<?= $alert['id'] ?>">
                                    <div class="alert-item-header">
                                        <h5 class="<?= $alert['is_read'] ? '' : 'font-weight-bold' ?>"><?= htmlspecialchars($alert['title']) ?></h5>
                                        <span class="alert-timestamp"><?= date('M j, Y g:i a', strtotime($alert['created_at'])) ?></span>
                                    </div>
                                    <p class="alert-message"><?= htmlspecialchars($alert['message']) ?></p>
                                    <div class="alert-footer">
                                        <div class="alert-type">
                                            <?php 
                                            // Get appropriate icon based on alert type
                                            $icon = 'info-circle';
                                            switch ($alert['alert_type']) {
                                                case 'appointment':
                                                    $icon = 'calendar-check';
                                                    break;
                                                case 'medicine_order':
                                                    $icon = 'pills';
                                                    break;
                                                case 'cab_booking':
                                                    $icon = 'taxi';
                                                    break;
                                                case 'prescription':
                                                    $icon = 'prescription';
                                                    break;
                                                case 'payment':
                                                    $icon = 'credit-card';
                                                    break;
                                                case 'patient_issue':
                                                    $icon = 'exclamation-circle';
                                                    break;
                                            }
                                            ?>
                                            <i class="fas fa-<?= $icon ?>"></i> 
                                            <?= ucfirst(str_replace('_', ' ', $alert['alert_type'])) ?>
                                        </div>
                                        <div class="alert-actions">
                                            <?php if (!$alert['is_read']): ?>
                                                <a href="index.php?module=patient&action=mark_alert_read&id=<?= $alert['id'] ?>" 
                                                   class="btn btn-sm btn-outline-secondary mark-read-btn"
                                                   data-alert-id="<?= $alert['id'] ?>">
                                                    <i class="fas fa-check"></i> Mark as Read
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($alert['reference_id'] && $alert['alert_type']): ?>
                                                <?php
                                                // Link to appropriate page based on alert type
                                                $link = '#';
                                                switch ($alert['alert_type']) {
                                                    case 'appointment':
                                                        $link = "index.php?module=patient&action=view_appointment&id={$alert['reference_id']}";
                                                        break;
                                                    case 'medicine_order':
                                                        $link = "index.php?module=patient&action=view_medicine_order&id={$alert['reference_id']}";
                                                        break;
                                                    case 'cab_booking':
                                                        $link = "index.php?module=patient&action=view_cab_booking&id={$alert['reference_id']}";
                                                        break;
                                                    case 'prescription':
                                                        $link = "index.php?module=patient&action=view_prescription&id={$alert['reference_id']}";
                                                        break;
                                                    case 'payment':
                                                        $link = "index.php?module=patient&action=payment_history";
                                                        break;
                                                    case 'patient_issue':
                                                        $link = "index.php?module=patient&action=view_issue&id={$alert['reference_id']}";
                                                        break;
                                                    case 'system':
                                                        if (strpos($alert['title'], 'Response to your issue') !== false) {
                                                            // Extract issue ID from the title if possible
                                                            preg_match('/#(\d+)/', $alert['title'], $matches);
                                                            if (!empty($matches[1])) {
                                                                $link = "index.php?module=patient&action=view_issue&id={$matches[1]}";
                                                            }
                                                        }
                                                        break;
                                                }
                                                ?>
                                                <a href="<?= $link ?>" class="btn btn-sm btn-primary ml-2">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert-empty">
                            <i class="fas fa-bell"></i>
                            <p class="lead">You don't have any alerts at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark as read functionality with AJAX
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const alertId = this.dataset.alertId;
            const url = `index.php?module=patient&action=mark_alert_read&id=${alertId}&ajax=1`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI to show alert is read
                        const alertElement = document.getElementById(`alert-${alertId}`);
                        alertElement.classList.remove('border-left-primary');
                        alertElement.querySelector('h5').classList.remove('font-weight-bold');
                        this.remove();
                        
                        // Update unread count in badge
                        const badge = document.querySelector('.badge-danger');
                        if (badge) {
                            let count = parseInt(badge.textContent);
                            count--;
                            if (count > 0) {
                                badge.textContent = `${count} unread`;
                            } else {
                                badge.remove();
                            }
                        }
                    }
                })
                .catch(error => console.error('Error marking alert as read:', error));
        });
    });
});
</script> 