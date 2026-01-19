-- Migration: Create Sectors and Sub-Sectors Tables
-- Created: 2026-01-17
-- Description: Creates sectors and sub_sectors tables for issue classification

-- ============================================================
-- PART 1: Create Sectors Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `sectors` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL COMMENT 'Short code for sector',
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL COMMENT 'Icon name or icon class',
  `color` VARCHAR(50) DEFAULT NULL COMMENT 'Color code for UI display',
  `display_order` INT(11) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  UNIQUE KEY `unique_sector_name` (`name`),
  UNIQUE KEY `unique_sector_code` (`code`),
  INDEX `idx_sector_status` (`status`),
  INDEX `idx_sector_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PART 2: Create Sub-Sectors Table
-- ============================================================

CREATE TABLE IF NOT EXISTS `sub_sectors` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `sector_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) DEFAULT NULL COMMENT 'Short code for sub-sector',
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `display_order` INT(11) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`sector_id`) REFERENCES `sectors`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_subsector_name_per_sector` (`sector_id`, `name`),
  UNIQUE KEY `unique_subsector_code` (`code`),
  INDEX `idx_subsector_sector` (`sector_id`),
  INDEX `idx_subsector_status` (`status`),
  INDEX `idx_subsector_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PART 3: Add Foreign Keys to issue_reports
