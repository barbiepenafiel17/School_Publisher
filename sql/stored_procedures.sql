-- ========================================
-- SQL STORED PROCEDURES FOR SCHOOL PUBLISHER PROJECT
-- ========================================
-- Set delimiter for procedure definitions
DELIMITER // -- Drop existing procedures if they exist
DROP PROCEDURE IF EXISTS ApproveArticle // DROP PROCEDURE IF EXISTS RejectArticle // DROP PROCEDURE IF EXISTS GetFilteredArticles // DROP PROCEDURE IF EXISTS RegisterUser // DROP PROCEDURE IF EXISTS CreateNotification // DROP PROCEDURE IF EXISTS GetUserDashboardStats // DROP PROCEDURE IF EXISTS BulkApproveArticles // -- ========================================
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
-- Log the action
INSERT INTO audit_log (action, created_at)
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
    p_rejection_reason
  );
-- Insert notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (v_user_id, v_message, 0, NOW());
-- Log the action
INSERT INTO audit_log (action, created_at)
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
-- Stored Procedure: Get Filtered Articles
-- Advanced filtering for newsfeed with pagination
-- ========================================
CREATE PROCEDURE GetFilteredArticles(
  IN p_user_id INT,
  IN p_institutes TEXT,
  IN p_sort_option VARCHAR(20),
  IN p_limit INT,
  IN p_offset INT
) BEGIN
DECLARE sql_query TEXT;
SET sql_query = 'SELECT 
                    a.id,
                    a.title,
                    a.abstract,
                    a.content,
                    a.created_at,
                    a.featured_image,
                    u.full_name AS author_name,
                    u.profile_picture,
                    u.institute AS author_institute,
                    COALESCE(likes.like_count, 0) AS likes,
                    COALESCE(comments.comment_count, 0) AS comments,
                    COALESCE(saves.save_count, 0) AS saves
                FROM articles a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN (
                    SELECT article_id, COUNT(*) AS like_count
                    FROM reactions 
                    WHERE reaction_type = "like"
                    GROUP BY article_id
                ) likes ON a.id = likes.article_id
                LEFT JOIN (
                    SELECT article_id, COUNT(*) AS comment_count
                    FROM comments
                    GROUP BY article_id
                ) comments ON a.id = comments.article_id
                LEFT JOIN (
                    SELECT article_id, COUNT(*) AS save_count
                    FROM saved_articles
                    GROUP BY article_id
                ) saves ON a.id = saves.article_id
                WHERE a.status = "approved"';
-- Add institute filter if not 'All'
IF p_institutes != 'All'
AND p_institutes IS NOT NULL
AND p_institutes != '' THEN
SET sql_query = CONCAT(
    sql_query,
    ' AND u.institute LIKE "%',
    p_institutes,
    '%"'
  );
END IF;
-- Add user-specific filters if user is logged in
IF p_user_id > 0 THEN
SET sql_query = CONCAT(
    sql_query,
    ' AND NOT EXISTS (
            SELECT 1 FROM hidden_articles h 
            WHERE h.article_id = a.id AND h.user_id = ',
    p_user_id,
    '
        )'
  );
END IF;
-- Add sorting
CASE
  p_sort_option
  WHEN 'old' THEN
  SET sql_query = CONCAT(sql_query, ' ORDER BY a.created_at ASC');
WHEN 'popular' THEN
SET sql_query = CONCAT(sql_query, ' ORDER BY likes DESC, comments DESC');
WHEN 'recent' THEN
SET sql_query = CONCAT(sql_query, ' ORDER BY a.created_at DESC');
ELSE
SET sql_query = CONCAT(sql_query, ' ORDER BY a.created_at DESC');
END CASE
;
-- Add pagination
SET sql_query = CONCAT(
    sql_query,
    ' LIMIT ',
    p_limit,
    ' OFFSET ',
    p_offset
  );
SET @sql = sql_query;
PREPARE stmt
FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
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
-- Log the registration
INSERT INTO audit_log (action, created_at)
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
-- Stored Procedure: Bulk Approve Articles
-- Approve multiple articles at once (admin feature)
-- ========================================
CREATE PROCEDURE BulkApproveArticles(
  IN p_article_ids TEXT,
  IN p_approval_notes TEXT
) BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE v_article_id INT;
DECLARE v_user_id INT;
DECLARE v_title VARCHAR(255);
DECLARE v_message TEXT;
DECLARE approved_count INT DEFAULT 0;
-- Create temporary table for article IDs
DROP TEMPORARY TABLE IF EXISTS temp_article_ids;
CREATE TEMPORARY TABLE temp_article_ids (article_id INT);
-- Split the comma-separated IDs and insert into temp table
SET @sql = CONCAT(
    'INSERT INTO temp_article_ids (article_id) VALUES (',
    REPLACE(p_article_ids, ',', '),('),
    ')'
  );
