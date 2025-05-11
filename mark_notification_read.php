<?php
include 'db_connect.php';
session_start();

// Include notification functions if they exist
if (file_exists('helpers/notification_functions.php')) {
    include_once('helpers/notification_functions.php');
}

// Mark user-specific notifications as read
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Update user notifications to mark them as read
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Handle admin notifications
// Check if notification ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $notification_id = intval($_GET['id']);

    // Use the function if available, otherwise direct query
    if (function_exists('mark_notification_read')) {
        mark_notification_read($notification_id);
    } else {
        $query = "UPDATE admin_notifications SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
    }
} else if (isset($_GET['all']) && $_GET['all'] == 1) {
    // Mark all as read
    if (function_exists('mark_all_notifications_read')) {
        mark_all_notifications_read();
    } else {
        $conn->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    }
}

// Redirect back if requested
if (isset($_GET['return'])) {
    header("Location: " . $_GET['return']);
    exit;
}
?>