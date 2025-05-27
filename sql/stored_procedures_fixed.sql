-- ========================================
-- SQL STORED PROCEDURES FOR SCHOOL PUBLISHER PROJECT
-- FIXED VERSION WITH PROPER SYNTAX
-- ========================================
-- Set delimiter for procedure definitions
DELIMITER // -- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS ApproveArticle // DROP PROCEDURE IF EXISTS RejectArticle // DROP PROCEDURE IF EXISTS GetFilteredArticles // DROP PROCEDURE IF EXISTS RegisterUser // DROP PROCEDURE IF EXISTS CreateNotification // DROP PROCEDURE IF EXISTS GetUserDashboardStats // DROP PROCEDURE IF EXISTS ReportArticle // DROP PROCEDURE IF EXISTS MarkNotificationRead // -- ========================================
-- Stored Procedure: Approve Article
-- Approves an article and sends notification to author
-- ========================================
CREATE PROCEDURE ApproveArticle(
  IN p_article_id INT,
  IN p_approval_notes TEXT
) BEGIN
DECLARE v_user_id INT;
DECLARE v_title VARCHAR(255);
DECLARE v_message TEXT;
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE article_exists INT DEFAULT 0;
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Error: ', v_error_message) AS result,
  'error' AS status;
END;
START TRANSACTION;
-- Check if article exists and is pending
SELECT COUNT(*) INTO article_exists
FROM articles
WHERE id = p_article_id
  AND status = 'pending';
IF article_exists = 0 THEN ROLLBACK;
SELECT 'Article not found or already processed' AS result,
  'error' AS status;
ELSE -- Get article details
SELECT user_id,
  title INTO v_user_id,
  v_title
FROM articles
WHERE id = p_article_id;
-- Update article status
UPDATE articles
SET status = 'approved'
WHERE id = p_article_id;
-- Create notification message
SET v_message = CONCAT(
    '🎉 Your article "',
    v_title,
    '" has been approved and is now published!'
  );
IF p_approval_notes IS NOT NULL
AND p_approval_notes != '' THEN
SET v_message = CONCAT(v_message, ' Admin notes: ', p_approval_notes);
END IF;
-- Insert notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (v_user_id, v_message, 0, NOW());
-- Log the action (only if audit_log table exists)
INSERT IGNORE INTO audit_log (action, created_at)
VALUES (
    CONCAT(
      'Article approved: ID ',
      p_article_id,
      ' - ',
      v_title
    ),
    NOW()
  );
COMMIT;
SELECT 'Article approved successfully' AS result,
  'success' AS status,
  p_article_id AS article_id;
END IF;
END // -- ========================================
-- Stored Procedure: Reject Article
-- Rejects an article and sends notification to author
-- ========================================
CREATE PROCEDURE RejectArticle(
  IN p_article_id INT,
  IN p_rejection_reason TEXT
) BEGIN
DECLARE v_user_id INT;
DECLARE v_title VARCHAR(255);
DECLARE v_message TEXT;
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE article_exists INT DEFAULT 0;
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Error: ', v_error_message) AS result,
  'error' AS status;
END;
START TRANSACTION;
-- Check if article exists and is pending
SELECT COUNT(*) INTO article_exists
FROM articles
WHERE id = p_article_id
  AND status = 'pending';
IF article_exists = 0 THEN ROLLBACK;
SELECT 'Article not found or already processed' AS result,
  'error' AS status;
ELSE -- Get article details
SELECT user_id,
  title INTO v_user_id,
  v_title
FROM articles
WHERE id = p_article_id;
-- Update article status and add feedback
UPDATE articles
SET status = 'rejected',
  feedback = p_rejection_reason
WHERE id = p_article_id;
-- Create notification message
SET v_message = CONCAT(
    '❌ Your article "',
    v_title,
    '" has been rejected. Reason: ',
    COALESCE(p_rejection_reason, 'No reason provided')
  );
-- Insert notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (v_user_id, v_message, 0, NOW());
-- Log the action (only if audit_log table exists)
INSERT IGNORE INTO audit_log (action, created_at)
VALUES (
    CONCAT(
      'Article rejected: ID ',
      p_article_id,
      ' - ',
      v_title
    ),
    NOW()
  );
COMMIT;
SELECT 'Article rejected successfully' AS result,
  'success' AS status,
  p_article_id AS article_id;
