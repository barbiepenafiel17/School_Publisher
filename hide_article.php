<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id']) && isset($_SESSION['user_id'])) {
    $articleId = intval($_POST['article_id']);
    $userId = intval($_SESSION['user_id']);

    // Prepare statement
    $stmt = $conn->prepare("INSERT IGNORE INTO hidden_articles (user_id, article_id) VALUES (?, ?)");

    if (!$stmt) {
        // Handle prepare error
        die("Database error: {$conn->error}");
    }

    $stmt->bind_param("ii", $userId, $articleId);

    if ($stmt->execute()) {
        // Close the statement before redirecting
        $stmt->close();
        header("Location: newsfeed.php");
        exit;
    } else {
        // Handle execute error
        $stmt->close();
        die("Error hiding article: {$stmt->error}");
    }
} else {
    // Invalid request
    die("Invalid request.");
}
