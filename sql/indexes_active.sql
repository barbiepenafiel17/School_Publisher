-- ========================================
-- ACTIVE SQL INDEXES FOR SCHOOL PUBLISHER PROJECT
-- Performance optimization indexes - WORKING VERSION
-- ========================================

USE dbclm_college;

-- ========================================
-- Articles Table Indexes
-- ========================================

-- Check and create indexes for articles table
-- Primary performance indexes for articles
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_articles_user_id ON articles(user_id);
CREATE INDEX idx_articles_created_at ON articles(created_at);
CREATE INDEX idx_articles_title ON articles(title);

-- Composite indexes for common query patterns
CREATE INDEX idx_articles_status_created ON articles(status, created_at);
CREATE INDEX idx_articles_status_user ON articles(status, user_id);
CREATE INDEX idx_articles_user_created ON articles(user_id, created_at);

-- ========================================
-- Users Table Indexes
-- ========================================

-- Essential user lookup indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_institute ON users(institute);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Composite indexes for user queries
CREATE INDEX idx_users_role_institute ON users(role, institute);
CREATE INDEX idx_users_status_role ON users(status, role);

-- ========================================
-- Comments Table Indexes
-- ========================================

-- Comment lookup and sorting indexes
CREATE INDEX idx_comments_article_id ON comments(article_id);
CREATE INDEX idx_comments_user_id ON comments(user_id);
CREATE INDEX idx_comments_created_at ON comments(created_at);

-- Composite index for article comments
CREATE INDEX idx_comments_article_created ON comments(article_id, created_at);
CREATE INDEX idx_comments_user_created ON comments(user_id, created_at);

-- ========================================
-- Reactions Table Indexes
-- ========================================

-- Reaction lookup indexes
CREATE INDEX idx_reactions_article_id ON reactions(article_id);
CREATE INDEX idx_reactions_user_id ON reactions(user_id);
CREATE INDEX idx_reactions_type ON reactions(reaction_type);

-- Composite index for unique reactions
CREATE INDEX idx_reactions_article_user ON reactions(article_id, user_id);
CREATE INDEX idx_reactions_article_type ON reactions(article_id, reaction_type);

-- ========================================
-- Notifications Table Indexes
-- ========================================

-- Notification management indexes
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);

-- Composite index for user notification queries
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_notifications_user_created ON notifications(user_id, created_at);

-- ========================================
-- Saved Articles Table Indexes
-- ========================================

-- Saved articles indexes
CREATE INDEX idx_saved_articles_user_id ON saved_articles(user_id);
CREATE INDEX idx_saved_articles_article_id ON saved_articles(article_id);
CREATE INDEX idx_saved_articles_saved_at ON saved_articles(saved_at);

-- Composite index for saved articles queries
CREATE INDEX idx_saved_articles_user_saved ON saved_articles(user_id, saved_at);

-- ========================================
-- Hidden Articles Table Indexes (if exists)
-- ========================================

-- Hidden articles indexes
CREATE INDEX idx_hidden_articles_user_id ON hidden_articles(user_id);
CREATE INDEX idx_hidden_articles_article_id ON hidden_articles(article_id);
CREATE INDEX idx_hidden_articles_user_article ON hidden_articles(user_id, article_id);

-- ========================================
-- Admin Notifications Table Indexes
-- ========================================

-- Admin notification indexes  
CREATE INDEX idx_admin_notifications_type ON admin_notifications(type);
CREATE INDEX idx_admin_notifications_read ON admin_notifications(is_read);
CREATE INDEX idx_admin_notifications_created ON admin_notifications(created_at);
CREATE INDEX idx_admin_notifications_reference ON admin_notifications(reference_id);

-- ========================================
-- VERIFICATION - Show created indexes
-- ========================================

SELECT 'INDEXES CREATED SUCCESSFULLY!' as Status;

-- Show indexes for main tables
SELECT 'Articles table indexes:' as Info;
SHOW INDEX FROM articles WHERE Key_name != 'PRIMARY';

SELECT 'Users table indexes:' as Info;
SHOW INDEX FROM users WHERE Key_name != 'PRIMARY';

SELECT 'Comments table indexes:' as Info;
SHOW INDEX FROM comments WHERE Key_name != 'PRIMARY';

SELECT 'Reactions table indexes:' as Info;
SHOW INDEX FROM reactions WHERE Key_name != 'PRIMARY';

SELECT 'Notifications table indexes:' as Info;
SHOW INDEX FROM notifications WHERE Key_name != 'PRIMARY';

-- ========================================
-- PERFORMANCE ANALYSIS
-- ========================================

-- Analyze tables for optimization
ANALYZE TABLE articles, users, comments, reactions, notifications, saved_articles;

SELECT '✅ All performance indexes have been created and analyzed!' as Result;
