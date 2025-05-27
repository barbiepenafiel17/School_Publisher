<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 5; // Number of articles per page
    $offset = ($page - 1) * $limit;

    // Fetch articles with pagination
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE status = 'approved' ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindParam(1, $limit, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return articles as JSON
    header('Content-Type: application/json');
    echo json_encode($articles);
    exit;
}
?>