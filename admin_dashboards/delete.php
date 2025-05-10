<?php
session_start();
require_once 'includes/db.php'; // Adjust path as needed

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Get the user ID from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit();
}

$user_id = (int)$data['id'];

// Optional: Use a transaction to ensure all deletions occur together
$mysqli->begin_transaction();

try {
    // Delete user's comments
    $stmt = $mysqli->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete user's articles
    $stmt = $mysqli->prepare("DELETE FROM articles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete user account
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
}

$mysqli->close();