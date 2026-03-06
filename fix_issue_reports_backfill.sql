-- One-time backfill for legacy issue records that are missing classification
-- and constituent detail fields used by dashboard issue detail views.
--
-- Safe usage:
-- 1) Backup DB first.
-- 2) Run in staging.
-- 3) Run this script in production during a low-traffic window.

START TRANSACTION;

-- 1) Backfill constituent fields from legacy reporter_* columns.
UPDATE issue_reports
SET
  constituent_name = COALESCE(NULLIF(constituent_name, ''), NULLIF(reporter_name, '')),
  constituent_email = COALESCE(NULLIF(constituent_email, ''), NULLIF(reporter_email, '')),
  constituent_contact = COALESCE(NULLIF(constituent_contact, ''), NULLIF(reporter_phone, ''))
WHERE
  (constituent_name IS NULL OR constituent_name = '' OR
   constituent_email IS NULL OR constituent_email = '' OR
   constituent_contact IS NULL OR constituent_contact = '');

-- 2) Keep legacy reporter_* fields in sync when only constituent_* exists.
UPDATE issue_reports
SET
  reporter_name = COALESCE(NULLIF(reporter_name, ''), NULLIF(constituent_name, '')),
  reporter_email = COALESCE(NULLIF(reporter_email, ''), NULLIF(constituent_email, '')),
  reporter_phone = COALESCE(NULLIF(reporter_phone, ''), NULLIF(constituent_contact, ''))
WHERE
  (reporter_name IS NULL OR reporter_name = '' OR
   reporter_email IS NULL OR reporter_email = '' OR
   reporter_phone IS NULL OR reporter_phone = '');

-- 3) Backfill gender from legacy "Gender: ..." appended in description.
UPDATE issue_reports
SET
  constituent_gender = CASE
    WHEN (constituent_gender IS NULL OR constituent_gender = '')
         AND description LIKE '%Gender:%'
    THEN TRIM(
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(REPLACE(REPLACE(description, '\r', ''), '<br>', '\n'), 'Gender: ', -1),
        '\n',
        1
      )
    )
    ELSE constituent_gender
  END
WHERE constituent_gender IS NULL OR constituent_gender = '';

-- 4) Backfill address from legacy "Address: ..." appended in description.
UPDATE issue_reports
SET
  constituent_address = CASE
    WHEN (constituent_address IS NULL OR constituent_address = '')
         AND description LIKE '%Address:%'
    THEN TRIM(
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(REPLACE(REPLACE(description, '\r', ''), '<br>', '\n'), 'Address: ', -1),
        '\n',
        1
      )
    )
    ELSE constituent_address
  END
WHERE constituent_address IS NULL OR constituent_address = '';

-- 5) Backfill issue_type from legacy "Issue Type: ..." appended in description.
UPDATE issue_reports
SET
  issue_type = CASE
    WHEN (issue_type IS NULL OR issue_type = '')
         AND description LIKE '%Issue Type:%'
    THEN LOWER(
      REPLACE(
        TRIM(
          SUBSTRING_INDEX(
            SUBSTRING_INDEX(REPLACE(REPLACE(description, '\r', ''), '<br>', '\n'), 'Issue Type: ', -1),
            '\n',
            1
          )
        ),
        '-',
        '_'
      )
    )
    ELSE issue_type
  END
WHERE issue_type IS NULL OR issue_type = '';

-- 6) Backfill affected_people_count from legacy "People Affected: ..." in description.
UPDATE issue_reports
SET
  affected_people_count = CASE
    WHEN (affected_people_count IS NULL OR affected_people_count = 0)
         AND description LIKE '%People Affected:%'
    THEN CAST(
      REPLACE(
        TRIM(
          SUBSTRING_INDEX(
            SUBSTRING_INDEX(REPLACE(REPLACE(description, '\r', ''), '<br>', '\n'), 'People Affected: ', -1),
            '\n',
            1
          )
        ),
        ',',
        ''
      ) AS UNSIGNED
    )
    ELSE affected_people_count
  END
WHERE affected_people_count IS NULL OR affected_people_count = 0;

-- 7) Backfill sector_id from legacy "Sector: ..." in description.
UPDATE issue_reports ir
JOIN sectors s
  ON LOWER(TRIM(s.name)) = LOWER(
    TRIM(
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(REPLACE(REPLACE(ir.description, '\r', ''), '<br>', '\n'), 'Sector: ', -1),
        '\n',
        1
      )
    )
  )
SET ir.sector_id = s.id
WHERE
  (ir.sector_id IS NULL OR ir.sector_id = 0)
  AND ir.description LIKE '%Sector:%';

-- 8) Backfill sub_sector_id from legacy "Subsector: ..." in description.
UPDATE issue_reports ir
JOIN sub_sectors ss
  ON LOWER(TRIM(ss.name)) = LOWER(
    TRIM(
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(REPLACE(REPLACE(ir.description, '\r', ''), '<br>', '\n'), 'Subsector: ', -1),
        '\n',
        1
      )
    )
  )
  AND ss.sector_id = ir.sector_id
SET ir.sub_sector_id = ss.id
WHERE
  ir.sector_id IS NOT NULL
  AND (ir.sub_sector_id IS NULL OR ir.sub_sector_id = 0)
  AND ir.description LIKE '%Subsector:%';

COMMIT;

-- Optional verification queries:
-- SELECT COUNT(*) AS missing_sector FROM issue_reports WHERE sector_id IS NULL;
-- SELECT COUNT(*) AS missing_sub_sector FROM issue_reports WHERE sub_sector_id IS NULL;
-- SELECT COUNT(*) AS missing_gender FROM issue_reports WHERE constituent_gender IS NULL OR constituent_gender = '';
-- SELECT COUNT(*) AS missing_address FROM issue_reports WHERE constituent_address IS NULL OR constituent_address = '';
