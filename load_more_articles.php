<?php
require_once 'helpers/db_helpers.php';
require_once 'db_connect.php';

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'Not authenticated']);
  exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$offset = isset($input['offset']) ? intval($input['offset']) : 0;
$limit = isset($input['limit']) ? intval($input['limit']) : 5;
$sortOption = isset($input['sort']) ? $input['sort'] : 'new';

// Validate parameters
if ($offset < 0 || $limit <= 0 || $limit > 20) {
  echo json_encode(['error' => 'Invalid parameters']);
  exit();
}

// Get institutes from the session or use default
$institutes = isset($_SESSION['filtered_institutes']) ? $_SESSION['filtered_institutes'] : ['All'];

// Fetch paginated articles
$articles = getFilteredArticlesPaginated($pdo, $institutes, $sortOption, $limit, $offset);

// Add is_owner flag
foreach ($articles as &$article) {
  $article['is_owner'] = ($article['user_id'] == $_SESSION['user_id']);
}

echo json_encode([
  'articles' => $articles,
  'hasMore' => count($articles) == $limit
]);
?>