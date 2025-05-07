<?php
// Include database connection
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the article ID is provided
if (!isset($_POST['article_id']) || !is_numeric($_POST['article_id'])) {
    die("Invalid article ID.");
}

$article_id = $_POST['article_id'];

// Check if the article is already saved
$query = "SELECT * FROM saved_articles WHERE user_id = ? AND article_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $article_id]);

if ($stmt->rowCount() > 0) {
    // Article is already saved
    header("Location: newsfeed.php?status=already_saved");
    exit();
}

// Save the article
$insert_query = "INSERT INTO saved_articles (user_id, article_id) VALUES (?, ?)";
$insert_stmt = $pdo->prepare($insert_query);

if ($insert_stmt->execute([$user_id, $article_id])) {
    // Redirect back with success message
    header("Location: newsfeed.php?status=saved");
} else {
    // Redirect back with error message
    header("Location: newsfeed.php?status=error");
}
?>