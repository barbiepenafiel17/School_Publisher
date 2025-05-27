-- ========================================
-- SQL VIEWS FOR SCHOOL PUBLISHER PROJECT
-- ========================================
-- Drop existing views if they exist
DROP VIEW IF EXISTS article_dashboard_view;
DROP VIEW IF EXISTS user_stats_view;
DROP VIEW IF EXISTS notification_summary_view;
DROP VIEW IF EXISTS admin_dashboard_stats;
DROP VIEW IF EXISTS article_engagement_view;
DROP VIEW IF EXISTS saved_articles_view;
-- ========================================
-- Article Dashboard View
-- Comprehensive article information with author details and engagement stats
-- ========================================
CREATE VIEW article_dashboard_view AS
SELECT a.id,
  a.title,
  a.abstract,
  a.content,
  a.status,
  a.created_at,
  a.featured_image,
  a.allow_comments,
  a.feedback,
  u.full_name AS author_name,
  u.email AS author_email,
  u.institute AS author_institute,
  u.profile_picture,
  COALESCE(r.like_count, 0) AS like_count,
  COALESCE(c.comment_count, 0) AS comment_count,
  COALESCE(s.save_count, 0) AS save_count
FROM articles a
  JOIN users u ON a.user_id = u.id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS like_count
    FROM reactions
    WHERE reaction_type = 'like'
    GROUP BY article_id
  ) r ON a.id = r.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS comment_count
    FROM comments
    GROUP BY article_id
  ) c ON a.id = c.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS save_count
    FROM saved_articles
    GROUP BY article_id
  ) s ON a.id = s.article_id;
-- ========================================
-- User Statistics View
-- Comprehensive user information with activity stats
-- ========================================
CREATE VIEW user_stats_view AS
SELECT u.id,
  u.full_name,
  u.email,
  u.role,
  u.institute,
  u.status,
  u.created_at,
  u.profile_picture,
  COALESCE(a.total_articles, 0) AS total_articles,
  COALESCE(a.approved_articles, 0) AS approved_articles,
  COALESCE(a.pending_articles, 0) AS pending_articles,
  COALESCE(a.rejected_articles, 0) AS rejected_articles,
  COALESCE(c.total_comments, 0) AS total_comments,
  COALESCE(sa.saved_articles_count, 0) AS saved_articles_count,
  a.last_article_date
FROM users u
  LEFT JOIN (
    SELECT user_id,
      COUNT(*) AS total_articles,
      SUM(
        CASE
          WHEN status = 'approved' THEN 1
          ELSE 0
        END
      ) AS approved_articles,
      SUM(
        CASE
          WHEN status = 'pending' THEN 1
          ELSE 0
        END
      ) AS pending_articles,
      SUM(
        CASE
          WHEN status = 'rejected' THEN 1
          ELSE 0
        END
      ) AS rejected_articles,
      MAX(created_at) AS last_article_date
    FROM articles
    GROUP BY user_id
  ) a ON u.id = a.user_id
  LEFT JOIN (
    SELECT user_id,
      COUNT(*) AS total_comments
    FROM comments
    GROUP BY user_id
  ) c ON u.id = c.user_id
  LEFT JOIN (
    SELECT user_id,
      COUNT(*) AS saved_articles_count
    FROM saved_articles
    GROUP BY user_id
  ) sa ON u.id = sa.user_id;
-- ========================================
-- Notification Summary View
-- User notification statistics
-- ========================================
CREATE VIEW notification_summary_view AS
SELECT user_id,
  COUNT(*) AS total_notifications,
  SUM(
    CASE
      WHEN is_read = 0 THEN 1
      ELSE 0
    END
  ) AS unread_count,
  MAX(created_at) AS latest_notification
FROM notifications
GROUP BY user_id;
-- ========================================
-- Admin Dashboard Statistics View
-- Quick overview stats for admin dashboard
-- ========================================
CREATE VIEW admin_dashboard_stats AS
SELECT (
    SELECT COUNT(*)
    FROM articles
  ) AS total_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE status = 'pending'
  ) AS pending_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE status = 'approved'
  ) AS approved_articles,
  (
    SELECT COUNT(*)
    FROM articles
    WHERE status = 'rejected'
  ) AS rejected_articles,
  (
    SELECT COUNT(*)
    FROM users
  ) AS total_users,
  (
    SELECT COUNT(*)
    FROM users
    WHERE role = 'student'
  ) AS total_students,
  (
    SELECT COUNT(*)
    FROM users
    WHERE role = 'teacher'
  ) AS total_teachers,
  (
    SELECT COUNT(*)
    FROM users
    WHERE role = 'admin'
  ) AS total_admins,
  (
    SELECT COUNT(*)
    FROM admin_notifications
  ) AS total_admin_notifications,
  (
    SELECT COUNT(*)
    FROM comments
  ) AS total_comments,
  (
    SELECT COUNT(*)
    FROM reactions
    WHERE reaction_type = 'like'
  ) AS total_likes,
  (
    SELECT COUNT(*)
    FROM announcements
    WHERE status = 'published'
  ) AS active_announcements;
-- ========================================
-- Article Engagement View
-- Detailed engagement metrics for articles
-- ========================================
CREATE VIEW article_engagement_view AS
SELECT a.id AS article_id,
  a.title,
  a.status,
  a.created_at,
  u.full_name AS author_name,
  u.institute AS author_institute,
  COALESCE(likes.like_count, 0) AS likes,
  COALESCE(dislikes.dislike_count, 0) AS dislikes,
  COALESCE(comments.comment_count, 0) AS comments,
  COALESCE(saves.save_count, 0) AS saves,
  COALESCE(reports.report_count, 0) AS reports,
  COALESCE(hides.hide_count, 0) AS hides,
  -- Engagement score calculation
  (
    COALESCE(likes.like_count, 0) * 2 + COALESCE(comments.comment_count, 0) * 3 + COALESCE(saves.save_count, 0) * 5 - COALESCE(dislikes.dislike_count, 0) * 1 - COALESCE(reports.report_count, 0) * 10
  ) AS engagement_score
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
      COUNT(*) AS dislike_count
    FROM reactions
    WHERE reaction_type = 'dislike'
    GROUP BY article_id
  ) dislikes ON a.id = dislikes.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS comment_count
    FROM comments
    GROUP BY article_id
  ) comments ON a.id = comments.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS save_count
    FROM saved_articles
    GROUP BY article_id
  ) saves ON a.id = saves.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS report_count
    FROM article_reports
    GROUP BY article_id
  ) reports ON a.id = reports.article_id
  LEFT JOIN (
    SELECT article_id,
      COUNT(*) AS hide_count
    FROM hidden_articles
    GROUP BY article_id
  ) hides ON a.id = hides.article_id;
-- ========================================
-- Saved Articles View with Details
-- Enhanced saved articles with full article info
-- ========================================
CREATE VIEW saved_articles_view AS
SELECT sa.user_id,
  sa.article_id,
  sa.saved_at,
  a.title,
  a.abstract,
  a.content,
  a.created_at AS article_created_at,
  a.featured_image,
  u.full_name AS author_name,
  u.profile_picture AS author_picture,
  u.institute AS author_institute,
  COALESCE(likes.like_count, 0) AS likes,
  COALESCE(comments.comment_count, 0) AS comments
FROM saved_articles sa
  JOIN articles a ON sa.article_id = a.id
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
  ) comments ON a.id = comments.article_id;