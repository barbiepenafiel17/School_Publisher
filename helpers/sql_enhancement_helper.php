<?php
/**
 * SQL Enhancement Helper Functions
 * School Publisher Project
 * 
 * This file contains helper functions to use the new SQL Views and Stored Procedures
 */

require_once 'db_connect.php';

class SQLEnhancementHelper
{
  private $conn;
  private $pdo;

  public function __construct()
  {
    global $conn, $pdo;
    $this->conn = $conn;
    $this->pdo = $pdo;
  }

  /**
   * Get article dashboard data using the view
   */
  public function getArticleDashboard($limit = 10, $offset = 0, $status = null)
  {
    try {
      $sql = "SELECT * FROM article_dashboard_view";
      $params = [];

      if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
      }

      $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
      $params[] = $limit;
      $params[] = $offset;

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching article dashboard: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get user statistics using the view
   */
  public function getUserStats($user_id = null)
  {
    try {
      $sql = "SELECT * FROM user_stats_view";
      $params = [];

      if ($user_id) {
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
      }

      $sql .= " ORDER BY created_at DESC";

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);

      return $user_id ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching user stats: " . $e->getMessage());
      return $user_id ? null : [];
    }
  }

  /**
   * Get admin dashboard statistics
   */
  public function getAdminDashboardStats()
  {
    try {
      $stmt = $this->pdo->query("SELECT * FROM admin_dashboard_stats");
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching admin stats: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Get notification summary for a user
   */
  public function getNotificationSummary($user_id)
  {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM notification_summary_view WHERE user_id = ?");
      $stmt->execute([$user_id]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching notification summary: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Get article engagement data
   */
  public function getArticleEngagement($article_id = null)
  {
    try {
      $sql = "SELECT * FROM article_engagement_view";
      $params = [];

      if ($article_id) {
        $sql .= " WHERE article_id = ?";
        $params[] = $article_id;
      }

      $sql .= " ORDER BY engagement_score DESC";

      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);

      return $article_id ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching article engagement: " . $e->getMessage());
      return $article_id ? null : [];
    }
  }

  /**
   * Get saved articles with details using the view
   */
  public function getSavedArticles($user_id, $limit = 10, $offset = 0)
  {
    try {
      $stmt = $this->pdo->prepare("
                SELECT * FROM saved_articles_view 
                WHERE user_id = ? 
                ORDER BY saved_at DESC 
                LIMIT ? OFFSET ?
            ");
      $stmt->execute([$user_id, $limit, $offset]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching saved articles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Approve article using stored procedure
   */
  public function approveArticle($article_id, $approval_notes = '')
  {
    try {
      $stmt = $this->pdo->prepare("CALL ApproveArticle(?, ?)");
      $stmt->execute([$article_id, $approval_notes]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return [
        'success' => $result['status'] === 'success',
        'message' => $result['result'],
        'article_id' => $result['article_id'] ?? null
      ];
    } catch (PDOException $e) {
      error_log("Error approving article: " . $e->getMessage());
      return ['success' => false, 'message' => 'Database error occurred'];
    }
  }

  /**
   * Reject article using stored procedure
   */
  public function rejectArticle($article_id, $rejection_reason)
  {
    try {
      $stmt = $this->pdo->prepare("CALL RejectArticle(?, ?)");
      $stmt->execute([$article_id, $rejection_reason]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return [
        'success' => $result['status'] === 'success',
        'message' => $result['result'],
        'article_id' => $result['article_id'] ?? null
      ];
    } catch (PDOException $e) {
      error_log("Error rejecting article: " . $e->getMessage());
      return ['success' => false, 'message' => 'Database error occurred'];
    }
  }

  /**
   * Get filtered articles using stored procedure
   */
  public function getFilteredArticles($user_id, $institutes = 'All', $sort_option = 'recent', $limit = 10, $offset = 0)
  {
    try {
      $stmt = $this->pdo->prepare("CALL GetFilteredArticles(?, ?, ?, ?, ?)");
      $stmt->execute([$user_id, $institutes, $sort_option, $limit, $offset]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error getting filtered articles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Register user using stored procedure
   */
  public function registerUser($full_name, $email, $password, $role, $institute)
  {
    try {
      $stmt = $this->pdo->prepare("CALL RegisterUser(?, ?, ?, ?, ?)");
      $stmt->execute([$full_name, $email, $password, $role, $institute]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return [
        'success' => $result['status'] === 'success',
        'message' => $result['result'],
        'user_id' => $result['user_id'] ?? null
      ];
    } catch (PDOException $e) {
      error_log("Error registering user: " . $e->getMessage());
      return ['success' => false, 'message' => 'Database error occurred'];
    }
  }

  /**
   * Create notification using stored procedure
   */
  public function createNotification($user_id, $message, $type = 'general')
  {
    try {
      $stmt = $this->pdo->prepare("CALL CreateNotification(?, ?, ?)");
      $stmt->execute([$user_id, $message, $type]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result['status'] === 'success';
    } catch (PDOException $e) {
      error_log("Error creating notification: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Get user dashboard statistics using stored procedure
   */
  public function getUserDashboardStats($user_id)
  {
    try {
      $stmt = $this->pdo->prepare("CALL GetUserDashboardStats(?)");
      $stmt->execute([$user_id]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error fetching user dashboard stats: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Bulk approve articles using stored procedure
   */
  public function bulkApproveArticles($article_ids_array, $approval_notes = '')
  {
    try {
      $article_ids_string = implode(',', $article_ids_array);
      $stmt = $this->pdo->prepare("CALL BulkApproveArticles(?, ?)");
      $stmt->execute([$article_ids_string, $approval_notes]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return [
        'success' => $result['status'] === 'success',
        'message' => $result['result'],
        'count' => $result[0] ?? 0
      ];
    } catch (PDOException $e) {
      error_log("Error bulk approving articles: " . $e->getMessage());
      return ['success' => false, 'message' => 'Database error occurred'];
    }
  }

  /**
   * Search articles using full-text search (requires full-text index)
   */
  public function searchArticles($search_term, $limit = 20)
  {
    try {
      $stmt = $this->pdo->prepare("
                SELECT *, MATCH(title, abstract, content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM articles 
                WHERE MATCH(title, abstract, content) AGAINST(? IN NATURAL LANGUAGE MODE)
                AND status = 'approved'
                ORDER BY relevance DESC
                LIMIT ?
            ");
      $stmt->execute([$search_term, $search_term, $limit]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error searching articles: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Get performance statistics
   */
  public function getPerformanceStats()
  {
    try {
      $stats = [];

      // Get table sizes
      $stmt = $this->pdo->query("
                SELECT 
                    table_name AS table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = 'dbclm_college'
                ORDER BY (data_length + index_length) DESC
            ");
      $stats['table_sizes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Get index information
      $stmt = $this->pdo->query("
                SELECT 
                    table_name,
                    index_name,
                    non_unique,
                    column_name
                FROM information_schema.statistics 
                WHERE table_schema = 'dbclm_college'
                ORDER BY table_name, index_name
            ");
      $stats['indexes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return $stats;
    } catch (PDOException $e) {
      error_log("Error fetching performance stats: " . $e->getMessage());
      return [];
    }
  }
}

// Create global instance
$sqlHelper = new SQLEnhancementHelper();
?>