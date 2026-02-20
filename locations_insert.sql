-- Locations Insert Script
-- Generated based on the constituency locations data
-- Communities and Smaller Communities = type 'community'
-- Suburbs and Cottages = type 'suburb'

-- First, modify the locations table to use only 'community' and 'suburb' types
ALTER TABLE `locations` MODIFY `type` enum('community','suburb') DEFAULT 'community';

-- Clear existing data if needed (uncomment if you want to start fresh)
-- TRUNCATE TABLE `locations`;

-- Insert Communities (Main Communities) - type: 'community', parent_id: NULL
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(1, 'BEDII', 'community', NULL, 'active'),
(2, 'ABOANIDUA', 'community', NULL, 'active'),
(3, 'NTRENTRESO', 'community', NULL, 'active'),
(4, 'AKOTI', 'community', NULL, 'active'),
(5, 'PABOASE', 'community', NULL, 'active'),
(6, 'APPIAHKROM', 'community', NULL, 'active'),
(7, 'AHWIAA', 'community', NULL, 'active'),
(8, 'FUTA', 'community', NULL, 'active'),
(9, 'PENAKROM', 'community', NULL, 'active'),
(10, 'NYAMEBEKYERE', 'community', NULL, 'active'),
(11, 'BOSOMOISO', 'community', NULL, 'active'),
(12, 'AFEREE', 'community', NULL, 'active'),
(13, 'ABODUAM', 'community', NULL, 'active'),
(14, 'EWIASE-NGAKAIN', 'community', NULL, 'active'),
(15, 'AMAFIE', 'community', NULL, 'active'),
(16, 'ABOBOYAA', 'community', NULL, 'active'),
(17, 'WIAWSO', 'community', NULL, 'active'),
(18, 'KESSEKROM', 'community', NULL, 'active'),
(19, 'AHWIAM', 'community', NULL, 'active'),
(20, 'MPOMAM', 'community', NULL, 'active'),
(21, 'DWENASE', 'community', NULL, 'active'),
(22, 'NSUONSUA', 'community', NULL, 'active'),
(23, 'TANOSO', 'community', NULL, 'active'),
(24, 'DATANO', 'community', NULL, 'active'),
(25, 'AHOKWAA', 'community', NULL, 'active'),
(26, 'DOMEABRA', 'community', NULL, 'active'),
(27, 'KETEBOI', 'community', NULL, 'active'),
(28, 'PUNIKROM', 'community', NULL, 'active'),
(29, 'CAMP', 'community', NULL, 'active'),
(30, 'ANYINABIRIM', 'community', NULL, 'active'),
(31, 'ABRABRA', 'community', NULL, 'active'),
(32, 'NKONYA', 'community', NULL, 'active'),
(33, 'ATTORKROM', 'community', NULL, 'active'),
(34, 'SUI', 'community', NULL, 'active'),
(35, 'BOAKO', 'community', NULL, 'active'),
(36, 'AKURAFU', 'community', NULL, 'active'),
(37, 'GYAMPOKROM', 'community', NULL, 'active'),
(38, 'AMPABAME', 'community', NULL, 'active'),
(39, 'ABOAGYEKROM', 'community', NULL, 'active'),
(40, 'BBB', 'community', NULL, 'active'),
(41, 'KUNUMA', 'community', NULL, 'active'),
(42, 'AFRIMKROM', 'community', NULL, 'active'),
(43, 'AKYEAKYEN', 'community', NULL, 'active'),
(44, 'KANKYEABO', 'community', NULL, 'active'),
(45, 'A', 'community', NULL, 'active'),
(46, 'ASAFO', 'community', NULL, 'active'),
(47, 'ASAWINSO', 'community', NULL, 'active'),
(48, 'KOJINA', 'community', NULL, 'active'),
(49, 'ESSAKROM', 'community', NULL, 'active');