PREPARE stmt
FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
-- Cursor to iterate through article IDs
DECLARE article_cursor CURSOR FOR
SELECT a.id,
  a.user_id,
  a.title
FROM articles a
  JOIN temp_article_ids t ON a.id = t.article_id
WHERE a.status = 'pending';
DECLARE CONTINUE HANDLER FOR NOT FOUND
SET done = TRUE;
START TRANSACTION;
OPEN article_cursor;
article_loop: LOOP FETCH article_cursor INTO v_article_id,
v_user_id,
v_title;
IF done THEN LEAVE article_loop;
END IF;
-- Update article status
UPDATE articles
SET status = 'approved'
WHERE id = v_article_id;
-- Create notification message
SET v_message = CONCAT(
    '🎉 Your article "',
    v_title,
    '" has been approved!'
  );
IF p_approval_notes IS NOT NULL
AND p_approval_notes != '' THEN
SET v_message = CONCAT(v_message, ' Notes: ', p_approval_notes);
END IF;
-- Insert notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (v_user_id, v_message, 0, NOW());
SET approved_count = approved_count + 1;
END LOOP;
CLOSE article_cursor;
-- Log the bulk action
INSERT INTO audit_log (action, created_at)
VALUES (
    CONCAT('Bulk approved ', approved_count, ' articles'),
    NOW()
  );
COMMIT;
-- Clean up
DROP TEMPORARY TABLE temp_article_ids;
SELECT CONCAT(
    approved_count,
    ' articles approved successfully'
  ) AS result,
  'success' AS status,
  approved_count;
END // -- ========================================
-- Additional Stored Procedures
-- ========================================
-- ========================================
-- Procedure: Reject Article with Notification
-- ========================================
CREATE PROCEDURE RejectArticle(
  IN p_article_id INT,
  IN p_rejection_reason TEXT
) BEGIN
DECLARE v_user_id INT;
DECLARE v_title VARCHAR(255);
DECLARE v_message TEXT;
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE v_article_exists INT DEFAULT 0;
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Error: ', v_error_message) AS result,
  0 AS success;
END;
START TRANSACTION;
-- Check if article exists and is pending
SELECT COUNT(*) INTO v_article_exists
FROM articles
WHERE id = p_article_id
  AND status = 'pending';
IF v_article_exists = 0 THEN ROLLBACK;
SELECT 'Article not found or already processed' AS result,
  0 AS success;
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
-- Log to audit table if it exists
INSERT IGNORE INTO audit_log (action, created_at)
VALUES (
    CONCAT(
      'Article rejected: ID ',
      p_article_id,
      ' by admin'
    ),
    NOW()
  );
COMMIT;
SELECT 'Article rejected successfully' AS result,
  1 AS success;
END IF;
END // -- ========================================
-- Procedure: Get Filtered Articles (Enhanced)
-- ========================================
CREATE PROCEDURE GetFilteredArticles(
  IN p_user_id INT,
  IN p_institutes TEXT,
  IN p_sort_option VARCHAR(20),
  IN p_limit INT,
  IN p_offset INT
) BEGIN
SET @sql = 'SELECT a.*, u.full_name, u.profile_picture,
                (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = "like") AS likes,
                (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
                FROM articles a
                JOIN users u ON a.user_id = u.id
                WHERE a.status = "approved"';
-- Add hidden articles filter if user is logged in
IF p_user_id > 0 THEN
SET @sql = CONCAT(
    @sql,
    ' AND NOT EXISTS (
            SELECT 1 FROM hidden_articles h 
            WHERE h.article_id = a.id AND h.user_id = ',
    p_user_id,
    '
        )'
  );
END IF;
-- Add institute filter
IF p_institutes IS NOT NULL
AND p_institutes != 'All'
AND p_institutes != '' THEN
SET @sql = CONCAT(@sql, ' AND (');
-- Handle multiple institutes
IF LOCATE('IC', p_institutes) > 0 THEN
SET @sql = CONCAT(@sql, 'u.institute LIKE "%IC%" OR ');
END IF;
IF LOCATE('ILEGG', p_institutes) > 0 THEN
SET @sql = CONCAT(@sql, 'u.institute LIKE "%ILEGG%" OR ');
END IF;
IF LOCATE('ITed', p_institutes) > 0 THEN
SET @sql = CONCAT(@sql, 'u.institute LIKE "%ITed%" OR ');
END IF;
IF LOCATE('IAAS', p_institutes) > 0 THEN
SET @sql = CONCAT(@sql, 'u.institute LIKE "%IAAS%" OR ');
END IF;
-- Remove trailing OR and close parenthesis
SET @sql = TRIM(
    TRAILING ' OR '
    FROM @sql
  );
