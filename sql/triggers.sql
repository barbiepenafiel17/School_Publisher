-- ========================================
-- SQL TRIGGERS FOR SCHOOL PUBLISHER PROJECT
-- ========================================
-- Set delimiter for trigger definitions
DELIMITER // -- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS after_article_insert // DROP TRIGGER IF EXISTS after_article_status_update // DROP TRIGGER IF EXISTS after_user_registration // DROP TRIGGER IF EXISTS before_article_delete // DROP TRIGGER IF EXISTS after_comment_insert // DROP TRIGGER IF EXISTS after_reaction_insert // DROP TRIGGER IF EXISTS article_audit_trigger // -- ========================================
-- Trigger: After Article Insert
-- Creates notification for admin when new article is submitted
-- ========================================
CREATE TRIGGER after_article_insert
AFTER
INSERT ON articles FOR EACH ROW BEGIN -- Create admin notification for new article submission
INSERT INTO admin_notifications (
    type,
    reference_id,
    message,
    created_at
  )
VALUES (
    'new_article',
    NEW.id,
    CONCAT(
      'New article submitted: "',
      NEW.title,
      '" by user ID ',
      NEW.user_id
    ),
    NOW()
  );
-- Create notification for the author
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    NEW.user_id,
    CONCAT(
      'Your article "',
      NEW.title,
      '" has been submitted for review. You will be notified once it\'s reviewed.'
    ),
    0,
    NOW()
  );
END // -- ========================================
-- Trigger: After Article Status Update
-- Creates notifications when article status changes
-- ========================================
CREATE TRIGGER after_article_status_update
AFTER
UPDATE ON articles FOR EACH ROW BEGIN -- Only trigger if status actually changed
  IF OLD.status != NEW.status THEN -- Handle approval
  IF NEW.status = 'approved'
  AND OLD.status = 'pending' THEN
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    NEW.user_id,
    CONCAT(
      '🎉 Great news! Your article "',
      NEW.title,
      '" has been approved and is now live!'
    ),
    0,
    NOW()
  );
END IF;
-- Handle rejection
IF NEW.status = 'rejected'
AND OLD.status = 'pending' THEN
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    NEW.user_id,
    CONCAT(
      '❌ Your article "',
      NEW.title,
      '" has been rejected. ',
      COALESCE(
        NEW.feedback,
        'Please check the feedback and resubmit if needed.'
      )
    ),
    0,
    NOW()
  );
END IF;
-- Create admin notification for status changes
INSERT INTO admin_notifications (
    type,
    reference_id,
    message,
    created_at
  )
VALUES (
    'status_change',
    NEW.id,
    CONCAT(
      'Article "',
      NEW.title,
      '" status changed from ',
      OLD.status,
      ' to ',
      NEW.status
    ),
    NOW()
  );
END IF;
END // -- ========================================
-- Trigger: After User Registration
-- Creates welcome notification and admin alert
-- ========================================
CREATE TRIGGER after_user_registration
AFTER
INSERT ON users FOR EACH ROW BEGIN -- Create welcome notification for new user
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    NEW.id,
    CONCAT(
      'Welcome to DBCLM College Publisher, ',
      NEW.full_name,
      '! 🎓 Start sharing your knowledge with the community.'
    ),
    0,
    NOW()
  );
-- Create admin notification for new user registration
INSERT INTO admin_notifications (
    type,
    reference_id,
    message,
    created_at
  )
VALUES (
    'new_user',
    NEW.id,
    CONCAT(
      'New user registered: ',
      NEW.full_name,
      ' (',
      NEW.email,
      ') - ',
      NEW.role,
      ' from ',
      NEW.institute
    ),
    NOW()
  );
END // -- ========================================
-- Trigger: Before Article Delete
-- Prevents actual deletion and converts to soft delete
-- ========================================
CREATE TRIGGER before_article_delete BEFORE DELETE ON articles FOR EACH ROW BEGIN -- Instead of deleting, update status to 'deleted'
INSERT INTO articles (
    id,
    user_id,
    title,
    abstract,
    content,
    status,
    created_at,
    featured_image,
    allow_comments,
    feedback
  )
