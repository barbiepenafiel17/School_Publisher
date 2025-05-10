<?php
session_start();
require 'db_connect.php'; // Adjust if your DB connection is elsewhere

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$article_id = $_POST['article_id'];

// Insert the report into the `article_reports` table
$sql = "INSERT INTO article_reports (user_id, article_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $user_id, $article_id);
$stmt->execute();
$stmt->close();

// Add a notification for the admin
$message = "A user has reported an article. Article ID: $article_id.";
$notif_sql = "INSERT INTO admin_notifications (type, reference_id, message) VALUES ('report', ?, ?)";
$notif_stmt = $conn->prepare($notif_sql);

if (!$notif_stmt) {
    die("Prepare failed: " . $conn->error);
}

$notif_stmt->bind_param("is", $article_id, $message);
$notif_stmt->execute();
$notif_stmt->close();

$conn->close();

// Redirect back with a success message
header("Location: newsfeed.php?status=reported");
exit();
?>
