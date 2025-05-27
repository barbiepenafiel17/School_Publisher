USE dbclm_college;
DROP PROCEDURE IF EXISTS ApproveArticle;
DELIMITER $$ CREATE PROCEDURE ApproveArticle(
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
-- Check if article exists and is pending (using UPPERCASE)
SELECT COUNT(*) INTO article_exists
FROM articles
WHERE id = p_article_id
  AND status = 'PENDING';
IF article_exists = 0 THEN ROLLBACK;
SELECT 'Article not found or already processed' AS result,
  'error' AS status;
ELSE -- Get article details
SELECT user_id,
  title INTO v_user_id,
  v_title
FROM articles
WHERE id = p_article_id;
-- Update article status (using UPPERCASE)
UPDATE articles
SET status = 'APPROVED'
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
END $$ DELIMITER;