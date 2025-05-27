-- ========================================
-- COMPLETE DATABASE SETUP FOR SCHOOL PUBLISHER PROJECT
-- This script sets up all SQL features in the correct order
-- ========================================
-- Set SQL mode and configurations
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
-- ========================================
-- 1. CREATE DATABASE AND USE IT
-- ========================================
-- CREATE DATABASE IF NOT EXISTS dbclm_college;
USE dbclm_college;
-- ========================================
-- 2. VIEWS - Create all database views
-- ========================================
SOURCE views.sql;
-- ========================================
-- 3. INDEXES - Create performance indexes
-- ========================================
SOURCE indexes_fixed.sql;
-- ========================================
-- 4. STORED PROCEDURES - Create all procedures
-- ========================================
SOURCE stored_procedures_fixed.sql;
-- ========================================
-- 5. TRIGGERS - Create all triggers
-- ========================================
SOURCE triggers_fixed.sql;
-- ========================================
-- 6. VERIFICATION QUERIES
-- Test that all features are working
-- ========================================
-- Show all views
SELECT 'VIEWS CREATED:' as Status;
SELECT TABLE_NAME as View_Name
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = 'dbclm_college';
-- Show all stored procedures
SELECT 'STORED PROCEDURES CREATED:' as Status;
SELECT ROUTINE_NAME as Procedure_Name,
  ROUTINE_TYPE
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'dbclm_college'
  AND ROUTINE_TYPE = 'PROCEDURE';
-- Show all triggers
SELECT 'TRIGGERS CREATED:' as Status;
SELECT TRIGGER_NAME,
  EVENT_MANIPULATION,
  EVENT_OBJECT_TABLE
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'dbclm_college';
-- Show indexes on main tables
SELECT 'INDEXES ON ARTICLES TABLE:' as Status;
SHOW INDEX
FROM articles;
SELECT 'INDEXES ON USERS TABLE:' as Status;
SHOW INDEX
FROM users;
-- Test a view
SELECT 'TESTING ARTICLE_DASHBOARD_VIEW:' as Status;
SELECT COUNT(*) as Total_Articles
FROM article_dashboard_view;
-- Test a stored procedure (if articles exist)
SELECT 'TESTING STORED PROCEDURE:' as Status;
CALL GetFilteredArticles('', 'approved', '', 10, 0);
-- Show helper tables created by triggers
SELECT 'HELPER TABLES FOR TRIGGERS:' as Status;
SHOW TABLES LIKE '%audit%';
SHOW TABLES LIKE '%notification%';
COMMIT;
-- ========================================
-- SUMMARY REPORT
-- ========================================
SELECT '========================================' as '';
SELECT 'DATABASE SETUP COMPLETE!' as Status;
SELECT '========================================' as '';
SELECT 'SQL FEATURES IMPLEMENTED:' as '';
SELECT '✅ Views - Article dashboard, user stats, etc.' as Feature;
SELECT '✅ JOINs - Used extensively in views and queries' as Feature;
SELECT '✅ Indexes - Performance indexes on key columns' as Feature;
SELECT '✅ Stored Procedures - Article management, search, etc.' as Feature;
SELECT '✅ Triggers - Auto notifications, audit logging' as Feature;
SELECT '========================================' as '';