<?php
session_start();

require_once 'db_connect.php';
require_once 'helpers/db_helpers.php';

// Redirect if user is not authenticated or article_id is missing
if (!isset($_SESSION['user_id'], $_POST['article_id']) || !is_numeric($_POST['article_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$article_id = (int) $_POST['article_id'];

try {
    // Check if the article belongs to the user or if the user is an admin
    $stmt = $pdo->prepare("SELECT user_id FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);

    if ($stmt->rowCount() > 0) {
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($article['user_id'] === $user_id || $user_role === 'admin') {
            // Soft delete: set status to 'DELETED'
            $update_stmt = $pdo->prepare("UPDATE articles SET status = 'DELETED' WHERE id = ?");
            $update_stmt->execute([$article_id]);

            if ($update_stmt->rowCount() > 0) {
                header("Location: newsfeed.php?deleted=1");
                exit();
            } else {
                echo json_encode(['error' => 'Failed to delete the article.']);
            }
        } else {
            echo json_encode(['error' => 'You do not have permission to delete this article.']);
        }
    } else {
        echo json_encode(['error' => 'Article not found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>