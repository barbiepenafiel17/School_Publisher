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
  // If the institutes array is empty or contains 'All', fetch all approved articles
  if (empty($institutes) || in_array('All', $institutes)) {
    $query = "SELECT a.*, u.full_name, u.profile_picture,
                  (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = 'like') AS likes,
                  (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
                  FROM articles a
                  JOIN users u ON a.user_id = u.id
                  WHERE a.status = 'approved'";

    // Add sorting logic
    switch ($sortOption) {
      case 'old':
        $query .= " ORDER BY a.created_at ASC";
        break;
      case 'popular':
      case 'hottest': // Assuming 'hottest' is the same as 'popular'
        $query .= " ORDER BY likes DESC";
        break;
      case 'new':
      default:
        $query .= " ORDER BY a.created_at DESC";
        break;
    }

    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Otherwise, filter by the provided institutes
  $placeholders = implode(',', array_fill(0, count($institutes), '?'));
  $query = "SELECT a.*, u.full_name, u.profile_picture,
              (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = 'like') AS likes,
              (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
              FROM articles a
              JOIN users u ON a.user_id = u.id
              WHERE a.status = 'approved' AND u.institute IN ($placeholders)";

  // Add sorting logic
  switch ($sortOption) {
    case 'old':
      $query .= " ORDER BY a.created_at ASC";
      break;
    case 'popular':
    case 'hottest':
      $query .= " ORDER BY likes DESC";
      break;
    case 'new':
    default:
      $query .= " ORDER BY a.created_at DESC";
      break;
  }

  $stmt = $pdo->prepare($query);
  $stmt->execute($institutes);
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