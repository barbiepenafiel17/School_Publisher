<?php
include 'db_final.php';
session_start();
require_once 'helpers/db_helpers.php';

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

// Unhide the article
$delete_query = "DELETE FROM hidden_articles WHERE user_id = ? AND article_id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("ii", $user_id, $article_id);

if ($stmt->execute()) {
    // Redirect back with success message
    header("Location: hide.php?status=unhidden");
} else {
    // Redirect back with error message
    header("Location: hide.php?status=error");
}

$stmt->close();
$conn->close();
?>