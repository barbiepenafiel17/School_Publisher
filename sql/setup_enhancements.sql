-- ========================================
-- SETUP SCRIPT FOR SQL ENHANCEMENTS
-- School Publisher Project
-- ========================================
-- This script sets up Views, Stored Procedures, and Indexes
-- Execute this file in your MySQL/phpMyAdmin to implement all enhancements
-- ========================================
-- 1. SETUP INDEXES FIRST
-- ========================================
SOURCE sql / indexes.sql;
-- ========================================
-- 2. SETUP VIEWS
-- ========================================
SOURCE sql / views.sql;
-- ========================================
-- 3. SETUP STORED PROCEDURES
-- ========================================
SOURCE sql / stored_procedures.sql;
-- ========================================
-- VERIFICATION QUERIES
-- ========================================
-- Check if views were created successfully
SHOW FULL TABLES IN dbclm_college
WHERE Table_type = 'VIEW';
-- Check if stored procedures were created successfully
SHOW PROCEDURE STATUS
WHERE Db = 'dbclm_college';
-- Check index status for main tables
SHOW INDEX
FROM articles;
SHOW INDEX
FROM users;
SHOW INDEX
FROM comments;
SHOW INDEX
FROM reactions;
SHOW INDEX
FROM notifications;
-- ========================================
-- TEST QUERIES
-- ========================================
-- Test article dashboard view
SELECT COUNT(*) as total_articles
FROM article_dashboard_view;
-- Test user stats view
SELECT COUNT(*) as total_users
FROM user_stats_view;
-- Test admin dashboard stats
SELECT *
FROM admin_dashboard_stats;
-- Test notification summary
SELECT COUNT(*) as users_with_notifications
FROM notification_summary_view;
-- ========================================
-- SUCCESS MESSAGE
-- ========================================
SELECT 'SQL enhancements setup completed successfully!' as STATUS;