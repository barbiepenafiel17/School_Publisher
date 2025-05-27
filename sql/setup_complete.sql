-- ========================================
-- COMPLETE DATABASE SETUP SCRIPT
-- School Publisher Project
-- ========================================
-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS dbclm_college CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dbclm_college;
-- ========================================
-- Execute SQL Files in Correct Order
-- ========================================
-- 1. First run the main database schema (if you have it)
-- SOURCE your_main_schema.sql;
-- 2. Create indexes for performance
SOURCE sql / indexes.sql;
-- 3. Create views for complex queries
SOURCE sql / views.sql;
-- 4. Create stored procedures
SOURCE sql / stored_procedures.sql;
-- 5. Create triggers for automated actions
SOURCE sql / triggers.sql;
-- ========================================
-- Verify Installation
-- ========================================
-- Check if all views were created successfully
SELECT 'VIEWS CHECK' as check_type;
SELECT TABLE_NAME as view_name
FROM INFORMATION_SCHEMA.VIEWS
WHERE TABLE_SCHEMA = 'dbclm_college';
-- Check if all stored procedures were created successfully
SELECT 'PROCEDURES CHECK' as check_type;
SELECT ROUTINE_NAME as procedure_name
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'dbclm_college'
  AND ROUTINE_TYPE = 'PROCEDURE';
-- Check if all triggers were created successfully
SELECT 'TRIGGERS CHECK' as check_type;
SELECT TRIGGER_NAME as trigger_name,
  EVENT_MANIPULATION,
  EVENT_OBJECT_TABLE
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = 'dbclm_college';
-- Check if all indexes were created successfully
SELECT 'INDEXES CHECK' as check_type;
SELECT TABLE_NAME,
  INDEX_NAME,
  COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'dbclm_college'
  AND INDEX_NAME != 'PRIMARY'
ORDER BY TABLE_NAME,
  INDEX_NAME;
-- ========================================
-- Test Basic Functionality
-- ========================================
-- Test stored procedures (these will fail if tables don't exist, but syntax will be validated)
-- CALL RegisterUser('Test User', 'test@example.com', 'hashed_password', 'student', 'Test Institute');
-- CALL GetUserDashboardStats(1);
SELECT 'Database setup completed successfully!' as status;