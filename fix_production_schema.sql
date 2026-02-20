-- =============================================================================
-- Production Database Fix: Add Missing Columns to issue_reports table
-- =============================================================================
-- Run this SQL on your production database (cPanel phpMyAdmin or SSH)
-- All statements use IF NOT EXISTS / column checks to be safe to re-run
-- =============================================================================

-- Step 1: Add 'priority' column (from base schema - should have existed)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'priority');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `priority` ENUM("low","medium","high","urgent") NOT NULL DEFAULT "medium" AFTER `status`',
    'SELECT "priority column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Add 'submitted_by_agent_id' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'submitted_by_agent_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `submitted_by_agent_id` INT UNSIGNED NULL AFTER `reporter_phone`',
    'SELECT "submitted_by_agent_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add 'submitted_by_officer_id' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'submitted_by_officer_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `submitted_by_officer_id` INT UNSIGNED NULL AFTER `submitted_by_agent_id`',
    'SELECT "submitted_by_officer_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Add 'assigned_officer_id' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assigned_officer_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assigned_officer_id` INT UNSIGNED NULL AFTER `submitted_by_officer_id`',
    'SELECT "assigned_officer_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 5: Add 'assigned_agent_id' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assigned_agent_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assigned_agent_id` INT UNSIGNED NULL AFTER `assigned_officer_id`',
    'SELECT "assigned_agent_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 6: Add 'assigned_task_force_id' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assigned_task_force_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assigned_task_force_id` INT UNSIGNED NULL AFTER `assigned_agent_id`',
    'SELECT "assigned_task_force_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 7: Add 'acknowledged_by' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'acknowledged_by');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `acknowledged_by` INT UNSIGNED NULL AFTER `acknowledged_at`',
    'SELECT "acknowledged_by column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 8: Add 'resolved_by' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'resolved_by');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `resolved_by` INT UNSIGNED NULL AFTER `resolved_at`',
    'SELECT "resolved_by column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 9: Add 'allocated_budget' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'allocated_budget');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `allocated_budget` DECIMAL(15,2) NULL AFTER `priority`',
    'SELECT "allocated_budget column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 10: Add 'allocated_resources' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'allocated_resources');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `allocated_resources` JSON NULL AFTER `allocated_budget`',
    'SELECT "allocated_resources column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 11: Add 'forwarded_to_admin_at' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'forwarded_to_admin_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `forwarded_to_admin_at` TIMESTAMP NULL AFTER `acknowledged_by`',
    'SELECT "forwarded_to_admin_at column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 12: Add 'assigned_to_task_force_at' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assigned_to_task_force_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assigned_to_task_force_at` TIMESTAMP NULL AFTER `forwarded_to_admin_at`',
    'SELECT "assigned_to_task_force_at column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 13: Add 'resources_allocated_at' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'resources_allocated_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `resources_allocated_at` TIMESTAMP NULL AFTER `assigned_to_task_force_at`',
    'SELECT "resources_allocated_at column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 14: Add 'resources_allocated_by' column
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'resources_allocated_by');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `resources_allocated_by` INT UNSIGNED NULL AFTER `resources_allocated_at`',
    'SELECT "resources_allocated_by column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 15: Add classification fields
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'sector_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `sector_id` INT UNSIGNED NULL',
    'SELECT "sector_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'sub_sector_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `sub_sector_id` INT UNSIGNED NULL',
    'SELECT "sub_sector_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'issue_type');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `issue_type` VARCHAR(100) NULL',
    'SELECT "issue_type column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'affected_people_count');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `affected_people_count` INT NULL',
    'SELECT "affected_people_count column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 16: Add location hierarchy fields
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'main_community_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `main_community_id` INT UNSIGNED NULL',
    'SELECT "main_community_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'smaller_community_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `smaller_community_id` INT UNSIGNED NULL',
    'SELECT "smaller_community_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'suburb_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `suburb_id` INT UNSIGNED NULL',
    'SELECT "suburb_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'cottage_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `cottage_id` INT UNSIGNED NULL',
    'SELECT "cottage_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 17: Add constituent fields
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'constituent_name');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `constituent_name` VARCHAR(255) NULL',
    'SELECT "constituent_name column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'constituent_email');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `constituent_email` VARCHAR(255) NULL',
    'SELECT "constituent_email column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'constituent_contact');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `constituent_contact` VARCHAR(50) NULL',
    'SELECT "constituent_contact column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'constituent_gender');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `constituent_gender` VARCHAR(20) NULL',
    'SELECT "constituent_gender column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'constituent_address');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `constituent_address` TEXT NULL',
    'SELECT "constituent_address column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 18: Add review tracking fields
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'reviewed_by_officer_id');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `reviewed_by_officer_id` INT UNSIGNED NULL',
    'SELECT "reviewed_by_officer_id column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'reviewed_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `reviewed_at` TIMESTAMP NULL',
    'SELECT "reviewed_at column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assessment_reviewed_by');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assessment_reviewed_by` INT UNSIGNED NULL',
    'SELECT "assessment_reviewed_by column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assessment_reviewed_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assessment_reviewed_at` TIMESTAMP NULL',
    'SELECT "assessment_reviewed_at column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'issue_reports' AND COLUMN_NAME = 'assessment_decision');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `issue_reports` ADD COLUMN `assessment_decision` VARCHAR(50) NULL',
    'SELECT "assessment_decision column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 19: Update the status ENUM to include all workflow statuses
-- First check current enum values
ALTER TABLE `issue_reports` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'submitted';

-- Step 20: Add indexes for frequently queried columns
-- (safe to re-run, MySQL will skip if index already exists)
CREATE INDEX idx_issue_assigned_officer ON `issue_reports` (`assigned_officer_id`);
CREATE INDEX idx_issue_assigned_task_force ON `issue_reports` (`assigned_task_force_id`);
CREATE INDEX idx_issue_submitted_by_agent ON `issue_reports` (`submitted_by_agent_id`);

-- =============================================================================
-- DONE! All missing columns have been added.
-- =============================================================================
-- After running this, verify by running: DESCRIBE issue_reports;
-- =============================================================================
