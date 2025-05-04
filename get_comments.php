<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';
require_once 'helpers/db_helpers.php';

// Validate the article_id parameter
if (empty($_POST['article_id']) || !is_numeric($_POST['article_id'])) {
  echo json_encode(['error' => 'Invalid or missing article ID']);
  exit;
}

$article_id = (int) $_POST['article_id'];

try {
  // Fetch comments using the helper function
  $comments = getCommentsForArticle($pdo, $article_id);
  echo json_encode($comments);
} catch (Exception $e) {
  echo json_encode(['error' => 'Failed to fetch comments: ' . $e->getMessage()]);
}
?>