SET @sql = CONCAT(@sql, ')');
END IF;
-- Add sorting
CASE
  p_sort_option
  WHEN 'old' THEN
  SET @sql = CONCAT(@sql, ' ORDER BY a.created_at ASC');
WHEN 'popular' THEN
SET @sql = CONCAT(@sql, ' ORDER BY likes DESC');
ELSE
SET @sql = CONCAT(@sql, ' ORDER BY a.created_at DESC');
END CASE
;
-- Add pagination
SET @sql = CONCAT(@sql, ' LIMIT ', p_limit, ' OFFSET ', p_offset);
PREPARE stmt
FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
END // -- ========================================
-- Procedure: Register User with Validation
-- ========================================
CREATE PROCEDURE RegisterUser(
  IN p_full_name VARCHAR(255),
  IN p_email VARCHAR(255),
  IN p_password VARCHAR(255),
  IN p_role ENUM('student', 'teacher', 'admin'),
  IN p_institute VARCHAR(255)
) BEGIN
DECLARE v_user_exists INT DEFAULT 0;
DECLARE v_error_message VARCHAR(255) DEFAULT '';
DECLARE v_new_user_id INT DEFAULT 0;
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
GET DIAGNOSTICS CONDITION 1 v_error_message = MESSAGE_TEXT;
SELECT CONCAT('Registration failed: ', v_error_message) AS result,
  0 AS success,
  0 AS user_id;
END;
START TRANSACTION;
-- Check if user already exists
SELECT COUNT(*) INTO v_user_exists
FROM users
WHERE email = p_email;
IF v_user_exists > 0 THEN ROLLBACK;
SELECT 'User with this email already exists' AS result,
  0 AS success,
  0 AS user_id;
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
SET v_new_user_id = LAST_INSERT_ID();
-- Log the registration
INSERT IGNORE INTO audit_log (action, created_at)
VALUES (
    CONCAT(
      'New user registered: ',
      p_full_name,
      ' with email ',
      p_email
    ),
    NOW()
  );
-- Send welcome notification
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (
    v_new_user_id,
    'Welcome to DBCLM College Publisher! Start sharing your knowledge with the community.',
    0,
    NOW()
  );
COMMIT;
SELECT 'User registered successfully' AS result,
  1 AS success,
  v_new_user_id AS user_id;
END IF;
END // -- ========================================
-- Procedure: Report Article with Admin Notification
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
  0 AS success;
END;
START TRANSACTION;
-- Check if user already reported this article
SELECT COUNT(*) INTO v_already_reported
FROM article_reports
WHERE user_id = p_user_id
  AND article_id = p_article_id;
IF v_already_reported > 0 THEN ROLLBACK;
SELECT 'You have already reported this article' AS result,
  0 AS success;
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
  1 AS success;
END IF;
END // -- ========================================
-- Procedure: Mark Notifications as Read
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
  1 AS success;
ELSE -- Mark specific notification as read
UPDATE notifications
SET is_read = 1
WHERE id = p_notification_id
  AND user_id = p_user_id;
SELECT 'Notification marked as read' AS result,
  1 AS success;
END IF;
END // -- ========================================
-- Procedure: Get User Dashboard Statistics
-- ========================================
CREATE PROCEDURE GetUserDashboardStats(IN p_user_id INT) BEGIN
SELECT (
    SELECT COUNT(*)
    FROM articles
    WHERE user_id = p_user_id
  ) AS total_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE user_id = p_user_id
      AND status = 'approved'
  ) AS approved_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE user_id = p_user_id
      AND status = 'pending'
  ) AS pending_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE user_id = p_user_id
      AND status = 'rejected'
  ) AS rejected_articles,
  (
    SELECT COUNT(*)
    FROM comments
    WHERE user_id = p_user_id
  ) AS total_comments,
  (
    SELECT COUNT(*)
    FROM saved_articles
    WHERE user_id = p_user_id
  ) AS saved_articles,
  (
    SELECT COUNT(*)
    FROM reactions
    WHERE user_id = p_user_id
  ) AS total_reactions,
  (
    SELECT COUNT(*)
    FROM notifications
    WHERE user_id = p_user_id
      AND is_read = 0
  ) AS unread_notifications;
END // -- Reset delimiter
DELIMITER;