<?php
/**
 * Chat with Patient View
 * Palliative Care System
 */

// Set page title
$page_title = 'Chat with ' . htmlspecialchars($appointment['patient_name']);

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Chat with <?php echo htmlspecialchars($appointment['patient_name']); ?></h2>
            <p class="text-muted">
                Appointment: <?php echo date('F j, Y g:i A', strtotime($appointment['appointment_date'])); ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?module=doctor&action=appointments" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comment-alt text-primary me-2"></i>
                        Conversation
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- Chat messages container with fixed height and scroll -->
                    <div class="chat-messages p-3" id="chatMessages" style="height: 400px; overflow-y: auto;">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isDoctor = $msg['sender_type'] === 'doctor'; ?>
                                <div class="message-container mb-3 <?php echo $isDoctor ? 'text-end' : ''; ?>">
                                    <div class="message d-inline-block p-3 rounded 
                                        <?php echo $isDoctor ? 'bg-primary text-white' : 'bg-light'; ?>"
                                        style="max-width: 75%;">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                        <div class="message-time text-<?php echo $isDoctor ? 'white' : 'muted'; ?> small mt-1">
                                            <?php echo date('M d, g:i a', strtotime($msg['created_at'])); ?>
                                            <?php if ($msg['is_read']): ?>
                                                <i class="fas fa-check-double ms-1" 
                                                   title="Read on <?php echo date('M d, g:i a', strtotime($msg['read_at'])); ?>"></i>
                                            <?php elseif ($isDoctor): ?>
                                                <i class="fas fa-check ms-1" title="Sent"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Message input form -->
                    <div class="chat-input p-3 border-top">
                        <form id="chatForm" method="post" action="index.php?module=doctor&action=send_message">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                            <div class="input-group">
                                <textarea class="form-control" name="message" placeholder="Type your message..." rows="2" required id="messageInput"></textarea>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Appointment Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Patient:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($appointment['patient_name']); ?></dd>
                            
                            <dt class="col-sm-4">Date:</dt>
                            <dd class="col-sm-8"><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></dd>
                            
                            <dt class="col-sm-4">Time:</dt>
                            <dd class="col-sm-8"><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-success">Confirmed</span>
                            </dd>
                            
                            <dt class="col-sm-4">Reason:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($appointment['reason'] ?? 'Not specified'); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to bottom of messages
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Submit form via AJAX
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!messageInput.value.trim()) {
            return;
        }
        
        // Create form data
        const formData = new FormData(chatForm);
        
        // Send AJAX request
        fetch('index.php?module=doctor&action=send_message', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                messageInput.value = '';
                
                // Reload chat messages
                location.reload();
            } else {
                alert('Error sending message: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending your message.');
        });
    });
    
    // Auto-refresh messages every 10 seconds
    setInterval(function() {
        location.reload();
    }, 10000);
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php';
?>