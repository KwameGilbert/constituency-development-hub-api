-- Migration: Create Officers Table and Community Assignment System
-- Created: 2026-01-17
-- Description: Creates officers table with community assignments to support the workflow

-- ============================================================
-- PART 1: Create Officers Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `officers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `officer_code` VARCHAR(50) DEFAULT NULL COMMENT 'Unique officer identifier',
  `supervisor_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Senior officer supervising this officer',
  `assigned_main_communities` JSON DEFAULT NULL COMMENT 'Array of main community IDs',
  `assigned_smaller_communities` JSON DEFAULT NULL COMMENT 'Array of smaller community IDs',
  `can_review_reports` TINYINT(1) NOT NULL DEFAULT 1,
  `can_forward_to_admin` TINYINT(1) NOT NULL DEFAULT 1,
  `can_submit_reports` TINYINT(1) NOT NULL DEFAULT 1,
  `profile_image` VARCHAR(500) DEFAULT NULL,
  `id_type` ENUM('ghana_card', 'voter_id', 'passport', 'drivers_license') DEFAULT NULL,
  `id_number` VARCHAR(100) DEFAULT NULL,
  `id_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `id_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `emergency_contact_name` VARCHAR(255) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(50) DEFAULT NULL,
  `reports_reviewed` INT(11) NOT NULL DEFAULT 0,
  `reports_submitted` INT(11) NOT NULL DEFAULT 0,
  `last_active_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`supervisor_id`) REFERENCES `officers`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_officer_user` (`user_id`),
  UNIQUE KEY `unique_officer_code` (`officer_code`),
  INDEX `idx_officer_communities` (`assigned_main_communities`(255), `assigned_smaller_communities`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PART 2: Create Officer-Community Assignment Junction Table
-- ============================================================
-- Alternative normalized approach (if preferred over JSON)

CREATE TABLE IF NOT EXISTS `officer_community_assignments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `officer_id` INT(11) UNSIGNED NOT NULL,
  `location_id` INT(11) UNSIGNED NOT NULL,
  `location_type` ENUM('main_community', 'smaller_community') NOT NULL,
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Admin who made the assignment',
  
  FOREIGN KEY (`officer_id`) REFERENCES `officers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_officer_location` (`officer_id`, `location_id`),
  INDEX `idx_location_assignments` (`location_id`, `location_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PART 3: Add Foreign Key for Officer Review
-- ============================================================

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_reviewed_by_officer` 
FOREIGN KEY (`reviewed_by_officer_id`) REFERENCES `officers`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- PART 4: Create Helper Functions/Procedures (Optional)
-- ============================================================

DELIMITER $$

-- Procedure to generate unique officer code
DROP PROCEDURE IF EXISTS `generate_officer_code`$$
CREATE PROCEDURE `generate_officer_code`(OUT officer_code VARCHAR(50))
BEGIN
  DECLARE code_exists INT DEFAULT 1;
  DECLARE new_code VARCHAR(50);
  DECLARE counter INT DEFAULT 1;
  
  WHILE code_exists = 1 DO
    SET new_code = CONCAT('OFC-', LPAD(counter, 4, '0'));
    SELECT COUNT(*) INTO code_exists FROM `officers` WHERE `officer_code` = new_code;
    SET counter = counter + 1;
  END WHILE;
  
  SET officer_code = new_code;
END$$

DELIMITER ;

-- ============================================================
-- PART 5: Migrate Existing Data (if needed)
-- ============================================================

-- If there's an existing officer-like table or data, migrate here
-- Example: INSERT INTO officers SELECT ... FROM old_officers_table;

-- ============================================================
-- PART 6: Sample Data for Testing
-- ============================================================

-- Insert sample officers (adjust based on existing users table)
-- Uncomment and modify as needed:

/*
INSERT INTO `officers` (
  `user_id`, 
  `officer_code`, 
  `assigned_main_communities`, 
  `assigned_smaller_communities`,
  `can_review_reports`,
  `can_forward_to_admin`,
  `can_submit_reports`
) VALUES 
  (
    (SELECT id FROM users WHERE role = 'officer' LIMIT 1),
    'OFC-0001',
    JSON_ARRAY(1, 3), -- Main community IDs
    JSON_ARRAY(5),    -- Smaller community IDs
    1, 1, 1
  );
*/

-- ============================================================
-- END OF MIGRATION
-- ============================================================

-- NOTES:
-- 1. This creates the officers table with community assignments
-- 2. Two approaches provided:
--    - JSON fields (assigned_main_communities, assigned_smaller_communities)
--    - Junction table (officer_community_assignments) for normalized approach
-- 3. Use whichever approach fits your needs (or both)
-- 4. The JSON approach is simpler for quick lookups
-- 5. The junction table approach is more normalized and flexible
