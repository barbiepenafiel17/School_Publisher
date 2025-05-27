<?php
// Simple test for ApproveArticle stored procedure
include 'db_connect.php';

echo "Testing ApproveArticle Stored Procedure...\n\n";

// Test with article ID 76
$article_id = 76;
$approval_notes = "Test approval via stored procedure";

echo "Testing approval of article ID: $article_id\n";

// Check current status
$check_stmt = $conn->prepare("SELECT title, status FROM articles WHERE id = ?");
$check_stmt->bind_param("i", $article_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    echo "Before: Article '" . $article['title'] . "' status: " . $article['status'] . "\n";
    
    // Call the stored procedure
    $stmt = $conn->prepare("CALL ApproveArticle(?, ?)");
    $stmt->bind_param("is", $article_id, $approval_notes);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $response = $result->fetch_assoc();
            echo "Stored Procedure Response: " . $response['result'] . " (Status: " . $response['status'] . ")\n";
        }
        
        // Check status after
        $stmt->close();
        $check_stmt2 = $conn->prepare("SELECT status FROM articles WHERE id = ?");
        $check_stmt2->bind_param("i", $article_id);
        $check_stmt2->execute();
        $result2 = $check_stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            $updated_article = $result2->fetch_assoc();
            echo "After: Article status: " . $updated_article['status'] . "\n";
            
            if ($updated_article['status'] === 'APPROVED') {
                echo "✅ SUCCESS: Article approval working correctly!\n";
            } else {
                echo "❌ FAILED: Article was not approved\n";
            }
        }
        $check_stmt2->close();
    } else {
        echo "❌ FAILED: Stored procedure execution failed: " . $stmt->error . "\n";
    }
} else {
    echo "❌ FAILED: Article not found\n";
}

$check_stmt->close();
$conn->close();
?>
