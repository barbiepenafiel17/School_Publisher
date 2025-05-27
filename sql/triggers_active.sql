-- ========================================
-- ACTIVE SQL TRIGGERS FOR SCHOOL PUBLISHER PROJECT
-- Automated functionality triggers - WORKING VERSION
-- ========================================

USE dbclm_college;

-- Set delimiter for trigger definitions
DELIMITER //

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS after_article_insert//
DROP TRIGGER IF EXISTS after_article_status_update//
DROP TRIGGER IF EXISTS after_user_registration//
DROP TRIGGER IF EXISTS after_comment_insert//
DROP TRIGGER IF EXISTS after_reaction_insert//
DROP TRIGGER IF EXISTS article_audit_trigger//

-- ========================================
-- Trigger: After Article Insert
-- Creates notification for admin when new article is submitted
-- ========================================
CREATE TRIGGER after_article_insert
    AFTER INSERT ON articles
    FOR EACH ROW
BEGIN
    -- Create admin notification for new article submission
    INSERT INTO admin_notifications (
        type, 
        reference_id, 
        message, 
        created_at
    ) VALUES (
        'new_article',
        NEW.id,
        CONCAT('New article submitted: "', NEW.title, '" by user ID ', NEW.user_id),
        NOW()
    );
    
    -- Create notification for the author
    INSERT INTO notifications (
        user_id,
        message,
        is_read,
        created_at
    ) VALUES (
        NEW.user_id,
        CONCAT('Your article "', NEW.title, '" has been submitted for review. You will be notified once it is reviewed.'),
        0,
        NOW()
    );
END//

-- ========================================
-- Trigger: After Article Status Update
-- Creates notifications when article status changes
-- ========================================
CREATE TRIGGER after_article_status_update
    AFTER UPDATE ON articles
    FOR EACH ROW
BEGIN
    -- Only trigger if status actually changed
    IF OLD.status != NEW.status THEN
        -- Notify author of status change
        IF NEW.status = 'approved' THEN
            INSERT INTO notifications (
                user_id,
                message,
                is_read,
                created_at
            ) VALUES (
                NEW.user_id,
                CONCAT('🎉 Great news! Your article "', NEW.title, '" has been approved and is now published!'),
                0,
                NOW()
            );
        ELSEIF NEW.status = 'rejected' THEN
            INSERT INTO notifications (
                user_id,
                message,
                is_read,
                created_at
            ) VALUES (
                NEW.user_id,
                CONCAT('Your article "', NEW.title, '" was not approved. Please check the feedback and try again.'),
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
        ) VALUES (
            'status_change',
            NEW.id,
            CONCAT('Article "', NEW.title, '" status changed from ', OLD.status, ' to ', NEW.status),
            NOW()
        );
    END IF;
END//

-- ========================================
-- Trigger: After User Registration
-- Creates welcome notification and admin alert
-- ========================================
CREATE TRIGGER after_user_registration
    AFTER INSERT ON users
    FOR EACH ROW
BEGIN
    -- Create welcome notification for new user
    INSERT INTO notifications (
        user_id,
        message,
        is_read,
        created_at
    ) VALUES (
        NEW.id,
        CONCAT('Welcome to DBCLM College Publisher, ', NEW.full_name, '! 🎓 Start sharing your knowledge with the community.'),
        0,
        NOW()
    );
    
    -- Create admin notification for new user registration
    INSERT INTO admin_notifications (
        type,
        reference_id,
        message,
        created_at
    ) VALUES (
        'new_user',
        NEW.id,
        CONCAT('New user registered: ', NEW.full_name, ' (', NEW.email, ') - ', NEW.role, ' from ', NEW.institute),
        NOW()
    );
END//

-- ========================================
-- Trigger: After Comment Insert
-- Creates notification for article author when someone comments
-- ========================================
CREATE TRIGGER after_comment_insert
    AFTER INSERT ON comments
    FOR EACH ROW
BEGIN
    DECLARE v_article_author_id INT;
    DECLARE v_article_title VARCHAR(255);
    DECLARE v_commenter_name VARCHAR(255);
    
    -- Get article details
    SELECT user_id, title INTO v_article_author_id, v_article_title
    FROM articles 
    WHERE id = NEW.article_id;
    
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
        ) VALUES (
            v_article_author_id,
            CONCAT('💬 ', v_commenter_name, ' commented on your article "', v_article_title, '"'),
            0,
            NOW()
        );
    END IF;
END//

-- ========================================
-- Trigger: After Reaction Insert
-- Creates notification for article author when someone reacts
-- ========================================
CREATE TRIGGER after_reaction_insert
    AFTER INSERT ON reactions
    FOR EACH ROW
BEGIN
    DECLARE v_article_author_id INT;
    DECLARE v_article_title VARCHAR(255);
    DECLARE v_reactor_name VARCHAR(255);
    
    -- Only process 'like' reactions
    IF NEW.reaction_type = 'like' THEN
        -- Get article details
        SELECT user_id, title INTO v_article_author_id, v_article_title
        FROM articles 
        WHERE id = NEW.article_id;
        
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
            ) VALUES (
                v_article_author_id,
                CONCAT('👍 ', v_reactor_name, ' liked your article "', v_article_title, '"'),
                0,
                NOW()
            );
        END IF;
    END IF;
END//

-- ========================================
-- Trigger: Article Audit Trigger
-- Logs all article changes for audit purposes
-- ========================================
CREATE TRIGGER article_audit_trigger
    AFTER UPDATE ON articles
    FOR EACH ROW
BEGIN
    -- Log the change
    INSERT INTO audit_log (
        table_name,
        operation,
        record_id,
        old_values,
        new_values,
        created_at
    ) VALUES (
        'articles',
        'UPDATE',
        NEW.id,
        CONCAT('status:', OLD.status, ',title:', OLD.title),
        CONCAT('status:', NEW.status, ',title:', NEW.title),
        NOW()
    );
END//

-- Reset delimiter
DELIMITER ;

-- ========================================
-- Create Helper Tables for Triggers
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

-- ========================================
-- VERIFICATION - Show created triggers
-- ========================================

SELECT 'TRIGGERS CREATED SUCCESSFULLY!' as Status;

-- Show all triggers
SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING
FROM information_schema.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'dbclm_college'
ORDER BY EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION;

-- Check helper tables
SELECT 'Helper tables created:' as Info;
SHOW TABLES LIKE '%audit%';
SHOW TABLES LIKE '%admin_notifications%';

SELECT '✅ All triggers have been created and are active!' as Result;
