<?php
include('includes/db.php');

// Update all notifications to mark them as read
$query = "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0";
if ($mysqli->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
}
?>