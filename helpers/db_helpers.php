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
 * Fetch unread notifications for a user using notification summary view
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
 * Get notification summary using view
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array|false Notification summary or false if not found
 */
function getNotificationSummary(PDO $pdo, int $userId)
{
  $stmt = $pdo->prepare("SELECT * FROM notification_summary_view WHERE user_id = :user_id");
  $stmt->execute(['user_id' => $userId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch approved articles with user info using view
 * 
 * @param PDO $pdo Database connection
 * @return array List of articles
 */
function getApprovedArticles(PDO $pdo)
{
  $stmt = $pdo->query(
    "SELECT * FROM article_dashboard_view 
     WHERE status = 'approved'
     ORDER BY created_at DESC"
  );
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get admin dashboard statistics using view
 * 
 * @param PDO $pdo Database connection
 * @return array Admin dashboard stats
 */
function getAdminDashboardStats(PDO $pdo)
{
  $stmt = $pdo->query("SELECT * FROM admin_dashboard_stats");
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user statistics using view
 * 
 * @param PDO $pdo Database connection
 * @param int|null $userId User ID (optional, if null returns all users)
 * @return array User statistics
 */
function getUserStats(PDO $pdo, ?int $userId = null)
{
  if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM user_stats_view WHERE id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    $stmt = $pdo->query("SELECT * FROM user_stats_view ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
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

function getPaginatedArticles($pdo, $offset, $limit)
{
  $stmt = $pdo->prepare("
      SELECT a.*, u.full_name, u.profile_picture 
      FROM articles a 
      JOIN users u ON a.user_id = u.id 
      WHERE a.is_approved = 1 
      ORDER BY a.created_at DESC 
      LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch filtered articles with pagination support
 * 
 * @param PDO $pdo Database connection
 * @param array $institutes List of institutes to filter by
 * @param string $sortOption Sorting option ('new', 'old', 'popular')
 * @param int $limit Maximum number of articles to return
 * @param int $offset Number of articles to skip
 * @return array List of filtered articles for the current page
 */
function getFilteredArticlesPaginated(PDO $pdo, array $institutes, string $sortOption = 'new', int $limit = 3, int $offset = 0): array
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

  // Add pagination
  $baseQuery .= " LIMIT :limit OFFSET :offset";

  $stmt = $pdo->prepare($baseQuery);
  $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
  $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

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
 * Approve article using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param int $articleId Article ID
 * @param string $approvalNotes Optional approval notes
 * @return array Result with success status and message
 */
function approveArticleWithProcedure(PDO $pdo, int $articleId, string $approvalNotes = '')
{
  $stmt = $pdo->prepare("CALL ApproveArticle(:article_id, :approval_notes)");
  $stmt->execute([
    'article_id' => $articleId,
    'approval_notes' => $approvalNotes
  ]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Reject article using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param int $articleId Article ID
 * @param string $rejectionReason Rejection reason
 * @return array Result with success status and message
 */
function rejectArticleWithProcedure(PDO $pdo, int $articleId, string $rejectionReason)
{
  $stmt = $pdo->prepare("CALL RejectArticle(:article_id, :rejection_reason)");
  $stmt->execute([
    'article_id' => $articleId,
    'rejection_reason' => $rejectionReason
  ]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Report article using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int $articleId Article ID
 * @param string $reason Report reason
 * @return array Result with success status and message
 */
function reportArticleWithProcedure(PDO $pdo, int $userId, int $articleId, string $reason = '')
{
  $stmt = $pdo->prepare("CALL ReportArticle(:user_id, :article_id, :reason)");
  $stmt->execute([
    'user_id' => $userId,
    'article_id' => $articleId,
    'reason' => $reason
  ]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Mark notifications as read using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int|null $notificationId Specific notification ID (optional)
 * @return array Result with success status and message
 */
function markNotificationReadWithProcedure(PDO $pdo, int $userId, ?int $notificationId = null)
{
  $stmt = $pdo->prepare("CALL MarkNotificationRead(:user_id, :notification_id)");
  $stmt->execute([
    'user_id' => $userId,
    'notification_id' => $notificationId
  ]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user dashboard statistics using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array User dashboard statistics
 */
function getUserDashboardStatsWithProcedure(PDO $pdo, int $userId)
{
  $stmt = $pdo->prepare("CALL GetUserDashboardStats(:user_id)");
  $stmt->execute(['user_id' => $userId]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get saved articles using enhanced view
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array List of saved articles with details
 */
function getSavedArticlesFromView(PDO $pdo, int $userId)
{
  $stmt = $pdo->prepare("SELECT * FROM saved_articles_view WHERE user_id = :user_id ORDER BY saved_at DESC");
  $stmt->execute(['user_id' => $userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Register user using stored procedure
 * 
 * @param PDO $pdo Database connection
 * @param string $fullName Full name
 * @param string $email Email address
 * @param string $password Hashed password
 * @param string $role User role
 * @param string $institute Institute name
 * @return array Result with success status, message, and user ID
 */
function registerUserWithProcedure(PDO $pdo, string $fullName, string $email, string $password, string $role, string $institute)
{
  $stmt = $pdo->prepare("CALL RegisterUser(:full_name, :email, :password, :role, :institute)");
  $stmt->execute([
    'full_name' => $fullName,
    'email' => $email,
    'password' => $password,
    'role' => $role,
    'institute' => $institute
  ]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}
