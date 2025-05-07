<?php
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
    header("Location: hide.php?status=error");
    exit();
}

$article_id = $_POST['article_id'];

// Check if the article is already hidden
$query = "SELECT * FROM hidden_articles WHERE user_id = ? AND article_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Article is already hidden
    header("Location: hide.php?status=already_hidden");
    exit();
}

// Hide the article
$insert_query = "INSERT INTO hidden_articles (user_id, article_id) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("ii", $user_id, $article_id);

if ($insert_stmt->execute()) {
    // Redirect back with success message
    header("Location: hide.php?status=hidden");
} else {
    // Redirect back with error message
    header("Location: hide.php?status=error");
}

$insert_stmt->close();
$stmt->close();
$conn->close();
?>