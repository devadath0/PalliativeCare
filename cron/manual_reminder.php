<?php
/**
 * Manual Reminder Trigger Page
 * This page provides a simple interface to trigger reminders manually
 */

// Basic security check
$allowed_ips = ['127.0.0.1', '::1']; // Add your server's IP address
$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips)) {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Reminders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .container {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        #result {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Send Reminders</h2>
        <p>Click the button below to send reminders for upcoming appointments and bookings.</p>
        <button onclick="sendReminders()">Send Reminders Now</button>
        <div id="result"></div>
    </div>

    <script>
        function sendReminders() {
            const button = document.querySelector('button');
            const result = document.getElementById('result');
            
            button.disabled = true;
            button.textContent = 'Sending...';
            result.innerHTML = '';
            
            fetch('trigger_reminders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        result.innerHTML = `
                            <div class="success">
                                <h3>Reminders Sent Successfully</h3>
                                <p>Timestamp: ${data.timestamp}</p>
                                <h4>Results:</h4>
                                <pre>${JSON.stringify(data.results, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        throw new Error('Failed to send reminders');
                    }
                })
                .catch(error => {
                    result.innerHTML = `
                        <div class="error">
                            <h3>Error</h3>
                            <p>Failed to send reminders: ${error.message}</p>
                        </div>
                    `;
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Send Reminders Now';
                });
        }
    </script>
</body>
</html> 