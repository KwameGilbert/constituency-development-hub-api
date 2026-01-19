-- Migration: Add Missing Issue Status States and Classification Fields
-- Created: 2026-01-17
-- Description: Extends issue_reports table with missing workflow states and classification

-- ============================================================
-- PART 1: Update Status ENUM with Missing States
-- ============================================================

-- Add missing status states to issue_reports
ALTER TABLE `issue_reports` 
MODIFY COLUMN `status` ENUM(
  'submitted',
  'pending',
  'reviewed',              -- NEW: Officer marked as reviewed
  'acknowledged',
  'pending_assessment',    -- NEW: Assigned to Task Force, awaiting assessment
  'under_assessment',      -- NEW: Assessment in progress
  'assessed',              -- NEW: Assessment completed, awaiting admin review
  'approved',              -- NEW: Admin approved assessment for execution
  'in_progress',
  'resolved',
  'closed',
  'rejected',
  'needs_revision',        -- NEW: Assessment/resolution needs more work
  'on_hold',               -- NEW: Temporarily paused
  'assigned_to_task_force',-- EXISTING but ensuring it's included
  'assessment_in_progress', -- EXISTING alias for under_assessment
  'assessment_submitted',   -- EXISTING
  'resources_allocated',    -- EXISTING
  'resolution_in_progress'  -- EXISTING
) NOT NULL DEFAULT 'submitted';

-- ============================================================
-- PART 2: Add Classification Fields
-- ============================================================

-- Add sector_id (will link to sectors table)
ALTER TABLE `issue_reports` 
ADD COLUMN `sector_id` INT(11) UNSIGNED NULL 
COMMENT 'Sector classification' AFTER `category`;

-- Add sub_sector_id (will link to sub_sectors table if it exists)
ALTER TABLE `issue_reports` 
ADD COLUMN `sub_sector_id` INT(11) UNSIGNED NULL 
COMMENT 'Sub-sector classification' AFTER `sector_id`;

-- Add issue type (community-based vs individual-based)
ALTER TABLE `issue_reports` 
ADD COLUMN `issue_type` ENUM('community_based', 'individual_based') 
DEFAULT 'community_based' 
COMMENT 'Type of issue: affects community or individual' 
AFTER `sub_sector_id`;

-- Add affected people count
ALTER TABLE `issue_reports` 
ADD COLUMN `affected_people_count` INT(11) UNSIGNED NULL 
COMMENT 'Approximate number of people affected' 
AFTER `issue_type`;

-- ============================================================
-- PART 3: Add Structured Location Fields
-- ============================================================

-- Add hierarchical location fields
ALTER TABLE `issue_reports` 
ADD COLUMN `main_community_id` INT(11) UNSIGNED NULL 
COMMENT 'Main community (top-level location)' 
AFTER `location`;

ALTER TABLE `issue_reports` 
ADD COLUMN `smaller_community_id` INT(11) UNSIGNED NULL 
COMMENT 'Smaller community (second-level location)' 
AFTER `main_community_id`;

ALTER TABLE `issue_reports` 
ADD COLUMN `suburb_id` INT(11) UNSIGNED NULL 
COMMENT 'Suburb (under main community)' 
AFTER `smaller_community_id`;

ALTER TABLE `issue_reports` 
ADD COLUMN `cottage_id` INT(11) UNSIGNED NULL 
COMMENT 'Cottage (under smaller community)' 
AFTER `suburb_id`;

-- Add foreign keys for location hierarchy
ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_main_community` 
FOREIGN KEY (`main_community_id`) REFERENCES `locations`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_smaller_community` 
FOREIGN KEY (`smaller_community_id`) REFERENCES `locations`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_suburb` 
FOREIGN KEY (`suburb_id`) REFERENCES `locations`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_cottage` 
FOREIGN KEY (`cottage_id`) REFERENCES `locations`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- PART 4: Add Constituent Information Fields
-- ============================================================

-- Rename existing reporter fields for clarity
ALTER TABLE `issue_reports` 
CHANGE COLUMN `reporter_name` `constituent_name` VARCHAR(255) DEFAULT NULL 
COMMENT 'Name of the constituent making the report';

