-- ============================================================
-- Seed: Categories, Sectors & Sub-Sectors
-- Generated: 2026-02-11
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Clear existing data (child first)
DELETE FROM `sub_sectors`;
DELETE FROM `sectors`;
DELETE FROM `categories`;

-- ============================================================
-- CATEGORIES
-- ============================================================
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `display_order`, `status`) VALUES
(1, 'WATER', 'water', 'Water supply, infrastructure and flood control', NULL, '#3b82f6', 1, 'active'),
(2, 'ELECTRICITY', 'electricity', 'Power supply, electrical infrastructure and street lighting', NULL, '#f59e0b', 2, 'active'),
(3, 'ROADS', 'roads', 'Feeder roads, urban roads and transport structures', NULL, '#6b7280', 3, 'active'),
(4, 'SANITATION', 'sanitation', 'Waste management, drainage, sewage and public hygiene', NULL, '#10b981', 4, 'active'),
(5, 'HEALTH', 'health', 'Health facilities, personnel, services and medical supplies', NULL, '#ef4444', 5, 'active'),
(6, 'EDUCATION', 'education', 'School infrastructure, teaching, learning and welfare', NULL, '#8b5cf6', 6, 'active'),
(7, 'AGRICULTURE', 'agriculture', 'Crop farming, livestock, fisheries and agricultural infrastructure', NULL, '#22c55e', 7, 'active'),
(8, 'EMPLOYMENT', 'employment', 'Unemployment, skills training and labour issues', NULL, '#0ea5e9', 8, 'active'),
(9, 'SOCIAL WELFARE ASSISTANCE', 'social-welfare-assistance', 'Financial support, vulnerable groups and social protection', NULL, '#ec4899', 9, 'active'),
(10, 'OTHER', 'other', 'Security, land, housing, governance and civic issues', NULL, '#64748b', 10, 'active');

-- ============================================================
-- SECTORS
-- ============================================================
INSERT INTO `sectors` (`id`, `category_id`, `name`, `slug`, `description`, `display_order`, `status`) VALUES
-- WATER
(1, 1, 'Urban Water Supply', 'urban-water-supply', NULL, 1, 'active'),
(2, 1, 'Rural Water Supply', 'rural-water-supply', NULL, 2, 'active'),
(3, 1, 'Water Infrastructure & Flood Control', 'water-infrastructure-flood-control', NULL, 3, 'active'),
-- ELECTRICITY
(4, 2, 'Power Supply', 'power-supply', NULL, 1, 'active'),
(5, 2, 'Electrical Infrastructure', 'electrical-infrastructure', NULL, 2, 'active'),
(6, 2, 'Street Lighting', 'street-lighting', NULL, 3, 'active'),
-- ROADS
(7, 3, 'Feeder Roads', 'feeder-roads', NULL, 1, 'active'),
(8, 3, 'Urban Roads & Streets', 'urban-roads-streets', NULL, 2, 'active'),
(9, 3, 'Bridges & Transport Structures', 'bridges-transport-structures', NULL, 3, 'active'),
-- SANITATION
(10, 4, 'Waste Management', 'waste-management', NULL, 1, 'active'),
(11, 4, 'Drainage & Sewage', 'drainage-sewage', NULL, 2, 'active'),
(12, 4, 'Public Toilets & Hygiene', 'public-toilets-hygiene', NULL, 3, 'active'),
-- HEALTH
(13, 5, 'Health Facilities', 'health-facilities', NULL, 1, 'active'),
(14, 5, 'Health Personnel & Services', 'health-personnel-services', NULL, 2, 'active'),
(15, 5, 'Medical Supplies & Public Health', 'medical-supplies-public-health', NULL, 3, 'active'),
-- EDUCATION
(16, 6, 'School Infrastructure', 'school-infrastructure', NULL, 1, 'active'),
(17, 6, 'Teaching & Learning', 'teaching-learning', NULL, 2, 'active'),
(18, 6, 'School Welfare & Access', 'school-welfare-access', NULL, 3, 'active'),
-- AGRICULTURE
(19, 7, 'Crop Farming', 'crop-farming', NULL, 1, 'active'),
(20, 7, 'Livestock & Fisheries', 'livestock-fisheries', NULL, 2, 'active'),
(21, 7, 'Agricultural Infrastructure & Markets', 'agricultural-infrastructure-markets', NULL, 3, 'active'),
-- EMPLOYMENT
(22, 8, 'Unemployment', 'unemployment', NULL, 1, 'active'),
(23, 8, 'Skills & Training', 'skills-training', NULL, 2, 'active'),
(24, 8, 'Labour Issues', 'labour-issues', NULL, 3, 'active'),
-- SOCIAL WELFARE ASSISTANCE
(25, 9, 'Financial & Material Support', 'financial-material-support', NULL, 1, 'active'),
(26, 9, 'Vulnerable Groups Support', 'vulnerable-groups-support', NULL, 2, 'active'),
(27, 9, 'Social Protection Programs', 'social-protection-programs', NULL, 3, 'active'),
-- OTHER
(28, 10, 'Security & Safety', 'security-safety', NULL, 1, 'active'),
(29, 10, 'Land & Housing', 'land-housing', NULL, 2, 'active'),
(30, 10, 'Governance & Civic Issues', 'governance-civic-issues', NULL, 3, 'active');

