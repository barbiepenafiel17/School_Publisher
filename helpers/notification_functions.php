<?php
/**
 * Helper functions for admin notifications
 */

/**
 * Add a notification to the admin notifications table
 * 
 * @param string $message The notification message
 * @param string $type Type of notification (default: 'info', options: 'info', 'success', 'warning', 'danger')
 * @param string $link Optional link to include with notification
 * @return bool Whether the notification was added successfully
 */
function add_admin_notification($message, $type = 'info', $link = null)
{
  global $conn;

  $query = "INSERT INTO admin_notifications (message, type, link) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("sss", $message, $type, $link);

  return $stmt->execute();
}

/**
 * Get the count of unread notifications
 * 
 * @return int Number of unread notifications
 */
function get_unread_notification_count()
{
  global $conn;

  $query = "SELECT COUNT(*) AS count FROM admin_notifications WHERE is_read = 0";
  $result = $conn->query($query);

  if ($result && $row = $result->fetch_assoc()) {
    return $row['count'];
  }

  return 0;
}

/**
 * Get the latest notifications
 * 
 * @param int $limit Maximum number of notifications to retrieve
 * @return array Array of notification objects
 */
function get_latest_notifications($limit = 5)
{
  global $conn;

  $query = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $limit);
  $stmt->execute();

  $result = $stmt->get_result();
  $notifications = [];

  while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
  }

  return $notifications;
}

/**
 * Mark a notification as read
 * 
 * @param int $notification_id ID of the notification to mark as read
 * @return bool Whether the operation was successful
 */
function mark_notification_read($notification_id)
{
  global $conn;

  $query = "UPDATE admin_notifications SET is_read = 1 WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $notification_id);

  return $stmt->execute();
}

/**
 * Mark all notifications as read
 * 
 * @return bool Whether the operation was successful
 */
function mark_all_notifications_read()
{
  global $conn;

  $query = "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0";

  return $conn->query($query);
}

/**
 * Delete a notification
 * 
 * @param int $notification_id ID of the notification to delete
 * @return bool Whether the operation was successful
 */
function delete_notification($notification_id)
{
  global $conn;

  $query = "DELETE FROM admin_notifications WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $notification_id);

  return $stmt->execute();
}

/**
 * Delete multiple notifications
 * 
 * @param array $notification_ids Array of notification IDs to delete
 * @return int Number of deleted notifications
 */
function delete_multiple_notifications($notification_ids)
{
  global $conn;

  if (empty($notification_ids)) {
    return 0;
  }

  $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
  $query = "DELETE FROM admin_notifications WHERE id IN ($placeholders)";
  $stmt = $conn->prepare($query);

  $types = str_repeat('i', count($notification_ids));
  $stmt->bind_param($types, ...$notification_ids);
  $stmt->execute();

  return $stmt->affected_rows;
}

/**
 * Get notification statistics
 * 
 * @return array Array of notification statistics
 */
function get_notification_stats()
{
  global $conn;

  $query = "SELECT 
      COUNT(*) AS total,
      SUM(is_read = 0) AS unread,
      SUM(type = 'info') AS info,
      SUM(type = 'warning') AS warning,
      SUM(type = 'danger') AS danger,
      SUM(type = 'success') AS success
  FROM admin_notifications";

  $result = $conn->query($query);

  if ($result) {
    return $result->fetch_assoc();
  }

  return [
    'total' => 0,
    'unread' => 0,
    'info' => 0,
    'warning' => 0,
    'danger' => 0,
    'success' => 0
  ];
}
