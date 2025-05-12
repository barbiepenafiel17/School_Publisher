<?php
session_start();
header('Content-Type: application/json');
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $articleId = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
    $commentText = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    $userId = $_SESSION['user_id'];

    if ($articleId > 0 && !empty($commentText)) {
        try {
            // Prepare SQL Insert
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $articleId, $userId, $commentText);

            if ($stmt->execute()) {
                // Get the comment count for this article
                $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM comments WHERE article_id = ?");
                $countStmt->bind_param("i", $articleId);
                $countStmt->execute();
                $result = $countStmt->get_result();
                $count = $result->fetch_assoc()['count'];

                echo json_encode([
                    'success' => true,
                    'comment_count' => $count
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error saving comment.']);
            }
            $stmt->close();
            $countStmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>