-- ============================================================
-- SUB-SECTORS
-- ============================================================

-- Sector 1: Urban Water Supply (category: WATER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(1, 'No water supply', '1.1.1', 1, 'active'),
(1, 'Irregular water flow', '1.1.2', 2, 'active'),
(1, 'Low water pressure', '1.1.3', 3, 'active'),
(1, 'Burst water pipes', '1.1.4', 4, 'active'),
(1, 'Broken water meters', '1.1.5', 5, 'active'),
(1, 'Illegal water connections', '1.1.6', 6, 'active'),
(1, 'Billing disputes', '1.1.7', 7, 'active'),
(1, 'Poor water quality', '1.1.8', 8, 'active'),
(1, 'Old/damaged pipelines', '1.1.9', 9, 'active'),
(1, 'New household connection request', '1.1.10', 10, 'active'),
(1, 'Disconnected water supply', '1.1.11', 11, 'active');

-- Sector 2: Rural Water Supply (category: WATER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(2, 'Broken borehole', '1.2.1', 1, 'active'),
(2, 'Faulty hand pump', '1.2.2', 2, 'active'),
(2, 'Dry well', '1.2.3', 3, 'active'),
(2, 'Contaminated well water', '1.2.4', 4, 'active'),
(2, 'Community water shortage', '1.2.5', 5, 'active'),
(2, 'Lack of borehole maintenance', '1.2.6', 6, 'active'),
(2, 'Broken overhead water tank', '1.2.7', 7, 'active'),
(2, 'Long distance to water source', '1.2.8', 8, 'active'),
(2, 'Seasonal water scarcity', '1.2.9', 9, 'active'),
(2, 'Request for new borehole', '1.2.10', 10, 'active'),
(2, 'Poor water management committee', '1.2.11', 11, 'active');

-- Sector 3: Water Infrastructure & Flood Control (category: WATER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(3, 'Flooding due to drainage overflow', '1.3.1', 1, 'active'),
(3, 'Damaged culverts', '1.3.2', 2, 'active'),
(3, 'Poor storm water channels', '1.3.3', 3, 'active'),
(3, 'Blocked waterways', '1.3.4', 4, 'active'),
(3, 'Erosion from water runoff', '1.3.5', 5, 'active'),
(3, 'Collapsed embankments', '1.3.6', 6, 'active'),
(3, 'Poor water diversion systems', '1.3.7', 7, 'active'),
(3, 'Overflowing dams', '1.3.8', 8, 'active'),
(3, 'Water infrastructure neglect', '1.3.9', 9, 'active'),
(3, 'Request for flood prevention works', '1.3.10', 10, 'active');

-- Sector 4: Power Supply (category: ELECTRICITY)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(4, 'Total power outage', '2.1.1', 1, 'active'),
(4, 'Frequent power cuts (dumsor)', '2.1.2', 2, 'active'),
(4, 'Low voltage', '2.1.3', 3, 'active'),
(4, 'Power fluctuations', '2.1.4', 4, 'active'),
(4, 'Transformer overload', '2.1.5', 5, 'active'),
(4, 'Burnt transformer', '2.1.6', 6, 'active'),
(4, 'Scheduled outage complaints', '2.1.7', 7, 'active'),
(4, 'Unannounced outages', '2.1.8', 8, 'active'),
(4, 'Community blackout', '2.1.9', 9, 'active'),
(4, 'Power rationing issues', '2.1.10', 10, 'active');

-- Sector 5: Electrical Infrastructure (category: ELECTRICITY)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(5, 'Fallen electric poles', '2.2.1', 1, 'active'),
(5, 'Exposed cables', '2.2.2', 2, 'active'),
(5, 'Broken cross arms', '2.2.3', 3, 'active'),
(5, 'Damaged transformers', '2.2.4', 4, 'active'),
(5, 'Leaning electric poles', '2.2.5', 5, 'active'),
(5, 'Street wiring hazards', '2.2.6', 6, 'active'),
(5, 'Old electrical installations', '2.2.7', 7, 'active'),
(5, 'Substation faults', '2.2.8', 8, 'active'),
(5, 'Illegal power connections', '2.2.9', 9, 'active'),
(5, 'Infrastructure vandalism', '2.2.10', 10, 'active');

-- Sector 6: Street Lighting (category: ELECTRICITY)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(6, 'Non-functional streetlights', '2.3.1', 1, 'active'),
(6, 'Broken streetlight poles', '2.3.2', 2, 'active'),
(6, 'Missing bulbs', '2.3.3', 3, 'active'),
(6, 'Poorly lit roads', '2.3.4', 4, 'active'),
(6, 'Vandalized streetlights', '2.3.5', 5, 'active'),
(6, 'Delayed repairs', '2.3.6', 6, 'active'),
(6, 'Newly installed lights not working', '2.3.7', 7, 'active'),
(6, 'Faulty solar streetlights', '2.3.8', 8, 'active'),
(6, 'Request for new streetlights', '2.3.9', 9, 'active'),
(6, 'Unsafe dark areas', '2.3.10', 10, 'active');

-- Sector 7: Feeder Roads (category: ROADS)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(7, 'Potholes', '3.1.1', 1, 'active'),
(7, 'Erosion damage', '3.1.2', 2, 'active'),
(7, 'Muddy roads during rains', '3.1.3', 3, 'active'),
(7, 'Dusty roads during dry season', '3.1.4', 4, 'active'),
(7, 'Impassable roads', '3.1.5', 5, 'active'),
(7, 'Broken culverts', '3.1.6', 6, 'active'),
(7, 'Road narrowing', '3.1.7', 7, 'active'),
(7, 'Poor drainage', '3.1.8', 8, 'active'),
(7, 'Road shoulder collapse', '3.1.9', 9, 'active'),
(7, 'Delayed road maintenance', '3.1.10', 10, 'active');

-- Sector 8: Urban Roads & Streets (category: ROADS)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(8, 'Damaged asphalt roads', '3.2.1', 1, 'active'),
(8, 'Flooded streets', '3.2.2', 2, 'active'),
(8, 'Poor road markings', '3.2.3', 3, 'active'),
(8, 'Missing road signs', '3.2.4', 4, 'active'),
(8, 'Traffic congestion points', '3.2.5', 5, 'active'),
(8, 'Accidents due to road condition', '3.2.6', 6, 'active'),
(8, 'Street encroachment', '3.2.7', 7, 'active'),
(8, 'Broken speed ramps', '3.2.8', 8, 'active'),
(8, 'Uneven road surfaces', '3.2.9', 9, 'active'),
(8, 'Pedestrian safety issues', '3.2.10', 10, 'active');

-- Sector 9: Bridges & Transport Structures (category: ROADS)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(9, 'Collapsed bridges', '3.3.1', 1, 'active'),
(9, 'Weak wooden bridges', '3.3.2', 2, 'active'),
(9, 'Damaged culverts', '3.3.3', 3, 'active'),
(9, 'Unsafe footbridges', '3.3.4', 4, 'active'),
(9, 'Flood-prone crossings', '3.3.5', 5, 'active'),
(9, 'Bridge erosion', '3.3.6', 6, 'active'),
(9, 'Missing guardrails', '3.3.7', 7, 'active'),
(9, 'Structural cracks', '3.3.8', 8, 'active'),
(9, 'Overloaded bridges', '3.3.9', 9, 'active'),
(9, 'Request for new bridge', '3.3.10', 10, 'active');

-- Sector 10: Waste Management (category: SANITATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(10, 'Refuse not collected', '4.1.1', 1, 'active'),
(10, 'Overflowing waste containers', '4.1.2', 2, 'active'),
(10, 'Illegal dumping', '4.1.3', 3, 'active'),
(10, 'Lack of waste bins', '4.1.4', 4, 'active'),
(10, 'Burning of refuse', '4.1.5', 5, 'active'),
(10, 'Poor landfill management', '4.1.6', 6, 'active'),
(10, 'Waste collection delays', '4.1.7', 7, 'active'),
(10, 'Community filth', '4.1.8', 8, 'active'),
(10, 'Market sanitation issues', '4.1.9', 9, 'active'),
(10, 'Request for waste skips', '4.1.10', 10, 'active');

-- Sector 11: Drainage & Sewage (category: SANITATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(11, 'Blocked drains', '4.2.1', 1, 'active'),
(11, 'Overflowing gutters', '4.2.2', 2, 'active'),
(11, 'Sewage leaks', '4.2.3', 3, 'active'),
(11, 'Open drains', '4.2.4', 4, 'active'),
(11, 'Flooding due to poor drainage', '4.2.5', 5, 'active'),
(11, 'Broken manholes', '4.2.6', 6, 'active'),
(11, 'Poor drain construction', '4.2.7', 7, 'active'),
(11, 'Stagnant water', '4.2.8', 8, 'active'),
(11, 'Drain siltation', '4.2.9', 9, 'active'),
(11, 'Drain maintenance delays', '4.2.10', 10, 'active');

-- Sector 12: Public Toilets & Hygiene (category: SANITATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(12, 'Broken public toilets', '4.3.1', 1, 'active'),
(12, 'No toilet facilities', '4.3.2', 2, 'active'),
(12, 'Poorly maintained toilets', '4.3.3', 3, 'active'),
(12, 'Open defecation', '4.3.4', 4, 'active'),
(12, 'Lack of handwashing facilities', '4.3.5', 5, 'active'),
(12, 'Bad odor complaints', '4.3.6', 6, 'active'),
(12, 'Toilet user fee issues', '4.3.7', 7, 'active'),
(12, 'Unsafe toilet structures', '4.3.8', 8, 'active'),
(12, 'Toilet management problems', '4.3.9', 9, 'active'),
(12, 'Request for new toilet facility', '4.3.10', 10, 'active');

-- Sector 13: Health Facilities (category: HEALTH)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(13, 'No health facility nearby', '5.1.1', 1, 'active'),
(13, 'Dilapidated clinic buildings', '5.1.2', 2, 'active'),
(13, 'Lack of beds', '5.1.3', 3, 'active'),
(13, 'Poor sanitation in facilities', '5.1.4', 4, 'active'),
(13, 'No electricity in clinic', '5.1.5', 5, 'active'),
(13, 'No water in facility', '5.1.6', 6, 'active'),
(13, 'Overcrowded facilities', '5.1.7', 7, 'active'),
(13, 'Facility expansion request', '5.1.8', 8, 'active'),
(13, 'Unsafe facility structures', '5.1.9', 9, 'active'),
(13, 'Delayed facility completion', '5.1.10', 10, 'active');

-- Sector 14: Health Personnel & Services (category: HEALTH)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(14, 'Shortage of doctors', '5.2.1', 1, 'active'),
(14, 'Shortage of nurses', '5.2.2', 2, 'active'),
(14, 'Staff absenteeism', '5.2.3', 3, 'active'),
(14, 'Poor staff attitude', '5.2.4', 4, 'active'),
(14, 'Long waiting times', '5.2.5', 5, 'active'),
(14, 'Emergency response delays', '5.2.6', 6, 'active'),
(14, 'Poor maternal care', '5.2.7', 7, 'active'),
(14, 'Child health services issues', '5.2.8', 8, 'active'),
(14, 'Inadequate night services', '5.2.9', 9, 'active'),
(14, 'Staff accommodation issues', '5.2.10', 10, 'active');

-- Sector 15: Medical Supplies & Public Health (category: HEALTH)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(15, 'Lack of essential drugs', '5.3.1', 1, 'active'),
(15, 'Expired medicines', '5.3.2', 2, 'active'),
(15, 'Broken medical equipment', '5.3.3', 3, 'active'),
(15, 'No ambulance service', '5.3.4', 4, 'active'),
(15, 'Disease outbreak reports', '5.3.5', 5, 'active'),
(15, 'Poor vaccination coverage', '5.3.6', 6, 'active'),
(15, 'Malaria prevalence', '5.3.7', 7, 'active'),
(15, 'Cholera risk', '5.3.8', 8, 'active'),
(15, 'Public health education gaps', '5.3.9', 9, 'active'),
(15, 'Medical outreach request', '5.3.10', 10, 'active');

-- Sector 16: School Infrastructure (category: EDUCATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(16, 'Dilapidated classrooms', '6.1.1', 1, 'active'),
(16, 'School roofs leaking', '6.1.2', 2, 'active'),
(16, 'Cracked walls', '6.1.3', 3, 'active'),
(16, 'No school buildings', '6.1.4', 4, 'active'),
(16, 'Overcrowded classrooms', '6.1.5', 5, 'active'),
(16, 'Incomplete school projects', '6.1.6', 6, 'active'),
(16, 'Unsafe school structures', '6.1.7', 7, 'active'),
(16, 'Lack of electricity', '6.1.8', 8, 'active'),
(16, 'Lack of water', '6.1.9', 9, 'active'),
(16, 'Fence/security issues', '6.1.10', 10, 'active');

-- Sector 17: Teaching & Learning (category: EDUCATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(17, 'Teacher shortage', '6.2.1', 1, 'active'),
(17, 'Teacher absenteeism', '6.2.2', 2, 'active'),
(17, 'Poor teaching quality', '6.2.3', 3, 'active'),
(17, 'Lack of textbooks', '6.2.4', 4, 'active'),
(17, 'No teaching aids', '6.2.5', 5, 'active'),
(17, 'Poor student performance', '6.2.6', 6, 'active'),
(17, 'Overloaded teachers', '6.2.7', 7, 'active'),
(17, 'Untrained teachers', '6.2.8', 8, 'active'),
(17, 'Poor supervision', '6.2.9', 9, 'active'),
(17, 'Curriculum implementation issues', '6.2.10', 10, 'active');

-- Sector 18: School Welfare & Access (category: EDUCATION)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(18, 'Lack of desks/furniture', '6.3.1', 1, 'active'),
(18, 'School feeding issues', '6.3.2', 2, 'active'),
(18, 'Long distance to school', '6.3.3', 3, 'active'),
(18, 'Unsafe routes to school', '6.3.4', 4, 'active'),
(18, 'School sanitation issues', '6.3.5', 5, 'active'),
(18, 'No disability access', '6.3.6', 6, 'active'),
(18, 'Dropout cases', '6.3.7', 7, 'active'),
(18, 'Child labour concerns', '6.3.8', 8, 'active'),
(18, 'School uniforms assistance', '6.3.9', 9, 'active'),
(18, 'Sanitary pad support', '6.3.10', 10, 'active');

-- Sector 19: Crop Farming (category: AGRICULTURE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(19, 'Crop failure', '7.1.1', 1, 'active'),
(19, 'Pest infestation', '7.1.2', 2, 'active'),
(19, 'Plant disease outbreak', '7.1.3', 3, 'active'),
(19, 'Poor soil fertility', '7.1.4', 4, 'active'),
(19, 'Lack of seeds', '7.1.5', 5, 'active'),
(19, 'Lack of fertilizer', '7.1.6', 6, 'active'),
(19, 'Poor extension services', '7.1.7', 7, 'active'),
(19, 'Climate impact on crops', '7.1.8', 8, 'active'),
(19, 'Post-harvest losses', '7.1.9', 9, 'active'),
(19, 'Request for farm inputs', '7.1.10', 10, 'active');

-- Sector 20: Livestock & Fisheries (category: AGRICULTURE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(20, 'Livestock disease outbreak', '7.2.1', 1, 'active'),
(20, 'Animal deaths', '7.2.2', 2, 'active'),
(20, 'Lack of veterinary services', '7.2.3', 3, 'active'),
(20, 'Feed shortages', '7.2.4', 4, 'active'),
(20, 'Poor animal housing', '7.2.5', 5, 'active'),
(20, 'Theft of livestock', '7.2.6', 6, 'active'),
(20, 'Poultry disease', '7.2.7', 7, 'active'),
(20, 'Fish pond collapse', '7.2.8', 8, 'active'),
(20, 'Lack of fingerlings', '7.2.9', 9, 'active'),
(20, 'Request for livestock support', '7.2.10', 10, 'active');

-- Sector 21: Agricultural Infrastructure & Markets (category: AGRICULTURE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(21, 'Poor farm roads', '7.3.1', 1, 'active'),
(21, 'Lack of storage facilities', '7.3.2', 2, 'active'),
(21, 'No drying platforms', '7.3.3', 3, 'active'),
(21, 'Market access problems', '7.3.4', 4, 'active'),
(21, 'Poor irrigation systems', '7.3.5', 5, 'active'),
(21, 'Broken dams', '7.3.6', 6, 'active'),
(21, 'Farm tool shortages', '7.3.7', 7, 'active'),
(21, 'Lack of processing centers', '7.3.8', 8, 'active'),
(21, 'Poor pricing issues', '7.3.9', 9, 'active'),
(21, 'Market infrastructure needs', '7.3.10', 10, 'active');

-- Sector 22: Unemployment (category: EMPLOYMENT)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(22, 'Youth unemployment', '8.1.1', 1, 'active'),
(22, 'Graduate unemployment', '8.1.2', 2, 'active'),
(22, 'Long-term unemployment', '8.1.3', 3, 'active'),
(22, 'Seasonal job loss', '8.1.4', 4, 'active'),
(22, 'School leavers unemployment', '8.1.5', 5, 'active'),
(22, 'Lack of local jobs', '8.1.6', 6, 'active'),
(22, 'Job mismatch', '8.1.7', 7, 'active'),
(22, 'Underemployment', '8.1.8', 8, 'active'),
(22, 'Community unemployment crisis', '8.1.9', 9, 'active'),
(22, 'Employment support request', '8.1.10', 10, 'active');

-- Sector 23: Skills & Training (category: EMPLOYMENT)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(23, 'No vocational centers', '8.2.1', 1, 'active'),
(23, 'Lack of apprenticeship opportunities', '8.2.2', 2, 'active'),
(23, 'Skills training request', '8.2.3', 3, 'active'),
(23, 'Poor training facilities', '8.2.4', 4, 'active'),
(23, 'Inaccessible training programs', '8.2.5', 5, 'active'),
(23, 'Cost of training', '8.2.6', 6, 'active'),
(23, 'Inadequate instructors', '8.2.7', 7, 'active'),
(23, 'Certification delays', '8.2.8', 8, 'active'),
(23, 'Outdated training programs', '8.2.9', 9, 'active'),
(23, 'Youth skills development request', '8.2.10', 10, 'active');

-- Sector 24: Labour Issues (category: EMPLOYMENT)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(24, 'Unpaid wages', '8.3.1', 1, 'active'),
(24, 'Delayed salaries', '8.3.2', 2, 'active'),
(24, 'Unsafe working conditions', '8.3.3', 3, 'active'),
(24, 'Workplace harassment', '8.3.4', 4, 'active'),
(24, 'Employment discrimination', '8.3.5', 5, 'active'),
(24, 'Contract violations', '8.3.6', 6, 'active'),
(24, 'Child labour', '8.3.7', 7, 'active'),
(24, 'Employer misconduct', '8.3.8', 8, 'active'),
(24, 'Casual labour exploitation', '8.3.9', 9, 'active'),
(24, 'Labour dispute resolution request', '8.3.10', 10, 'active');

-- Sector 25: Financial & Material Support (category: SOCIAL WELFARE ASSISTANCE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(25, 'Extreme poverty assistance', '9.1.1', 1, 'active'),
(25, 'Emergency financial support', '9.1.2', 2, 'active'),
(25, 'Food aid request', '9.1.3', 3, 'active'),
(25, 'Rent assistance', '9.1.4', 4, 'active'),
(25, 'School fees support', '9.1.5', 5, 'active'),
(25, 'Medical bill assistance', '9.1.6', 6, 'active'),
(25, 'Funeral support', '9.1.7', 7, 'active'),
(25, 'Disaster relief support', '9.1.8', 8, 'active'),
(25, 'Clothing assistance', '9.1.9', 9, 'active'),
(25, 'Household support request', '9.1.10', 10, 'active');

-- Sector 26: Vulnerable Groups Support (category: SOCIAL WELFARE ASSISTANCE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(26, 'Persons with disabilities support', '9.2.1', 1, 'active'),
(26, 'Elderly care support', '9.2.2', 2, 'active'),
(26, 'Orphans support', '9.2.3', 3, 'active'),
(26, 'Widows support', '9.2.4', 4, 'active'),
(26, 'Single parent assistance', '9.2.5', 5, 'active'),
(26, 'Street children support', '9.2.6', 6, 'active'),
(26, 'Abandoned persons', '9.2.7', 7, 'active'),
(26, 'Mental health support', '9.2.8', 8, 'active'),
(26, 'Chronic illness support', '9.2.9', 9, 'active'),
(26, 'Caregiver assistance', '9.2.10', 10, 'active');

-- Sector 27: Social Protection Programs (category: SOCIAL WELFARE ASSISTANCE)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(27, 'LEAP enrollment issues', '9.3.1', 1, 'active'),
(27, 'NHIS registration problems', '9.3.2', 2, 'active'),
(27, 'Disability fund access', '9.3.3', 3, 'active'),
(27, 'Social grants delays', '9.3.4', 4, 'active'),
(27, 'Beneficiary exclusion complaints', '9.3.5', 5, 'active'),
(27, 'Payment irregularities', '9.3.6', 6, 'active'),
(27, 'Program awareness gaps', '9.3.7', 7, 'active'),
(27, 'Complaints about officials', '9.3.8', 8, 'active'),
(27, 'Verification delays', '9.3.9', 9, 'active'),
(27, 'Program expansion request', '9.3.10', 10, 'active');

-- Sector 28: Security & Safety (category: OTHER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(28, 'Theft incidents', '10.1.1', 1, 'active'),
(28, 'Armed robbery concerns', '10.1.2', 2, 'active'),
(28, 'Community insecurity', '10.1.3', 3, 'active'),
(28, 'Police response delays', '10.1.4', 4, 'active'),
(28, 'Vigilante activity', '10.1.5', 5, 'active'),
(28, 'Drug abuse concerns', '10.1.6', 6, 'active'),
(28, 'Domestic violence', '10.1.7', 7, 'active'),
(28, 'Night security issues', '10.1.8', 8, 'active'),
(28, 'Unsafe public spaces', '10.1.9', 9, 'active'),
(28, 'Community patrol request', '10.1.10', 10, 'active');

-- Sector 29: Land & Housing (category: OTHER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(29, 'Land disputes', '10.2.1', 1, 'active'),
(29, 'Boundary conflicts', '10.2.2', 2, 'active'),
(29, 'Illegal land sales', '10.2.3', 3, 'active'),
(29, 'Housing demolition threats', '10.2.4', 4, 'active'),
(29, 'Poor housing conditions', '10.2.5', 5, 'active'),
(29, 'Encroachment issues', '10.2.6', 6, 'active'),
(29, 'Building permit problems', '10.2.7', 7, 'active'),
(29, 'Housing assistance request', '10.2.8', 8, 'active'),
(29, 'Settlement planning issues', '10.2.9', 9, 'active'),
(29, 'Land documentation issues', '10.2.10', 10, 'active');

-- Sector 30: Governance & Civic Issues (category: OTHER)
INSERT INTO `sub_sectors` (`sector_id`, `name`, `code`, `display_order`, `status`) VALUES
(30, 'Abuse of authority', '10.3.1', 1, 'active'),
(30, 'Corruption allegations', '10.3.2', 2, 'active'),
(30, 'Poor service delivery', '10.3.3', 3, 'active'),
(30, 'Community leadership disputes', '10.3.4', 4, 'active'),
(30, 'Exclusion from government programs', '10.3.5', 5, 'active'),
(30, 'Electoral concerns', '10.3.6', 6, 'active'),
(30, 'Public engagement complaints', '10.3.7', 7, 'active'),
(30, 'Lack of information', '10.3.8', 8, 'active'),
(30, 'Petition follow-up issues', '10.3.9', 9, 'active'),
(30, 'General civic complaint', '10.3.10', 10, 'active');

COMMIT;
