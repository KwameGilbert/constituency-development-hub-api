-- Rollback Script: Undo All Schema Migrations
-- Created: 2026-01-17
-- WARNING: This will remove all changes made by migrations 001-003
-- Make sure you have a backup before running this!

-- ============================================================
-- Rollback Migration 003: Sectors Tables
-- ============================================================

-- Remove foreign keys from issue_reports
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_sector`;
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_sub_sector`;

-- Drop tables
DROP TABLE IF EXISTS `sub_sectors`;
DROP TABLE IF EXISTS `sectors`;

-- ============================================================
-- Rollback Migration 002: Officers Table
-- ============================================================

-- Remove foreign key from issue_reports
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_reviewed_by_officer`;

-- Drop tables
DROP PROCEDURE IF EXISTS `generate_officer_code`;
DROP TABLE IF EXISTS `officer_community_assignments`;
DROP TABLE IF EXISTS `officers`;

-- ============================================================
-- Rollback Migration 001: Issue Reports Extensions
-- ============================================================

-- Drop indexes
DROP INDEX IF EXISTS `idx_issue_location_hierarchy` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_cottage` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_suburb` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_smaller_community` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_main_community` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_type` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_sub_sector` ON `issue_reports`;
DROP INDEX IF EXISTS `idx_issue_sector` ON `issue_reports`;

-- Drop foreign keys
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_cottage`;
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_suburb`;
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_smaller_community`;
ALTER TABLE `issue_reports` DROP FOREIGN KEY IF EXISTS `fk_issue_main_community`;

-- Remove review tracking columns
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `assessment_decision`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `assessment_reviewed_at`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `assessment_reviewed_by`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `reviewed_at`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `reviewed_by_officer_id`;

-- Remove constituent information columns
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `constituent_address`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `constituent_gender`;

-- Rename constituent columns back to reporter
ALTER TABLE `issue_reports` 
CHANGE COLUMN `constituent_contact` `reporter_phone` VARCHAR(50) DEFAULT NULL;

ALTER TABLE `issue_reports` 
CHANGE COLUMN `constituent_email` `reporter_email` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `issue_reports` 
CHANGE COLUMN `constituent_name` `reporter_name` VARCHAR(255) DEFAULT NULL;

-- Remove location hierarchy columns
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `cottage_id`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `suburb_id`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `smaller_community_id`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `main_community_id`;

-- Remove classification columns
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `affected_people_count`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `issue_type`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `sub_sector_id`;
ALTER TABLE `issue_reports` DROP COLUMN IF EXISTS `sector_id`;

-- Revert status ENUM to original values
ALTER TABLE `issue_reports` 
MODIFY COLUMN `status` ENUM(
  'submitted',
  'acknowledged',
  'in_progress',
  'resolved',
  'closed',
  'rejected',
  'assigned_to_task_force',
  'assessment_in_progress',
  'assessment_submitted',
  'resources_allocated',
  'resolution_in_progress'
) NOT NULL DEFAULT 'submitted';

-- Remove "Other" location entries
DELETE FROM `locations` WHERE `name` = 'Other' AND `type` IN ('community', 'smaller_community');

-- ============================================================
-- END OF ROLLBACK
-- ============================================================

COMMIT;

-- Verify rollback
SELECT 'Rollback completed. Verify table structure:' AS message;
SHOW COLUMNS FROM `issue_reports`;
