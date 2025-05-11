<?php
include('db_connect.php');

// Include notification functions if they exist
if (file_exists('helpers/notification_functions.php')) {
  include_once('helpers/notification_functions.php');
}

// Check for notification ID to delete
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $notification_id = intval($_GET['id']);

  // Delete the notification
  $query = "DELETE FROM admin_notifications WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $notification_id);
  $stmt->execute();

  // Report success/failure
  if ($stmt->affected_rows > 0) {
    $success = true;
    $message = "Notification deleted successfully";
  } else {
    $success = false;
    $message = "Failed to delete notification";
  }
}
// Check for bulk delete request
else if (isset($_POST['delete_selected']) && !empty($_POST['notification_ids'])) {
  $ids = $_POST['notification_ids'];
  $placeholders = str_repeat('?,', count($ids) - 1) . '?';

  $query = "DELETE FROM admin_notifications WHERE id IN ($placeholders)";
  $stmt = $conn->prepare($query);

  $types = str_repeat('i', count($ids));
  $stmt->bind_param($types, ...$ids);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    $success = true;
    $message = $stmt->affected_rows . " notification(s) deleted successfully";
  } else {
    $success = false;
    $message = "Failed to delete notifications";
  }
}
// Check for delete all request 
else if (isset($_POST['delete_all']) && $_POST['delete_all'] == 1) {
  // Optional condition to delete based on filters
  $where = '';
  $params = [];
  $types = '';

  if (!empty($_POST['type'])) {
    $where .= " WHERE type = ?";
    $params[] = $_POST['type'];
    $types .= 's';
  }

  if (isset($_POST['is_read']) && $_POST['is_read'] !== '') {
    $is_read = ($_POST['is_read'] == 'read') ? 1 : 0;
    $where = empty($where) ? " WHERE is_read = ?" : $where . " AND is_read = ?";
    $params[] = $is_read;
    $types .= 'i';
  }

  $query = "DELETE FROM admin_notifications" . $where;
  $stmt = $conn->prepare($query);

  if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    $success = true;
    $message = $stmt->affected_rows . " notification(s) deleted successfully";
  } else {
    $success = false;
    $message = "No notifications were deleted";
  }
} else {
  $success = false;
  $message = "Invalid request";
}

// Handle response based on request type
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
  // Return JSON for AJAX requests
  header('Content-Type: application/json');
  echo json_encode([
    'success' => $success,
    'message' => $message
  ]);
} else {
  // Redirect for regular requests
  $return_page = $_GET['return'] ?? 'all_notifications.php';

  if ($success) {
    header("Location: $return_page?status=success&message=" . urlencode($message));
  } else {
    header("Location: $return_page?status=error&message=" . urlencode($message));
  }
}
?>