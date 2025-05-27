<?php
// Test script for approve_article.php functionality
// This script will test the approval process without requiring a full web interface

// Set up test parameters
$_POST['article_id'] = 76; // Use a pending article
$_POST['approval_notes'] = 'This article meets our quality standards.';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Capture output
ob_start();

// Include the approve_article.php script
try {
    // Prevent actual redirect during testing by overriding the exit function
    if (!function_exists('header')) {
        function header($string) {
            echo "REDIRECT: " . $string . "\n";
        }
    }
    
    include 'approve_article.php';
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();
echo "Test Output:\n";
echo $output;

// Check if the article was actually approved
include 'db_connect.php';
$check_stmt = $conn->prepare("SELECT status FROM articles WHERE id = ?");
$check_stmt->bind_param("i", $_POST['article_id']);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    echo "\nArticle Status After Test: " . $article['status'];
    if ($article['status'] === 'APPROVED') {
        echo " ✅ SUCCESS - Article was approved correctly!";
    } else {
        echo " ❌ FAILED - Article status was not updated";
    }
} else {
    echo "\n❌ FAILED - Article not found";
}

$check_stmt->close();
$conn->close();
?>