-- Insert Suburbs for BEDII (parent_id: 1)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(50, 'AGONA SAFO', 'suburb', 1, 'active'),
(51, 'OLD BEDII', 'suburb', 1, 'active');

-- Smaller Communities (treated as 'community') without specific parent - under main communities
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(52, 'LARWEHKROM', 'community', 5, 'active');

-- Cottages for LARWEHKROM (parent_id: 52)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(53, 'PROJECT', 'suburb', 52, 'active'),
(54, 'KWAME ANINIKROM', 'suburb', 52, 'active');

-- Cottage for AHWIAA (parent_id: 7)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(55, 'KYEREBOSO', 'suburb', 7, 'active');

-- Smaller Community ALOMUM (under NYAMEBEKYERE - 10)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(56, 'ALOMUM', 'community', 10, 'active');

-- Smaller Community DWENEWOHO (under AFEREE - 12)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(57, 'DWENEWOHO', 'community', 12, 'active');

-- Cottage for ABODUAM (parent_id: 13)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(58, 'BETEKYE', 'suburb', 13, 'active');

-- Suburbs for WIAWSO (parent_id: 17)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(59, 'GARDEN', 'suburb', 17, 'active'),
(60, 'OLD ADIEMBRA', 'suburb', 17, 'active'),
(61, 'NEW ADIEMBRA', 'suburb', 17, 'active'),
(62, 'SUSUMINITE3', 'suburb', 17, 'active'),
(63, 'NEWTOWN', 'suburb', 17, 'active'),
(64, 'SOMAAKROM', 'suburb', 17, 'active'),
(65, 'ABUOTEM', 'suburb', 17, 'active'),
(66, 'KWASIPATABO', 'suburb', 17, 'active'),
(67, 'ASAMAN', 'suburb', 17, 'active'),
(68, 'SOUTH AFRICA', 'suburb', 17, 'active'),
(69, 'AKAALOMBO', 'suburb', 17, 'active'),
(70, 'ASIKAFUO AMMANTEM', 'suburb', 17, 'active'),
(71, 'MEMPE-ASEM', 'suburb', 17, 'active'),
(72, 'ZONGO', 'suburb', 17, 'active'),
(73, 'LOWCOST', 'suburb', 17, 'active');

-- Cottages for AHWIAM (parent_id: 19)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(74, 'KWAADUKROM', 'suburb', 19, 'active'),
(75, 'NO.2 (BOAMAKROM)', 'suburb', 19, 'active');

-- Smaller Community GYATOKROM (under AHWIAM - 19)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(76, 'GYATOKROM', 'community', 19, 'active');

-- Suburbs for DWENASE (parent_id: 21)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(77, 'ATEKYEM', 'suburb', 21, 'active'),
(78, 'AKURASE', 'suburb', 21, 'active'),
(79, 'EIGHTEEN', 'suburb', 21, 'active'),
(80, 'ZONGO', 'suburb', 21, 'active'),
(81, 'NEW SITE', 'suburb', 21, 'active'),
(82, 'COMPOUND', 'suburb', 21, 'active'),
(83, 'KOKOKRO', 'suburb', 21, 'active'),
(84, 'NSUEKRO', 'suburb', 21, 'active'),
(85, 'KUZENSO', 'suburb', 21, 'active');

-- Smaller Communities under NSUONSUA (parent_id: 22)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(86, 'ANGLO', 'community', 22, 'active'),
(87, 'BAAKONGA', 'community', 22, 'active'),
(88, 'ENSONYAMEYE', 'community', 22, 'active'),
(89, 'KWAMEBUO', 'community', 22, 'active'),
(90, 'KROBOM', 'community', 22, 'active'),
(91, 'AYILEKRO', 'community', 22, 'active');

-- Cottage for KWAMEBUO (parent_id: 89)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(92, 'KWASI ARMAKROM', 'suburb', 89, 'active');

