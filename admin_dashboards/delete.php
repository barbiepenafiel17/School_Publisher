<?php
session_start();
require_once 'includes/db.php'; // Adjust path as needed

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

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

        header("Location: index1.php?message=User+and+all+related+data+deleted");
        exit();
    } catch (Exception $e) {
        $mysqli->rollback();
        echo "Failed to delete user: " . $e->getMessage();
    }

} else {
    echo "No user ID provided.";
}

$mysqli->close();