ALTER TABLE `issue_reports` 
CHANGE COLUMN `reporter_email` `constituent_email` VARCHAR(255) DEFAULT NULL 
COMMENT 'Email of the constituent (if available)';

ALTER TABLE `issue_reports` 
CHANGE COLUMN `reporter_phone` `constituent_contact` VARCHAR(50) DEFAULT NULL 
COMMENT 'Contact phone of the constituent';

-- Add new constituent fields
ALTER TABLE `issue_reports` 
ADD COLUMN `constituent_gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL 
COMMENT 'Gender of the constituent' 
AFTER `constituent_contact`;

ALTER TABLE `issue_reports` 
ADD COLUMN `constituent_address` TEXT DEFAULT NULL 
COMMENT 'Home address of the constituent' 
AFTER `constituent_gender`;

-- ============================================================
-- PART 5: Create Indexes for Performance
-- ============================================================

-- Indexes for new classification fields
CREATE INDEX `idx_issue_sector` ON `issue_reports`(`sector_id`);
CREATE INDEX `idx_issue_sub_sector` ON `issue_reports`(`sub_sector_id`);
CREATE INDEX `idx_issue_type` ON `issue_reports`(`issue_type`);

-- Indexes for new location fields
CREATE INDEX `idx_issue_main_community` ON `issue_reports`(`main_community_id`);
CREATE INDEX `idx_issue_smaller_community` ON `issue_reports`(`smaller_community_id`);
CREATE INDEX `idx_issue_suburb` ON `issue_reports`(`suburb_id`);
CREATE INDEX `idx_issue_cottage` ON `issue_reports`(`cottage_id`);

-- Composite index for location queries
CREATE INDEX `idx_issue_location_hierarchy` 
ON `issue_reports`(`main_community_id`, `smaller_community_id`, `suburb_id`, `cottage_id`);

-- ============================================================
-- PART 6: Add "Other" Location Entries
-- ============================================================

-- Insert "Other" entries for orphaned suburbs/cottages
INSERT INTO `locations` (`name`, `type`, `status`, `created_at`, `updated_at`) 
VALUES 
  ('Other', 'community', 'active', NOW(), NOW()),
  ('Other', 'smaller_community', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- ============================================================
-- PART 7: Add Review Tracking Fields
-- ============================================================

-- Add officer review tracking
ALTER TABLE `issue_reports` 
ADD COLUMN `reviewed_by_officer_id` INT(11) UNSIGNED NULL 
COMMENT 'Officer who reviewed and forwarded the issue' 
AFTER `assigned_officer_id`;

ALTER TABLE `issue_reports` 
ADD COLUMN `reviewed_at` TIMESTAMP NULL DEFAULT NULL 
COMMENT 'When the issue was reviewed by officer' 
AFTER `reviewed_by_officer_id`;

-- Add assessment review tracking
ALTER TABLE `issue_reports` 
ADD COLUMN `assessment_reviewed_by` INT(11) UNSIGNED NULL 
COMMENT 'Admin who reviewed the assessment' 
AFTER `assigned_to_task_force_at`;

ALTER TABLE `issue_reports` 
ADD COLUMN `assessment_reviewed_at` TIMESTAMP NULL DEFAULT NULL 
COMMENT 'When the assessment was reviewed' 
AFTER `assessment_reviewed_by`;

ALTER TABLE `issue_reports` 
ADD COLUMN `assessment_decision` ENUM('approved', 'rejected', 'needs_revision') DEFAULT NULL 
COMMENT 'Admin decision on assessment' 
AFTER `assessment_reviewed_at`;

-- ============================================================
-- END OF MIGRATION
-- ============================================================

-- NOTES:
-- 1. Run this migration using: mysql -u root -p const_dev_db < migration_001_extend_issue_reports.sql
-- 2. Or use Phinx if configured: vendor/bin/phinx migrate
-- 3. Backup your database before running this migration
-- 4. The `location` VARCHAR field is kept for backward compatibility
-- 5. Gradually migrate data from `location` to structured fields
