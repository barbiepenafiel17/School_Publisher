-- ========================================
-- SQL INDEXES FOR SCHOOL PUBLISHER PROJECT
-- Performance optimization indexes
-- ========================================
-- Note: Some indexes might already exist. This script will skip existing ones.
-- ========================================
-- Articles Table Indexes
-- ========================================
-- Primary performance indexes for articles
CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status);
CREATE INDEX IF NOT EXISTS idx_articles_user_id ON articles(user_id);
CREATE INDEX IF NOT EXISTS idx_articles_created_at ON articles(created_at);
CREATE INDEX IF NOT EXISTS idx_articles_title ON articles(title);
-- Composite indexes for common query patterns
CREATE INDEX IF NOT EXISTS idx_articles_status_created ON articles(status, created_at);
CREATE INDEX IF NOT EXISTS idx_articles_status_user ON articles(status, user_id);
CREATE INDEX IF NOT EXISTS idx_articles_user_created ON articles(user_id, created_at);
-- Full-text search index for content search (skip if exists)
-- ALTER TABLE articles ADD FULLTEXT(title, abstract, content);
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
-- Core comment indexes
CREATE INDEX idx_comments_article_id ON comments(article_id);
CREATE INDEX idx_comments_user_id ON comments(user_id);
CREATE INDEX idx_comments_created_at ON comments(created_at);
-- Composite indexes for comment queries
CREATE INDEX idx_comments_article_created ON comments(article_id, created_at);
CREATE INDEX idx_comments_user_created ON comments(user_id, created_at);
-- ========================================
-- Reactions Table Indexes
-- ========================================
-- Primary reaction indexes
CREATE INDEX idx_reactions_article_id ON reactions(article_id);
CREATE INDEX idx_reactions_user_id ON reactions(user_id);
CREATE INDEX idx_reactions_type ON reactions(reaction_type);
-- Composite indexes for reaction queries
CREATE INDEX idx_reactions_article_type ON reactions(article_id, reaction_type);
CREATE INDEX idx_reactions_user_type ON reactions(user_id, reaction_type);
CREATE INDEX idx_reactions_article_user ON reactions(article_id, user_id);
-- Unique constraint to prevent duplicate reactions
CREATE UNIQUE INDEX idx_reactions_unique ON reactions(article_id, user_id, reaction_type);
-- ========================================
-- Notifications Table Indexes
-- ========================================
-- Core notification indexes
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
-- Composite indexes for notification queries
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_notifications_user_created ON notifications(user_id, created_at);
CREATE INDEX idx_notifications_read_created ON notifications(is_read, created_at);
-- ========================================
-- Saved Articles Table Indexes
-- ========================================
-- Core saved article indexes
CREATE INDEX idx_saved_articles_user_id ON saved_articles(user_id);
CREATE INDEX idx_saved_articles_article_id ON saved_articles(article_id);
CREATE INDEX idx_saved_articles_saved_at ON saved_articles(saved_at);
-- Composite indexes
CREATE INDEX idx_saved_articles_user_saved ON saved_articles(user_id, saved_at);
-- Unique constraint to prevent duplicate saves
CREATE UNIQUE INDEX idx_saved_articles_unique ON saved_articles(user_id, article_id);
-- ========================================
-- Hidden Articles Table Indexes
-- ========================================
-- Core hidden article indexes
CREATE INDEX idx_hidden_articles_user_id ON hidden_articles(user_id);
CREATE INDEX idx_hidden_articles_article_id ON hidden_articles(article_id);
-- Composite index for efficient lookups
CREATE INDEX idx_hidden_articles_user_article ON hidden_articles(user_id, article_id);
-- Unique constraint to prevent duplicate hides
CREATE UNIQUE INDEX idx_hidden_articles_unique ON hidden_articles(user_id, article_id);
-- ========================================
-- Admin Notifications Table Indexes
-- ========================================
-- Core admin notification indexes
CREATE INDEX idx_admin_notifications_type ON admin_notifications(type);
CREATE INDEX idx_admin_notifications_created_at ON admin_notifications(created_at);
CREATE INDEX idx_admin_notifications_reference_id ON admin_notifications(reference_id);
-- Composite indexes
CREATE INDEX idx_admin_notifications_type_created ON admin_notifications(type, created_at);
-- ========================================
-- Article Reports Table Indexes
-- ========================================
-- Core report indexes
CREATE INDEX idx_article_reports_user_id ON article_reports(user_id);
CREATE INDEX idx_article_reports_article_id ON article_reports(article_id);
CREATE INDEX idx_article_reports_reported_at ON article_reports(reported_at);
-- Composite indexes
CREATE INDEX idx_article_reports_article_reported ON article_reports(article_id, reported_at);
-- ========================================
-- Audit Log Table Indexes
-- ========================================
-- Core audit log indexes
CREATE INDEX idx_audit_log_created_at ON audit_log(created_at);
CREATE INDEX idx_audit_log_action ON audit_log(action);
-- ========================================
-- Announcements Table Indexes
-- ========================================
-- Core announcement indexes
CREATE INDEX idx_announcements_status ON announcements(status);
CREATE INDEX idx_announcements_audience ON announcements(audience);
CREATE INDEX idx_announcements_publication_date ON announcements(publication_date);
CREATE INDEX idx_announcements_expiry_date ON announcements(expiry_date);
-- Composite indexes for announcement queries
CREATE INDEX idx_announcements_status_publication ON announcements(status, publication_date);
CREATE INDEX idx_announcements_audience_status ON announcements(audience, status);
-- ========================================
-- Article Logs Table Indexes
-- ========================================
-- Core article log indexes
CREATE INDEX idx_article_logs_article_id ON article_logs(article_id);
CREATE INDEX idx_article_logs_action ON article_logs(action);
CREATE INDEX idx_article_logs_timestamp ON article_logs(timestamp);
-- Composite indexes
CREATE INDEX idx_article_logs_article_timestamp ON article_logs(article_id, timestamp);
-- ========================================
-- User Logs Table Indexes
-- ========================================
-- Core user log indexes
CREATE INDEX idx_user_logs_user_id ON user_logs(user_id);
CREATE INDEX idx_user_logs_action ON user_logs(action);
CREATE INDEX idx_user_logs_timestamp ON user_logs(timestamp);
-- Composite indexes
CREATE INDEX idx_user_logs_user_timestamp ON user_logs(user_id, timestamp);
-- ========================================
-- Institutes Table Indexes
-- ========================================
-- Core institute indexes
CREATE INDEX idx_institutes_name ON institutes(name);
CREATE INDEX idx_institutes_status ON institutes(status);
-- ========================================
-- Performance Analysis Query
-- Use this to check index usage after implementation
-- ========================================
/*
 -- Query to check index usage:
 SHOW INDEX FROM articles;
 SHOW INDEX FROM users;
 SHOW INDEX FROM comments;
 SHOW INDEX FROM reactions;
 SHOW INDEX FROM notifications;
 
 -- Query to analyze slow queries:
 SELECT * FROM information_schema.processlist WHERE command != 'Sleep';
 
 -- Query to check table sizes:
 SELECT 
 table_name AS 'Table',
 ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
 FROM information_schema.TABLES 
 WHERE table_schema = 'dbclm_college'
 ORDER BY (data_length + index_length) DESC;
 */
CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status);
CREATE INDEX IF NOT EXISTS idx_articles_user_id ON articles(user_id);
CREATE INDEX IF NOT EXISTS idx_articles_created_at ON articles(created_at);
CREATE INDEX IF NOT EXISTS idx_articles_status_created ON articles(status, created_at);
CREATE INDEX IF NOT EXISTS idx_articles_title ON articles(title);
CREATE INDEX IF NOT EXISTS idx_articles_featured_image ON articles(featured_image);
-- Composite indexes for common query patterns
CREATE INDEX IF NOT EXISTS idx_articles_status_user_id ON articles(status, user_id);
CREATE INDEX IF NOT EXISTS idx_articles_user_status_created ON articles(user_id, status, created_at);
-- ========================================
-- Users Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_institute ON users(institute);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);
CREATE INDEX IF NOT EXISTS idx_users_full_name ON users(full_name);
-- Composite indexes for authentication and filtering
CREATE INDEX IF NOT EXISTS idx_users_email_status ON users(email, status);
CREATE INDEX IF NOT EXISTS idx_users_role_institute ON users(role, institute);
CREATE INDEX IF NOT EXISTS idx_users_institute_status ON users(institute, status);
-- ========================================
-- Comments Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_comments_article_id ON comments(article_id);
CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id);
CREATE INDEX IF NOT EXISTS idx_comments_created_at ON comments(created_at);
-- Composite indexes for comment queries
CREATE INDEX IF NOT EXISTS idx_comments_article_created ON comments(article_id, created_at);
CREATE INDEX IF NOT EXISTS idx_comments_user_created ON comments(user_id, created_at);
-- ========================================
-- Reactions Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_reactions_article_id ON reactions(article_id);
CREATE INDEX IF NOT EXISTS idx_reactions_user_id ON reactions(user_id);
CREATE INDEX IF NOT EXISTS idx_reactions_type ON reactions(reaction_type);
CREATE INDEX IF NOT EXISTS idx_reactions_created_at ON reactions(created_at);
-- Composite indexes for reaction queries
CREATE INDEX IF NOT EXISTS idx_reactions_article_type ON reactions(article_id, reaction_type);
CREATE INDEX IF NOT EXISTS idx_reactions_user_article ON reactions(user_id, article_id);
CREATE INDEX IF NOT EXISTS idx_reactions_article_type_created ON reactions(article_id, reaction_type, created_at);
-- ========================================
-- Notifications Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at);
-- Composite indexes for notification queries
CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_user_read_created ON notifications(user_id, is_read, created_at);
-- ========================================
-- Saved Articles Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_saved_articles_user_id ON saved_articles(user_id);
CREATE INDEX IF NOT EXISTS idx_saved_articles_article_id ON saved_articles(article_id);
CREATE INDEX IF NOT EXISTS idx_saved_articles_saved_at ON saved_articles(saved_at);
-- Composite indexes
CREATE INDEX IF NOT EXISTS idx_saved_articles_user_saved ON saved_articles(user_id, saved_at);
CREATE INDEX IF NOT EXISTS idx_saved_articles_user_article ON saved_articles(user_id, article_id);
-- ========================================
-- Hidden Articles Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_hidden_articles_user_id ON hidden_articles(user_id);
CREATE INDEX IF NOT EXISTS idx_hidden_articles_article_id ON hidden_articles(article_id);
CREATE INDEX IF NOT EXISTS idx_hidden_articles_hidden_at ON hidden_articles(hidden_at);
-- Composite index for hiding logic
CREATE INDEX IF NOT EXISTS idx_hidden_articles_user_article ON hidden_articles(user_id, article_id);
-- ========================================
-- Admin Notifications Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_admin_notifications_type ON admin_notifications(type);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_is_read ON admin_notifications(is_read);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_created_at ON admin_notifications(created_at);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_reference_id ON admin_notifications(reference_id);
-- Composite indexes
CREATE INDEX IF NOT EXISTS idx_admin_notifications_type_read ON admin_notifications(type, is_read);
CREATE INDEX IF NOT EXISTS idx_admin_notifications_read_created ON admin_notifications(is_read, created_at);
-- ========================================
-- Article Reports Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_article_reports_user_id ON article_reports(user_id);
CREATE INDEX IF NOT EXISTS idx_article_reports_article_id ON article_reports(article_id);
CREATE INDEX IF NOT EXISTS idx_article_reports_reported_at ON article_reports(reported_at);
-- Composite indexes
CREATE INDEX IF NOT EXISTS idx_article_reports_user_article ON article_reports(user_id, article_id);
CREATE INDEX IF NOT EXISTS idx_article_reports_article_reported ON article_reports(article_id, reported_at);
-- ========================================
-- Announcements Table Indexes
-- ========================================
CREATE INDEX IF NOT EXISTS idx_announcements_status ON announcements(status);
CREATE INDEX IF NOT EXISTS idx_announcements_audience ON announcements(audience);
CREATE INDEX IF NOT EXISTS idx_announcements_publication_date ON announcements(publication_date);
CREATE INDEX IF NOT EXISTS idx_announcements_created_at ON announcements(created_at);
-- Composite indexes
CREATE INDEX IF NOT EXISTS idx_announcements_status_audience ON announcements(status, audience);
CREATE INDEX IF NOT EXISTS idx_announcements_status_publication ON announcements(status, publication_date);
-- ========================================
-- Audit Log Table Indexes (if exists)
-- ========================================
CREATE INDEX IF NOT EXISTS idx_audit_log_created_at ON audit_log(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_log_action ON audit_log(action);
-- ========================================
-- Performance Analysis Queries
-- Use these to monitor index usage and performance
-- ========================================
-- Check index usage
-- SHOW INDEX FROM articles;
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM comments;
-- SHOW INDEX FROM reactions;
-- SHOW INDEX FROM notifications;
-- Check query performance
-- EXPLAIN SELECT * FROM articles WHERE status = 'approved' ORDER BY created_at DESC;
-- EXPLAIN SELECT * FROM users WHERE institute LIKE '%IC%' AND status = 'active';
-- EXPLAIN SELECT * FROM notifications WHERE user_id = 1 AND is_read = 0;
-- Monitor slow queries
-- SHOW VARIABLES LIKE 'slow_query_log';
-- SET GLOBAL slow_query_log = 'ON';
-- SET GLOBAL long_query_time = 2;