END IF;
END // -- ========================================
-- Stored Procedure: Register User
-- User registration with validation and logging
-- ========================================
CREATE PROCEDURE RegisterUser(
  IN p_full_name VARCHAR(255),
  IN p_email VARCHAR(255),
  IN p_password VARCHAR(255),
  IN p_role ENUM('student', 'teacher', 'admin'),
  IN p_institute VARCHAR(255)
) BEGIN
DECLARE v_user_exists INT DEFAULT 0;
DECLARE v_user_id INT;
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Registration failed: ', v_error_message) AS result,
  'error' AS status;
END;
START TRANSACTION;
-- Check if user already exists
SELECT COUNT(*) INTO v_user_exists
FROM users
WHERE email = p_email;
IF v_user_exists > 0 THEN ROLLBACK;
SELECT 'User with this email already exists' AS result,
  'error' AS status;
ELSE -- Insert new user
INSERT INTO users (
    full_name,
    email,
    password,
    role,
    institute,
    status,
    created_at
  )
VALUES (
    p_full_name,
    p_email,
    p_password,
    p_role,
    p_institute,
    'active',
    NOW()
  );
SET v_user_id = LAST_INSERT_ID();
-- Create welcome notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (
    v_user_id,
    CONCAT(
      'Welcome to School Publisher, ',
      p_full_name,
      '! 🎉'
    ),
    0,
    NOW()
  );
-- Log the registration (only if audit_log table exists)
INSERT IGNORE INTO audit_log (action, created_at)
VALUES (
    CONCAT(
      'New user registered: ',
      p_full_name,
      ' (',
      p_email,
      ')'
    ),
    NOW()
  );
COMMIT;
SELECT 'User registered successfully' AS result,
  'success' AS status,
  v_user_id AS user_id;
END IF;
END // -- ========================================
-- Stored Procedure: Create Notification
-- Centralized notification creation
-- ========================================
CREATE PROCEDURE CreateNotification(
  IN p_user_id INT,
  IN p_message TEXT,
  IN p_type VARCHAR(50)
) BEGIN
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT(
    'Notification creation failed: ',
    v_error_message
  ) AS result,
  'error' AS status;
END;
-- Insert notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (p_user_id, p_message, 0, NOW());
SELECT 'Notification created successfully' AS result,
  'success' AS status;
END // -- ========================================
-- Stored Procedure: Get User Dashboard Stats
-- Comprehensive user statistics for dashboard
-- ========================================
CREATE PROCEDURE GetUserDashboardStats(IN p_user_id INT) BEGIN
SELECT u.full_name,
  u.email,
  u.role,
  u.institute,
  u.created_at AS member_since,
  COALESCE(stats.total_articles, 0) AS total_articles,
  COALESCE(stats.approved_articles, 0) AS approved_articles,
  COALESCE(stats.pending_articles, 0) AS pending_articles,
  COALESCE(stats.total_comments, 0) AS total_comments,
  COALESCE(stats.saved_articles_count, 0) AS saved_articles,
  COALESCE(notif.unread_count, 0) AS unread_notifications,
  COALESCE(likes.total_likes_received, 0) AS total_likes_received
FROM users u
  LEFT JOIN user_stats_view stats ON u.id = stats.id
  LEFT JOIN notification_summary_view notif ON u.id = notif.user_id
  LEFT JOIN (
    SELECT a.user_id,
      COUNT(r.id) AS total_likes_received
    FROM articles a
      LEFT JOIN reactions r ON a.id = r.article_id
      AND r.reaction_type = 'like'
    WHERE a.user_id = p_user_id
    GROUP BY a.user_id
  ) likes ON u.id = likes.user_id
WHERE u.id = p_user_id;
END // -- ========================================
-- Stored Procedure: Report Article
-- Creates a report entry and notifies admin
-- ========================================
CREATE PROCEDURE ReportArticle(
  IN p_user_id INT,
  IN p_article_id INT,
  IN p_reason TEXT
) BEGIN
DECLARE v_already_reported INT DEFAULT 0;
DECLARE v_article_title VARCHAR(255);
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Error: ', v_error_message) AS result,
  'error' AS status;
END;
START TRANSACTION;
-- Check if user already reported this article
SELECT COUNT(*) INTO v_already_reported
FROM article_reports
WHERE user_id = p_user_id
  AND article_id = p_article_id;
IF v_already_reported > 0 THEN ROLLBACK;
SELECT 'You have already reported this article' AS result,
  'error' AS status;
ELSE -- Get article title
SELECT title INTO v_article_title
FROM articles
WHERE id = p_article_id;
-- Insert the report
INSERT INTO article_reports (user_id, article_id, reason, reported_at)
VALUES (p_user_id, p_article_id, p_reason, NOW());
-- Create admin notification
INSERT INTO admin_notifications (type, reference_id, message, created_at)
VALUES (
    'report',
    p_article_id,
    CONCAT(
      'Article "',
      COALESCE(v_article_title, 'Unknown'),
      '" has been reported. Reason: ',
      COALESCE(p_reason, 'No reason provided')
    ),
    NOW()
  );