-- Cottages for AYILEKRO (parent_id: 91)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(93, 'ADAMU', 'suburb', 91, 'active'),
(94, 'ABOPROE', 'suburb', 91, 'active');

-- Suburbs for TANOSO (parent_id: 23)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(95, 'AHODWO', 'suburb', 23, 'active'),
(96, 'DIVISION', 'suburb', 23, 'active'),
(97, 'EFO JUNCTION', 'suburb', 23, 'active');

-- Smaller Communities under TANOSO (parent_id: 23)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(98, 'FUAKYEKROM', 'community', 23, 'active'),
(99, 'NYAMEGYESO', 'community', 23, 'active');

-- Cottages for FUAKYEKROM (parent_id: 98)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(100, 'ESONEBIAGYA', 'suburb', 98, 'active'),
(101, 'MEMPE-ASEM', 'suburb', 98, 'active'),
(102, 'KWADWO ANTO', 'suburb', 98, 'active');

-- Suburb for DATANO (parent_id: 24)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(103, 'KUSASI-LINE', 'suburb', 24, 'active');

-- Smaller Community SUHENSO (under AHOKWAA - 25)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(104, 'SUHENSO', 'community', 25, 'active');

-- Cottage for DOMEABRA (parent_id: 26)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(105, 'OWUO ABOROSO', 'suburb', 26, 'active');

-- Smaller Communities under DOMEABRA (parent_id: 26)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(106, 'BOWOBRA', 'community', 26, 'active'),
(107, 'KYEAMEKRO', 'community', 26, 'active'),
(108, 'APENTEMADI', 'community', 26, 'active'),
(109, 'NYAME NNAE', 'community', 26, 'active'),
(110, 'SWANZY', 'community', 26, 'active'),
(111, 'PIASE', 'community', 26, 'active');

-- Cottages for NYAME NNAE (parent_id: 109)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(112, 'OBIA NTOBI', 'suburb', 109, 'active'),
(113, 'OSEI MPAMBRO', 'suburb', 109, 'active'),
(114, 'KRAMO TENTEN', 'suburb', 109, 'active'),
(115, 'NYAME NNAI ZONGO', 'suburb', 109, 'active');

-- Cottages for SWANZY (parent_id: 110)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(116, 'MORGAN', 'suburb', 110, 'active'),
(117, 'ADIYAAKRO', 'suburb', 110, 'active'),
(118, 'SHED', 'suburb', 110, 'active'),
(119, 'AMADU KROM', 'suburb', 110, 'active'),
(120, 'TUPA', 'suburb', 110, 'active');

-- Cottage for PIASE (parent_id: 111)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(121, 'MATAPOLI', 'suburb', 111, 'active');

-- Cottages for KETEBOI (parent_id: 27)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(122, 'EMIESO', 'suburb', 27, 'active'),
(123, 'GYAASEHENE', 'suburb', 27, 'active'),
(124, 'KAINA-C', 'suburb', 27, 'active'),
(125, 'GARIBAKRO', 'suburb', 27, 'active');

-- Cottages for PUNIKROM (parent_id: 28)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(126, 'ADOWA NKWANTA', 'suburb', 28, 'active'),
(127, 'ADUHENEKROM', 'suburb', 28, 'active'),
(128, 'ADENKYIN', 'suburb', 28, 'active');

-- Smaller Community KRAMOKRO (under PUNIKROM - 28)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(129, 'KRAMOKRO', 'community', 28, 'active');

-- Cottage for KRAMOKRO (parent_id: 129)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(130, 'AGYA AKUFO', 'suburb', 129, 'active');

-- Cottages for CAMP (parent_id: 29)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(131, 'KOFIKROM', 'suburb', 29, 'active'),
(132, 'BOMODEN', 'suburb', 29, 'active'),
(133, 'BREDI', 'suburb', 29, 'active'),
(134, 'ASANTEKROM', 'suburb', 29, 'active'),
(135, 'POWERKROM', 'suburb', 29, 'active'),
(136, 'AKUAPIM NKWANTA', 'suburb', 29, 'active'),
(137, 'BANGROMESAA', 'suburb', 29, 'active');

