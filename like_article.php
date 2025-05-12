<?php
session_start();
header('Content-Type: application/json');

include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Handle both POST form data and JSON requests
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['article_id'])) {
    $article_id = intval($input['article_id']);
} else {
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
}

if (!$article_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid article ID']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM reactions WHERE user_id = ? AND article_id = ?");
$stmt->bind_param("ii", $user_id, $article_id);
$stmt->execute();
$stmt->store_result();
$already_liked = $stmt->num_rows > 0;

if ($already_liked) {
    // Already liked, remove like
    $stmt = $conn->prepare("DELETE FROM reactions WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("ii", $user_id, $article_id);
    $stmt->execute();
    $action = 'unliked';
} else {
    // Insert like
    $stmt = $conn->prepare("INSERT INTO reactions (user_id, article_id, reaction_type) VALUES (?, ?, 'like')");
    $stmt->bind_param("ii", $user_id, $article_id);
    $stmt->execute();
    $action = 'liked';
}

// Count updated likes
$stmt = $conn->prepare("SELECT COUNT(*) FROM reactions WHERE article_id = ? AND reaction_type = 'like'");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$stmt->bind_result($like_count);
$stmt->fetch();

echo json_encode([
    'success' => true,
    'likes' => $like_count,
    'action' => $action
]);
