<?php
// Update service provider status
require_once 'config/database.php';
$db = Database::getInstance();
$stmt = $db->prepare('UPDATE service_providers SET status=? WHERE service_type=?');
$stmt->execute(['active', 'transportation']);

echo "Transportation provider status updated to active\n";
?> 