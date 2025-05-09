<?php
require_once 'helpers/db_helpers.php';
require_once 'db_connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = 5;

$articles = getPaginatedArticles($pdo, $offset, $limit);

header('Content-Type: application/json');
echo json_encode($articles);
?>
