<?php
$mysqli = new mysqli("localhost", "root", "", "dbclm_college");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get pending articles count
$query = "SELECT COUNT(*) as count FROM articles WHERE status IN ('PENDING', 'SUBMITTED')";
$result = $mysqli->query($query);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}

$articles_count = $result->fetch_assoc()['count'];

// Get unread notifications count
$query = "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0";
$result = $mysqli->query($query);

if (!$result) {
    $notifications_count = 0;
} else {
    $notifications_count = $result->fetch_assoc()['count'];
}

// Return JSON with both counts
header('Content-Type: application/json');
echo json_encode([
    'articles' => $articles_count,
    'notifications' => $notifications_count,
    'total' => $articles_count + $notifications_count
]);
?>