COMMIT;
SELECT 'Article reported successfully' AS result,
  'success' AS status;
END IF;
END // -- ========================================
-- Stored Procedure: Mark Notifications as Read
-- Marks notifications as read for a user
-- ========================================
CREATE PROCEDURE MarkNotificationRead(
  IN p_user_id INT,
  IN p_notification_id INT
) BEGIN IF p_notification_id IS NULL
OR p_notification_id = 0 THEN -- Mark all notifications as read for user
UPDATE notifications
SET is_read = 1
WHERE user_id = p_user_id
  AND is_read = 0;
SELECT 'All notifications marked as read' AS result,
  'success' AS status;
ELSE -- Mark specific notification as read
UPDATE notifications
SET is_read = 1
WHERE id = p_notification_id
  AND user_id = p_user_id;
SELECT 'Notification marked as read' AS result,
  'success' AS status;
END IF;
END // -- ========================================
-- Stored Procedure: Get Filtered Articles
-- Advanced filtering for newsfeed with pagination
-- ========================================
CREATE PROCEDURE GetFilteredArticles(
  IN p_user_id INT,
  IN p_institutes VARCHAR(500),
  IN p_sort_option VARCHAR(20),
  IN p_limit INT,
  IN p_offset INT
) BEGIN -- Simple version without dynamic SQL to ensure compatibility
IF p_sort_option = 'popular' THEN
SELECT a.id,
  a.title,
  a.abstract,
  a.content,
  a.created_at,
  a.featured_image,
  u.full_name AS author_name,
  u.profile_picture,
  u.institute AS author_institute,
  COALESCE(likes.like_count, 0) AS likes,
  COALESCE(comments.comment_count, 0) AS comments
FROM articles a
  JOIN users u ON a.user_id = u.id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS like_count
    FROM reactions
    WHERE reaction_type = 'like'
    GROUP BY article_id
  ) likes ON a.id = likes.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS comment_count
    FROM comments
    GROUP BY article_id
  ) comments ON a.id = comments.article_id
WHERE a.status = 'approved'
ORDER BY likes DESC,
  comments DESC
LIMIT p_limit OFFSET p_offset;
ELSEIF p_sort_option = 'old' THEN
SELECT a.id,
  a.title,
  a.abstract,
  a.content,
  a.created_at,
  a.featured_image,
  u.full_name AS author_name,
  u.profile_picture,
  u.institute AS author_institute,
  COALESCE(likes.like_count, 0) AS likes,
  COALESCE(comments.comment_count, 0) AS comments
FROM articles a
  JOIN users u ON a.user_id = u.id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS like_count
    FROM reactions
    WHERE reaction_type = 'like'
    GROUP BY article_id
  ) likes ON a.id = likes.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS comment_count
    FROM comments
    GROUP BY article_id
  ) comments ON a.id = comments.article_id
WHERE a.status = 'approved'
ORDER BY a.created_at ASC
LIMIT p_limit OFFSET p_offset;
ELSE -- Default: recent
SELECT a.id,
  a.title,
  a.abstract,
  a.content,
  a.created_at,
  a.featured_image,
  u.full_name AS author_name,
  u.profile_picture,
  u.institute AS author_institute,
  COALESCE(likes.like_count, 0) AS likes,
  COALESCE(comments.comment_count, 0) AS comments
FROM articles a
  JOIN users u ON a.user_id = u.id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS like_count
    FROM reactions
    WHERE reaction_type = 'like'
    GROUP BY article_id
  ) likes ON a.id = likes.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS comment_count
    FROM comments
    GROUP BY article_id
  ) comments ON a.id = comments.article_id
WHERE a.status = 'approved'
ORDER BY a.created_at DESC
LIMIT p_limit OFFSET p_offset;
END IF;
END // -- Reset delimiter
DELIMITER;
-- ========================================
-- Create required tables if they don't exist
-- ========================================
-- Create audit_log table if it doesn't exist
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(50),
  operation VARCHAR(10),
  record_id INT,
  old_values TEXT,
  new_values TEXT,
  changed_by INT,
  action TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Create admin_notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS admin_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) DEFAULT 'info',
  reference_id INT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  link VARCHAR(255) DEFAULT NULL
);
-- Create article_reports table if it doesn't exist
CREATE TABLE IF NOT EXISTS article_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  article_id INT NOT NULL,
  reason TEXT,
  reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_article_report (user_id, article_id)
);