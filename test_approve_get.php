<?php
// Test the approve_article.php with GET request
// Simulate GET request
$_GET['id'] = 77;
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "Testing approve_article.php with GET request...\n";
echo "Article ID: " . $_GET['id'] . "\n\n";

// Check article status before
include 'db_connect.php';
$check_stmt = $conn->prepare("SELECT title, status FROM articles WHERE id = ?");
$check_stmt->bind_param("i", $_GET['id']);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    echo "Before: '" . $article['title'] . "' status: " . $article['status'] . "\n";
} else {
    echo "Article not found!\n";
    exit;
}
$check_stmt->close();
$conn->close();

// Capture output and prevent redirect
ob_start();

// Override header function to prevent redirect
function header($string) {
    echo "REDIRECT INTERCEPTED: " . $string . "\n";
}

// Override exit function to prevent termination
// (Can't actually override exit, so we'll use a different approach)

try {
    include 'approve_article.php';
} catch (Exception $e) {
    // Expected when exit() is called
}

$output = ob_get_clean();
echo "Script Output:\n" . $output . "\n";

// Check status after
include 'db_connect.php';
$check_stmt2 = $conn->prepare("SELECT status FROM articles WHERE id = ?");
$check_stmt2->bind_param("i", $_GET['id']);
$check_stmt2->execute();
$result2 = $check_stmt2->get_result();

if ($result2->num_rows > 0) {
    $updated_article = $result2->fetch_assoc();
    echo "After: Article status: " . $updated_article['status'] . "\n";
    
    if ($updated_article['status'] === 'APPROVED') {
        echo "✅ SUCCESS: approve_article.php working correctly!\n";
    } else {
        echo "❌ FAILED: Article was not approved\n";
    }
} else {
    echo "❌ FAILED: Article not found after processing\n";
}

$check_stmt2->close();
$conn->close();
?>