-- Suburb for ANYINABIRIM (parent_id: 30)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(138, 'GYIDI', 'suburb', 30, 'active');

-- Cottage for ANYINABIRIM (parent_id: 30)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(139, 'KOFORIDUA', 'suburb', 30, 'active');

-- Smaller Communities under ANYINABIRIM (parent_id: 30)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(140, 'MILE 2', 'community', 30, 'active'),
(141, 'MILE-3', 'community', 30, 'active'),
(142, 'MILE-4', 'community', 30, 'active'),
(143, 'MILE-5', 'community', 30, 'active');

-- Cottages for MILE-3 (parent_id: 141)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(144, 'MILE-3 FOREST', 'suburb', 141, 'active'),
(145, 'GYAAKYE', 'suburb', 141, 'active');

-- Cottage for MILE-4 (parent_id: 142)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(146, 'MILE-4 FOREST', 'suburb', 142, 'active');

-- Cottages for MILE-5 (parent_id: 143)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(147, 'ASUOGYA', 'suburb', 143, 'active'),
(148, 'KOO NSIA', 'suburb', 143, 'active');

-- Cottages for ABRABRA (parent_id: 31)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(149, 'NYAME NNAE-ABRABRA', 'suburb', 31, 'active'),
(150, 'ABRABRA FOREST', 'suburb', 31, 'active'),
(151, 'KUMIKROM', 'suburb', 31, 'active');

-- Smaller Communities under NKONYA (parent_id: 32)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(152, 'PEWODIE', 'community', 32, 'active'),
(153, 'SUI NKWANTA', 'community', 32, 'active'),
(154, 'ATTA CAMP', 'community', 32, 'active');

-- Cottage for PEWODIE (parent_id: 152)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(155, 'PAPA KWAME', 'suburb', 152, 'active');

-- Cottages for ATTA CAMP (parent_id: 154)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(156, 'MFUMKROM', 'suburb', 154, 'active'),
(157, 'KROBO LINE', 'suburb', 154, 'active');

-- Suburbs for BOAKO (parent_id: 35)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(158, 'BARRACKS', 'suburb', 35, 'active'),
(159, 'BUADAC', 'suburb', 35, 'active');

-- Cottage for BARRACKS (parent_id: 158)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(160, 'ASUOEBA', 'suburb', 158, 'active');

-- Smaller Community KWANANE (under BOAKO - 35)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(161, 'KWANANE', 'community', 35, 'active');

-- Cottages for KWANANE (parent_id: 161)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(162, 'ABODUAM', 'suburb', 161, 'active'),
(163, 'TEABANTE', 'suburb', 161, 'active'),
(164, 'GRUMAHENE', 'suburb', 161, 'active');

-- Cottages for AKURAFU (parent_id: 36)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(165, 'KWADWO HUNU', 'suburb', 36, 'active'),
(166, 'SHED', 'suburb', 36, 'active'),
(167, 'KUMORKRO', 'suburb', 36, 'active'),
(168, 'AMPROMBI', 'suburb', 36, 'active'),
(169, 'BOADUKROM', 'suburb', 36, 'active'),
(170, 'YAWKWATIAKRO', 'suburb', 36, 'active');

-- Smaller Communities under AKURAFU (parent_id: 36)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(171, 'FAWOMAN', 'community', 36, 'active'),
(172, 'NYETINA', 'community', 36, 'active');

-- Smaller Communities under GYAMPOKROM (parent_id: 37)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(173, 'ABONSE', 'community', 37, 'active'),
(174, 'BEKYIWA', 'community', 37, 'active');

-- Cottage for ABONSE (parent_id: 173)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(175, 'AWUTU', 'suburb', 173, 'active');