-- ============================================================

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_sector` 
FOREIGN KEY (`sector_id`) REFERENCES `sectors`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `issue_reports` 
ADD CONSTRAINT `fk_issue_sub_sector` 
FOREIGN KEY (`sub_sector_id`) REFERENCES `sub_sectors`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- PART 4: Insert Default Sectors
-- ============================================================

INSERT INTO `sectors` (`name`, `code`, `description`, `icon`, `color`, `display_order`, `status`) VALUES
('Infrastructure', 'INFRA', 'Roads, bridges, buildings, and physical structures', 'building', '#3B82F6', 1, 'active'),
('Healthcare', 'HEALTH', 'Health facilities, medical services, and public health', 'heart-pulse', '#EF4444', 2, 'active'),
('Education', 'EDU', 'Schools, educational facilities, and learning resources', 'graduation-cap', '#10B981', 3, 'active'),
('Water & Sanitation', 'WATER', 'Water supply, drainage, and sanitation systems', 'droplet', '#06B6D4', 4, 'active'),
('Electricity', 'POWER', 'Power supply, streetlights, and electrical infrastructure', 'zap', '#F59E0B', 5, 'active'),
('Roads & Transportation', 'ROADS', 'Road networks, traffic, and transportation systems', 'car', '#6B7280', 6, 'active'),
('Environment', 'ENV', 'Environmental issues, waste management, and conservation', 'leaf', '#22C55E', 7, 'active'),
('Security', 'SEC', 'Public safety, crime prevention, and emergency services', 'shield', '#DC2626', 8, 'active'),
('Social Services', 'SOCIAL', 'Community welfare, social programs, and support services', 'users', '#8B5CF6', 9, 'active'),
('Economic Development', 'ECON', 'Business development, employment, and economic issues', 'trending-up', '#14B8A6', 10, 'active'),
('General', 'GEN', 'General community issues not fitting other categories', 'circle', '#9CA3AF', 11, 'active');

-- ============================================================
-- PART 5: Insert Default Sub-Sectors
-- ============================================================

-- Infrastructure Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'INFRA'), 'Buildings', 'INFRA-BUILD', 'Government buildings, schools, hospitals', 1),
((SELECT id FROM sectors WHERE code = 'INFRA'), 'Bridges', 'INFRA-BRIDGE', 'Bridges and overpasses', 2),
((SELECT id FROM sectors WHERE code = 'INFRA'), 'Public Facilities', 'INFRA-PUBLIC', 'Markets, parks, community centers', 3);

-- Healthcare Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'HEALTH'), 'Hospitals', 'HEALTH-HOSP', 'Hospital facilities and equipment', 1),
((SELECT id FROM sectors WHERE code = 'HEALTH'), 'Clinics', 'HEALTH-CLINIC', 'Health centers and clinics', 2),
((SELECT id FROM sectors WHERE code = 'HEALTH'), 'Medical Equipment', 'HEALTH-EQUIP', 'Medical equipment and supplies', 3),
((SELECT id FROM sectors WHERE code = 'HEALTH'), 'Public Health', 'HEALTH-PUBLIC', 'Disease prevention, sanitation, health education', 4);

-- Education Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'EDU'), 'Primary Schools', 'EDU-PRIMARY', 'Primary school facilities', 1),
((SELECT id FROM sectors WHERE code = 'EDU'), 'Secondary Schools', 'EDU-SECONDARY', 'Secondary school facilities', 2),
((SELECT id FROM sectors WHERE code = 'EDU'), 'Vocational Training', 'EDU-VOC', 'Technical and vocational training centers', 3),
((SELECT id FROM sectors WHERE code = 'EDU'), 'Learning Resources', 'EDU-RESOURCES', 'Books, equipment, teaching materials', 4);

-- Water & Sanitation Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'WATER'), 'Water Supply', 'WATER-SUPPLY', 'Pipe-borne water, boreholes, wells', 1),
((SELECT id FROM sectors WHERE code = 'WATER'), 'Drainage', 'WATER-DRAIN', 'Drainage systems and gutters', 2),
((SELECT id FROM sectors WHERE code = 'WATER'), 'Waste Management', 'WATER-WASTE', 'Refuse collection, sanitation', 3),
((SELECT id FROM sectors WHERE code = 'WATER'), 'Toilets', 'WATER-TOILET', 'Public toilets and sanitation facilities', 4);

-- Electricity Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'POWER'), 'Power Supply', 'POWER-SUPPLY', 'Electricity distribution and supply', 1),
((SELECT id FROM sectors WHERE code = 'POWER'), 'Street Lights', 'POWER-LIGHTS', 'Public lighting systems', 2),
((SELECT id FROM sectors WHERE code = 'POWER'), 'Transformers', 'POWER-TRANS', 'Electrical transformers and substations', 3);

-- Roads & Transportation Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'ROADS'), 'Road Construction', 'ROADS-CONST', 'New road construction', 1),
((SELECT id FROM sectors WHERE code = 'ROADS'), 'Road Maintenance', 'ROADS-MAINT', 'Potholes, repairs, resurfacing', 2),
((SELECT id FROM sectors WHERE code = 'ROADS'), 'Traffic Management', 'ROADS-TRAFFIC', 'Traffic lights, signs, road markings', 3),
((SELECT id FROM sectors WHERE code = 'ROADS'), 'Public Transport', 'ROADS-TRANS', 'Bus stops, taxi ranks, transport services', 4);

-- Environment Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'ENV'), 'Waste Management', 'ENV-WASTE', 'Refuse disposal, recycling', 1),
((SELECT id FROM sectors WHERE code = 'ENV'), 'Green Spaces', 'ENV-GREEN', 'Parks, trees, gardens', 2),
((SELECT id FROM sectors WHERE code = 'ENV'), 'Pollution Control', 'ENV-POLLUTION', 'Air, water, noise pollution', 3),
((SELECT id FROM sectors WHERE code = 'ENV'), 'Sanitation', 'ENV-SANIT', 'Community cleanliness and hygiene', 4);

-- Security Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'SEC'), 'Crime Prevention', 'SEC-CRIME', 'Crime and safety issues', 1),
((SELECT id FROM sectors WHERE code = 'SEC'), 'Emergency Services', 'SEC-EMERG', 'Fire, ambulance, emergency response', 2),
((SELECT id FROM sectors WHERE code = 'SEC'), 'Street Lighting', 'SEC-LIGHT', 'Lighting for safety and security', 3);

-- Social Services Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'SOCIAL'), 'Welfare Programs', 'SOCIAL-WELFARE', 'Social welfare and support', 1),
((SELECT id FROM sectors WHERE code = 'SOCIAL'), 'Youth Programs', 'SOCIAL-YOUTH', 'Youth development and empowerment', 2),
((SELECT id FROM sectors WHERE code = 'SOCIAL'), 'Elderly Care', 'SOCIAL-ELDERLY', 'Services for the elderly', 3),
((SELECT id FROM sectors WHERE code = 'SOCIAL'), 'Disability Support', 'SOCIAL-DISABILITY', 'Support for persons with disabilities', 4);

-- Economic Development Sub-Sectors
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `description`, `display_order`) VALUES
((SELECT id FROM sectors WHERE code = 'ECON'), 'Employment', 'ECON-EMPLOY', 'Job creation and employment opportunities', 1),
((SELECT id FROM sectors WHERE code = 'ECON'), 'Business Development', 'ECON-BUSINESS', 'SME support, entrepreneurship', 2),
((SELECT id FROM sectors WHERE code = 'ECON'), 'Market Infrastructure', 'ECON-MARKET', 'Market facilities and trading spaces', 3);

-- ============================================================
-- END OF MIGRATION
-- ============================================================

-- NOTES:
-- 1. Sectors and sub-sectors provide hierarchical classification
-- 2. Icons use lucide-react icon names for consistency
-- 3. Colors are Tailwind CSS color codes
-- 4. Add more sub-sectors as needed for your constituency
-- 5. You can deactivate sectors/sub-sectors without deleting them
