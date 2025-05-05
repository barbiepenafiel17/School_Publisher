<?php
/**
 * Database Helper Functions
 * Contains common database operations used throughout the application
 */

/**
 * Fetch user info by ID
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function getUserInfo(PDO $pdo, int $userId)
{
  $stmt = $pdo->prepare("SELECT full_name, profile_picture FROM users WHERE id = :id");
  $stmt->execute(['id' => $userId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch unread notifications for a user
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array List of unread notifications
 */
function getUnreadNotifications(PDO $pdo, int $userId)
{
  $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC");
  $stmt->execute(['user_id' => $userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch approved articles with user info
 * 
 * @param PDO $pdo Database connection
 * @return array List of articles
 */
function getApprovedArticles(PDO $pdo)
{
  $stmt = $pdo->query(
    "SELECT a.*, u.full_name, u.profile_picture,
        (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
        FROM articles a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'approved'
        ORDER BY a.created_at DESC"
  );
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch filtered articles based on institutes and sort options
 * 
 * @param PDO $pdo Database connection
 * @param array $institutes List of institutes to filter by
 * @param string $sortOption Sorting option ('new', 'old', 'popular', 'hottest')
 * @return array List of filtered articles
 */
function getFilteredArticles(PDO $pdo, array $institutes, string $sortOption = 'new'): array
{
  // Start session if not already started
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

  // Base query to get articles and exclude hidden ones
  $baseQuery = "SELECT a.*, u.full_name, u.profile_picture,
              (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = 'like') AS likes,
              (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
              FROM articles a
              JOIN users u ON a.user_id = u.id
              WHERE a.status = 'approved' 
              AND NOT EXISTS (
                SELECT 1 FROM hidden_articles h 
                WHERE h.article_id = a.id AND h.user_id = :userId
              )";

  // Filter by user's institute, handling abbreviations correctly
  if (!empty($institutes) && !in_array('All', $institutes)) {
    // Convert abbreviations to LIKE patterns for partial matching
    $patterns = [];
    foreach ($institutes as $institute) {
      switch ($institute) {
        case 'IC':
          $patterns[] = "u.institute LIKE '%IC%'";
          break;
        case 'ILEGG':
          $patterns[] = "u.institute LIKE '%ILEGG%'";
          break;
        case 'ITed':
          $patterns[] = "u.institute LIKE '%ITed%'";
          break;
        case 'IAAS':
          $patterns[] = "u.institute LIKE '%IAAS%'";
          break;
        default:
          // For full institute names (in case they're used)
          $patterns[] = "u.institute = ?";
      }
    }

    
    // Join patterns with OR
    if (!empty($patterns)) {
      $baseQuery .= " AND (" . implode(' OR ', $patterns) . ")";
    }
  }

  // Add sorting logic
  switch ($sortOption) {
    case 'old':
      $baseQuery .= " ORDER BY a.created_at ASC";
      break;
    case 'popular':
      $baseQuery .= " ORDER BY likes DESC";
      break;
    case 'new':
    default:
      $baseQuery .= " ORDER BY a.created_at DESC";
      break;
  }

  $stmt = $pdo->prepare($baseQuery);
  $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

  // Bind institute parameters if needed (only for full institute names)
  $paramIndex = 1;
  if (!empty($institutes) && !in_array('All', $institutes)) {
    foreach ($institutes as $institute) {
      if (!in_array($institute, ['IC', 'ILEGG', 'ITed', 'IAAS'])) {
        $stmt->bindValue($paramIndex++, $institute);
      }
    }
  }

  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch latest announcements
 * 
 * @param PDO $pdo Database connection
 * @return array List of announcements
 */
function getLatestAnnouncements(PDO $pdo)
{
  $stmt = $pdo->query("SELECT * FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch comments for a specific article
 * 
 * @param PDO $pdo Database connection
 * @param int $articleId Article ID
 * @return array List of comments
 */
function getCommentsForArticle(PDO $pdo, int $articleId)
{
  $stmt = $pdo->prepare(
    "SELECT c.*, u.full_name AS commenter_name 
         FROM comments c 
         JOIN users u ON c.user_id = u.id 
         WHERE c.article_id = :article_id 
         ORDER BY c.created_at ASC"
  );
  $stmt->execute(['article_id' => $articleId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}