-- Cottages for BEKYIWA (parent_id: 174)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(176, 'POSTMASTER', 'suburb', 174, 'active'),
(177, 'MANGOASE', 'suburb', 174, 'active'),
(178, 'AMANO', 'suburb', 174, 'active'),
(179, 'OYOKO', 'suburb', 174, 'active'),
(180, 'ABOSO', 'suburb', 174, 'active'),
(181, 'ABIGYAN LINE', 'suburb', 174, 'active');

-- Smaller Communities under AMPABAME (parent_id: 38)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(182, 'AFADIKROM', 'community', 38, 'active'),
(183, 'LARBIKROM', 'community', 38, 'active'),
(184, 'MADINA', 'community', 38, 'active');

-- Cottages for ABOAGYEKROM (parent_id: 39)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(185, 'KWASI ADJEIKROM', 'suburb', 39, 'active'),
(186, 'GYAMPO', 'suburb', 39, 'active'),
(187, 'DAWOME', 'suburb', 39, 'active'),
(188, 'OJOBI', 'suburb', 39, 'active'),
(189, 'ASIEDUKRO', 'suburb', 39, 'active'),
(190, 'AMANKWAKRO', 'suburb', 39, 'active');

-- Smaller Communities under ABOAGYEKROM (parent_id: 39)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(191, 'BREKULINE', 'community', 39, 'active'),
(192, 'MENSAHLINE KWASIKRU', 'community', 39, 'active');

-- Cottages for MENSAHLINE KWASIKRU (parent_id: 192)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(193, 'KAE AWURADE', 'suburb', 192, 'active'),
(194, 'TUTUCAMP', 'suburb', 192, 'active');

-- Cottages for KUNUMA (parent_id: 41)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(195, 'NANA YARO', 'suburb', 41, 'active'),
(196, 'NANA HARUNA', 'suburb', 41, 'active');

-- Smaller Community SUI ANO (under KUNUMA - 41)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(197, 'SUI ANO', 'community', 41, 'active');

-- Cottages for KANKYEABO (parent_id: 44)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(198, 'BRE NYE KWA', 'suburb', 44, 'active'),
(199, 'ADUANE KYEN SIKA', 'suburb', 44, 'active');

-- Smaller Community ROME (under A - 45)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(200, 'ROME', 'community', 45, 'active');

-- Cottages for ASAFO (parent_id: 46)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(201, 'ANKAASE', 'suburb', 46, 'active'),
(202, 'KWAME DRUG STORE', 'suburb', 46, 'active');

-- Cottages for ASAWINSO (parent_id: 47)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(203, 'ZUGU LINE', 'suburb', 47, 'active'),
(204, 'GYATOKROM', 'suburb', 47, 'active');

-- Suburbs for ASAWINSO (parent_id: 47)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(205, 'ACKAAKROM', 'suburb', 47, 'active'),
(206, 'DE-BEAT', 'suburb', 47, 'active'),
(207, 'SEFWI LINE', 'suburb', 47, 'active'),
(208, 'BILI BOY', 'suburb', 47, 'active'),
(209, 'OBAATAASO', 'suburb', 47, 'active'),
(210, 'ZAB ZERO', 'suburb', 47, 'active'),
(211, 'MARKET AREA', 'suburb', 47, 'active'),
(212, 'MEMENEDA KOKOO', 'suburb', 47, 'active'),
(213, 'WANOPE ASEM', 'suburb', 47, 'active');

-- Cottage for KOJINA (parent_id: 48)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(214, 'ATHURKROM', 'suburb', 48, 'active');

-- Smaller Community AKWASI ADDAEKROM (under ESSAKROM - 49)
INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`, `status`) VALUES
(215, 'AKWASI ADDAEKROM', 'community', 49, 'active');

-- Set auto_increment for future inserts
ALTER TABLE `locations` AUTO_INCREMENT = 216;
