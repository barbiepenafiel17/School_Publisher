-- ========================================
-- SQL INDEXES FOR SCHOOL PUBLISHER PROJECT
-- Safe installation script that handles existing indexes
-- ========================================
-- Create a procedure to safely add indexes
DELIMITER // CREATE PROCEDURE SafeAddIndex(
  IN table_name VARCHAR(64),
  IN index_name VARCHAR(64),
  IN index_definition TEXT
) BEGIN
DECLARE index_exists INT DEFAULT 0;
-- Check if index exists
SELECT COUNT(*) INTO index_exists
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = table_name
  AND index_name = index_name;
-- Add index if it doesn't exist
IF index_exists = 0 THEN
SET @sql = CONCAT(
    'CREATE INDEX ',
    index_name,
    ' ON ',
    table_name,
    '(',
    index_definition,
    ')'
  );
PREPARE stmt
FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SELECT CONCAT('Created index: ', index_name) AS result;
ELSE
SELECT CONCAT('Index already exists: ', index_name) AS result;
END IF;
END // DELIMITER;
-- ========================================
-- Articles Table Indexes
-- ========================================
CALL SafeAddIndex('articles', 'idx_articles_status_new', 'status');
CALL SafeAddIndex(
  'articles',
  'idx_articles_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'articles',
  'idx_articles_created_at_new',
  'created_at'
);
CALL SafeAddIndex('articles', 'idx_articles_title_new', 'title');
CALL SafeAddIndex(
  'articles',
  'idx_articles_status_created_new',
  'status, created_at'
);
CALL SafeAddIndex(
  'articles',
  'idx_articles_status_user_new',
  'status, user_id'
);
CALL SafeAddIndex(
  'articles',
  'idx_articles_user_created_new',
  'user_id, created_at'
);
-- ========================================
-- Users Table Indexes
-- ========================================
CALL SafeAddIndex('users', 'idx_users_email_new', 'email');
CALL SafeAddIndex('users', 'idx_users_role_new', 'role');
CALL SafeAddIndex('users', 'idx_users_institute_new', 'institute');
CALL SafeAddIndex('users', 'idx_users_status_new', 'status');
CALL SafeAddIndex(
  'users',
  'idx_users_created_at_new',
  'created_at'
);
CALL SafeAddIndex(
  'users',
  'idx_users_role_institute_new',
  'role, institute'
);
CALL SafeAddIndex(
  'users',
  'idx_users_status_role_new',
  'status, role'
);
-- ========================================
-- Comments Table Indexes
-- ========================================
CALL SafeAddIndex(
  'comments',
  'idx_comments_article_id_new',
  'article_id'
);
CALL SafeAddIndex(
  'comments',
  'idx_comments_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'comments',
  'idx_comments_created_at_new',
  'created_at'
);
CALL SafeAddIndex(
  'comments',
  'idx_comments_article_created_new',
  'article_id, created_at'
);
CALL SafeAddIndex(
  'comments',
  'idx_comments_user_created_new',
  'user_id, created_at'
);
-- ========================================
-- Reactions Table Indexes
-- ========================================
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_article_id_new',
  'article_id'
);
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_type_new',
  'reaction_type'
);
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_article_type_new',
  'article_id, reaction_type'
);
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_user_type_new',
  'user_id, reaction_type'
);
CALL SafeAddIndex(
  'reactions',
  'idx_reactions_article_user_new',
  'article_id, user_id'
);
-- ========================================
-- Notifications Table Indexes
-- ========================================
CALL SafeAddIndex(
  'notifications',
  'idx_notifications_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'notifications',
  'idx_notifications_is_read_new',
  'is_read'
);
CALL SafeAddIndex(
  'notifications',
  'idx_notifications_created_at_new',
  'created_at'
);
CALL SafeAddIndex(
  'notifications',
  'idx_notifications_user_read_new',
  'user_id, is_read'
);
CALL SafeAddIndex(
  'notifications',
  'idx_notifications_user_created_new',
  'user_id, created_at'
);
-- ========================================
-- Saved Articles Table Indexes
-- ========================================
CALL SafeAddIndex(
  'saved_articles',
  'idx_saved_articles_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'saved_articles',
  'idx_saved_articles_article_id_new',
  'article_id'
);
CALL SafeAddIndex(
  'saved_articles',
  'idx_saved_articles_saved_at_new',
  'saved_at'
);
CALL SafeAddIndex(
  'saved_articles',
  'idx_saved_articles_user_saved_new',
  'user_id, saved_at'
);
-- ========================================
-- Hidden Articles Table Indexes
-- ========================================
CALL SafeAddIndex(
  'hidden_articles',
  'idx_hidden_articles_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'hidden_articles',
  'idx_hidden_articles_article_id_new',
  'article_id'
);
CALL SafeAddIndex(
  'hidden_articles',
  'idx_hidden_articles_user_article_new',
  'user_id, article_id'
);
-- ========================================
-- Admin Notifications Table Indexes
-- ========================================
CALL SafeAddIndex(
  'admin_notifications',
  'idx_admin_notifications_type_new',
  'type'
);
CALL SafeAddIndex(
  'admin_notifications',
  'idx_admin_notifications_created_at_new',
  'created_at'
);
CALL SafeAddIndex(
  'admin_notifications',
  'idx_admin_notifications_reference_id_new',
  'reference_id'
);
-- ========================================
-- Article Reports Table Indexes
-- ========================================
CALL SafeAddIndex(
  'article_reports',
  'idx_article_reports_user_id_new',
  'user_id'
);
CALL SafeAddIndex(
  'article_reports',
  'idx_article_reports_article_id_new',
  'article_id'
);
CALL SafeAddIndex(
  'article_reports',
  'idx_article_reports_reported_at_new',
  'reported_at'
);
-- ========================================
-- Cleanup
-- ========================================
DROP PROCEDURE SafeAddIndex;
SELECT 'All indexes have been processed successfully!' AS status;