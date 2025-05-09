<?php
include 'db_connect.php';

if (!isset($_GET['article_id']) || !is_numeric($_GET['article_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID.']);
    exit();
}

$article_id = $_GET['article_id'];

$query = "
    SELECT 
        articles.title, 
        articles.content, 
        users.full_name AS author_name, 
        users.institute AS author_institute 
    FROM articles 
    JOIN users ON articles.user_id = users.id 
    WHERE articles.id = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    echo json_encode(['success' => true, 'article' => $article]);
} else {
    echo json_encode(['success' => false, 'message' => 'Article not found.']);
}

$stmt->close();
?>