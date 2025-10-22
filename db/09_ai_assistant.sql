-- ============================================================================
-- AI QUERY ASSISTANT TABLES
-- ============================================================================
-- File: 09_ai_assistant.sql
-- Purpose: Tables for AI-powered natural language query assistant
-- Features: Query history, favorites, usage tracking
-- ============================================================================

USE `smart_stay`;

-- ============================================================================
-- Table: ai_query_history
-- Description: Stores all AI-generated queries for history and analytics
-- ============================================================================
CREATE TABLE IF NOT EXISTS `ai_query_history` (
  `history_id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `natural_query` TEXT NOT NULL COMMENT 'User natural language input',
  `generated_sql` TEXT NOT NULL COMMENT 'AI-generated SQL query',
  `explanation` TEXT COMMENT 'AI explanation of the query',
  `execution_status` ENUM('Success', 'Failed', 'Not Executed') DEFAULT 'Not Executed',
  `execution_time_ms` INT COMMENT 'Query execution time in milliseconds',
  `rows_returned` INT COMMENT 'Number of rows returned',
  `error_message` TEXT COMMENT 'Error message if execution failed',
  `tokens_used` INT COMMENT 'OpenAI tokens consumed',
  `is_favorite` TINYINT DEFAULT 0 COMMENT '1=favorited, 0=normal',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_admin` (`admin_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_favorite` (`is_favorite`),
  CONSTRAINT `fk_history_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores AI query generation history';

-- ============================================================================
-- Table: ai_query_favorites
-- Description: User-saved favorite queries with custom names
-- ============================================================================
CREATE TABLE IF NOT EXISTS `ai_query_favorites` (
  `favorite_id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `favorite_name` VARCHAR(100) NOT NULL COMMENT 'User-defined name for the query',
  `natural_query` TEXT COMMENT 'Original natural language query',
  `sql_query` TEXT NOT NULL COMMENT 'The SQL query',
  `description` TEXT COMMENT 'User notes about the query',
  `category` VARCHAR(50) DEFAULT 'General' COMMENT 'User-defined category',
  `use_count` INT DEFAULT 0 COMMENT 'Number of times executed',
  `last_used` TIMESTAMP NULL COMMENT 'Last execution timestamp',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_admin` (`admin_id`),
  KEY `idx_category` (`category`),
  UNIQUE KEY `unique_admin_name` (`admin_id`, `favorite_name`),
  CONSTRAINT `fk_favorite_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User-saved favorite queries';

-- ============================================================================
-- Table: ai_usage_stats
-- Description: Daily usage statistics for monitoring and limits
-- ============================================================================
CREATE TABLE IF NOT EXISTS `ai_usage_stats` (
  `stat_id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `usage_date` DATE NOT NULL,
  `queries_generated` INT DEFAULT 0 COMMENT 'Number of queries generated',
  `queries_executed` INT DEFAULT 0 COMMENT 'Number of queries executed',
  `total_tokens` INT DEFAULT 0 COMMENT 'Total OpenAI tokens used',
  `successful_queries` INT DEFAULT 0 COMMENT 'Successful executions',
  `failed_queries` INT DEFAULT 0 COMMENT 'Failed executions',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_admin_date` (`admin_id`, `usage_date`),
  KEY `idx_date` (`usage_date`),
  CONSTRAINT `fk_stats_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Daily AI usage statistics per admin';

-- ============================================================================
-- Stored Procedure: GetQueryHistory
-- Description: Retrieve query history for an admin with filters
-- ============================================================================
DELIMITER $$
CREATE PROCEDURE `GetQueryHistory`(
    IN p_admin_id INT,
    IN p_favorites_only TINYINT,
    IN p_limit INT
)
BEGIN
    IF p_favorites_only = 1 THEN
        SELECT 
            history_id,
            natural_query,
            generated_sql,
            explanation,
            execution_status,
            rows_returned,
            created_at
        FROM ai_query_history
        WHERE admin_id = p_admin_id AND is_favorite = 1
        ORDER BY created_at DESC
        LIMIT p_limit;
    ELSE
        SELECT 
            history_id,
            natural_query,
            generated_sql,
            explanation,
            execution_status,
            rows_returned,
            is_favorite,
            created_at
        FROM ai_query_history
        WHERE admin_id = p_admin_id
        ORDER BY created_at DESC
        LIMIT p_limit;
    END IF;
END$$

-- ============================================================================
-- Stored Procedure: UpdateUsageStats
-- Description: Update daily usage statistics
-- ============================================================================
CREATE PROCEDURE `UpdateUsageStats`(
    IN p_admin_id INT,
    IN p_tokens INT,
    IN p_executed TINYINT,
    IN p_success TINYINT
)
BEGIN
    INSERT INTO ai_usage_stats (admin_id, usage_date, queries_generated, queries_executed, total_tokens, successful_queries, failed_queries)
    VALUES (
        p_admin_id, 
        CURDATE(), 
        1, 
        IF(p_executed = 1, 1, 0),
        p_tokens,
        IF(p_success = 1, 1, 0),
        IF(p_success = 0 AND p_executed = 1, 1, 0)
    )
    ON DUPLICATE KEY UPDATE
        queries_generated = queries_generated + 1,
        queries_executed = queries_executed + IF(p_executed = 1, 1, 0),
        total_tokens = total_tokens + p_tokens,
        successful_queries = successful_queries + IF(p_success = 1, 1, 0),
        failed_queries = failed_queries + IF(p_success = 0 AND p_executed = 1, 1, 0),
        updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- ============================================================================
-- Sample Data (Optional)
-- ============================================================================
-- This will be populated automatically as admins use the AI assistant

-- ============================================================================
-- Indexes for Performance
-- ============================================================================
-- Already included in table definitions above

-- ============================================================================
-- Usage Examples
-- ============================================================================

/*
-- Get recent history
CALL GetQueryHistory(1, 0, 20);

-- Get only favorites
CALL GetQueryHistory(1, 1, 10);

-- Update usage stats
CALL UpdateUsageStats(1, 150, 1, 1);

-- View usage stats
SELECT * FROM ai_usage_stats WHERE admin_id = 1 ORDER BY usage_date DESC LIMIT 7;

-- Popular favorites
SELECT 
    favorite_name,
    category,
    use_count,
    last_used
FROM ai_query_favorites
WHERE admin_id = 1
ORDER BY use_count DESC
LIMIT 10;

-- Mark query as favorite
UPDATE ai_query_history 
SET is_favorite = 1 
WHERE history_id = 123 AND admin_id = 1;

-- Save to favorites
INSERT INTO ai_query_favorites (admin_id, favorite_name, sql_query, description, category)
VALUES (1, 'Monthly Revenue Report', 'SELECT...', 'Total revenue for current month', 'Revenue');
*/

-- ============================================================================
-- End of AI Assistant Schema
-- ============================================================================
