<?php
include 'db_connect.php'; // Ensure db.php creates $conn properly

// Handle both POST and GET requests for flexibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check for article ID in both POST and GET
    $article_id = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id']) && is_numeric($_POST['article_id'])) {
        $article_id = $_POST['article_id'];
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $article_id = $_GET['id'];
    }    // Proceed if we have a valid article ID
    if ($article_id !== null) {
        // Check for approval notes
        $approval_notes = isset($_POST['approval_notes']) ? trim($_POST['approval_notes']) : '';

        // Use the stored procedure for article approval
        $stmt = $conn->prepare("CALL ApproveArticle(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("is", $article_id, $approval_notes);

        if ($stmt->execute()) {
            // Article approved successfully via stored procedure
            // The stored procedure handles status update and notifications automatically
        } else {
            // Handle failure of article approval
            die("Article approval failed: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();        // Redirect back to admin dashboard with success indicator
        header("Location: admin_dashboard.php?approved=1");
        exit();
    } else {
        // Handle invalid or missing article ID
        die("Invalid article ID.");
    }
} else {
    // Handle incorrect request method (not POST or GET)
    die("Invalid request method. This endpoint requires either POST or GET.");
}
?>