VALUES (
    OLD.id,
    OLD.user_id,
    OLD.title,
    OLD.abstract,
    OLD.content,
    'deleted',
    OLD.created_at,
    OLD.featured_image,
    OLD.allow_comments,
    OLD.feedback
  ) ON DUPLICATE KEY
UPDATE status = 'deleted';
-- Log the deletion attempt
INSERT INTO admin_notifications (
    type,
    reference_id,
    message,
    created_at
  )
VALUES (
    'article_deleted',
    OLD.id,
    CONCAT(
      'Article "',
      OLD.title,
      '" was marked as deleted'
    ),
    NOW()
  );
-- Signal to cancel the actual delete
SIGNAL SQLSTATE '45000'
SET MESSAGE_TEXT = 'Article marked as deleted instead of physical deletion';
END // -- ========================================
-- Trigger: After Comment Insert
-- Creates notification for article author when someone comments
-- ========================================
CREATE TRIGGER after_comment_insert
AFTER
INSERT ON comments FOR EACH ROW BEGIN
DECLARE v_article_author_id INT;
DECLARE v_article_title VARCHAR(255);
DECLARE v_commenter_name VARCHAR(255);
-- Get article author details
SELECT a.user_id,
  a.title INTO v_article_author_id,
  v_article_title
FROM articles a
WHERE a.id = NEW.article_id;
-- Get commenter name
SELECT full_name INTO v_commenter_name
FROM users
WHERE id = NEW.user_id;
-- Only notify if commenter is not the article author
IF v_article_author_id != NEW.user_id THEN
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    v_article_author_id,
    CONCAT(
      '💬 ',
      v_commenter_name,
      ' commented on your article "',
      v_article_title,
      '"'
    ),
    0,
    NOW()
  );
END IF;
END // -- ========================================
-- Trigger: After Reaction Insert
-- Creates notification for article author when someone likes their article
-- ========================================
CREATE TRIGGER after_reaction_insert
AFTER
INSERT ON reactions FOR EACH ROW BEGIN
DECLARE v_article_author_id INT;
DECLARE v_article_title VARCHAR(255);
DECLARE v_reactor_name VARCHAR(255);
-- Only process likes (not dislikes)
IF NEW.reaction_type = 'like' THEN -- Get article author details
SELECT a.user_id,
  a.title INTO v_article_author_id,
  v_article_title
FROM articles a
WHERE a.id = NEW.article_id;
-- Get reactor name
SELECT full_name INTO v_reactor_name
FROM users
WHERE id = NEW.user_id;
-- Only notify if reactor is not the article author
IF v_article_author_id != NEW.user_id THEN
INSERT INTO notifications (
    user_id,
    message,
    is_read,
    created_at
  )
VALUES (
    v_article_author_id,
    CONCAT(
      '👍 ',
      v_reactor_name,
      ' liked your article "',
      v_article_title,
      '"'
    ),
    0,
    NOW()
  );
END IF;
END IF;
END // -- ========================================
-- Trigger: Article Audit Trigger
-- Logs all article changes for audit purposes
-- ========================================
CREATE TRIGGER article_audit_trigger
AFTER
UPDATE ON articles FOR EACH ROW BEGIN -- Create audit log table if it doesn't exist
  CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50),
    operation VARCHAR(10),
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    changed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
-- Log the change
INSERT INTO audit_log (
    table_name,
    operation,
    record_id,
    old_values,
    new_values,
    created_at
  )
VALUES (
    'articles',
    'UPDATE',
    NEW.id,
    CONCAT('status:', OLD.status, ',title:', OLD.title),
    CONCAT('status:', NEW.status, ',title:', NEW.title),
    NOW()
  );
END // -- Reset delimiter
DELIMITER;
-- ========================================
-- Additional Helper Tables for Triggers
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
-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_audit_log_table_record ON audit_log(table_name, record_id);
CREATE INDEX IF NOT EXISTS idx_audit_log_created_at ON audit_log(created_at);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_type ON admin_notifications(type);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_read ON admin_notifications(is_read);