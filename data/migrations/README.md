# Database Migration Guide

## Overview
This guide explains how to apply the database schema updates for the Community Development Hub system.

## Prerequisites
- Database backup completed
- MySQL/MariaDB access credentials
- Command-line access to the server

## Migration Files

1. **001_extend_issue_reports_schema.sql** - Core schema extensions
   - Adds missing status states
   - Adds classification fields (sector, issue type, affected people)
   - Adds structured location hierarchy
   - Adds constituent information fields
   - Adds review tracking

2. **002_create_officers_table.sql** - Officers management
   - Creates officers table
   - Adds community assignment system
   - Supports both JSON and junction table approaches

3. **003_create_sectors_tables.sql** - Classification system
   - Creates sectors and sub_sectors tables
   - Includes default Ghana-specific sectors
   - Links to issue_reports via foreign keys

## Migration Steps

### Step 1: Backup Database

```bash
# Create backup
mysqldump -u root -p const_dev_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup
ls -lh backup_*.sql
```

### Step 2: Review Migration Files

```bash
cd c:\Users\AnthonyAfriyie\Desktop\projects\constituency-development-hub-api\data\migrations

# Review each file
cat 001_extend_issue_reports_schema.sql
cat 002_create_officers_table.sql
cat 003_create_sectors_tables.sql
```

### Step 3: Apply Migrations

#### Option A: Using MySQL Command Line (Recommended)

```bash
# Navigate to api directory
cd c:\Users\AnthonyAfriyie\Desktop\projects\constituency-development-hub-api

# Apply migrations in order
mysql -u root -p const_dev_db < data/migrations/001_extend_issue_reports_schema.sql
mysql -u root -p const_dev_db < data/migrations/002_create_officers_table.sql
mysql -u root -p const_dev_db < data/migrations/003_create_sectors_tables.sql
```

#### Option B: Using Phinx (If configured)

```bash
# Check phinx configuration
cat phinx.php

# Run migrations
vendor/bin/phinx migrate
```

#### Option C: Using phpMyAdmin or Database GUI

1. Open phpMyAdmin
2. Select `const_dev_db` database
3. Go to "SQL" tab
4. Copy and paste contents of each migration file
5. Execute in order (001, 002, 003)

### Step 4: Verify Migration

```bash
# Check table structure
mysql -u root -p const_dev_db -e "DESCRIBE issue_reports;"
mysql -u root -p const_dev_db -e "DESCRIBE officers;"
mysql -u root -p const_dev_db -e "DESCRIBE sectors;"
mysql -u root -p const_dev_db -e "DESCRIBE sub_sectors;"

# Check data
mysql -u root -p const_dev_db -e "SELECT COUNT(*) FROM sectors;"
mysql -u root -p const_dev_db -e "SELECT COUNT(*) FROM sub_sectors;"
```

Expected output:
- `issue_reports` should have new columns: `sector_id`, `issue_type`, `constituent_gender`, etc.
- `officers` table should exist
- `sectors` table should have 11 rows
- `sub_sectors` table should have 40+ rows

## Post-Migration Tasks

### 1. Update Backend Models

The Eloquent models need to be updated to reflect the new schema:

**File**: `src/models/IssueReport.php`

```php
// Add to fillable array
protected $fillable = [
    // ... existing fields ...
    'sector_id',
    'sub_sector_id',
    'issue_type',
    'affected_people_count',
    'main_community_id',
    'smaller_community_id',
    'suburb_id',
    'cottage_id',
    'constituent_gender',
    'constituent_address',
    'reviewed_by_officer_id',
    'reviewed_at',
    'assessment_reviewed_by',
    'assessment_reviewed_at',
    'assessment_decision',
];

// Add relationships
public function sector() {
    return $this->belongsTo(Sector::class);
}

public function subSector() {
    return $this->belongsTo(SubSector::class);
}

public function mainCommunity() {
    return $this->belongsTo(Location::class, 'main_community_id');
}

public function reviewedByOfficer() {
    return $this->belongsTo(Officer::class, 'reviewed_by_officer_id');
}
```

### 2. Create New Model Files

Create these new model files in `src/models/`:

- `Officer.php`
- `Sector.php`
- `SubSector.php`

### 3. Update API Controllers

Update controllers to handle new fields:
- `IssueReportController.php` - Accept new fields in create/update
- `OfficerController.php` - New controller for officer management
- `SectorController.php` - New controller for sector management

### 4. Update Frontend

- Update issue submission forms to include:
  - Sector and sub-sector dropdowns
  - Issue type selection (community vs individual)
  - Affected people count field
  - Constituent gender field
  - Structured location selectors (hierarchical dropdowns)
  
- Update issue listing/filtering to support new classifications

### 5. Data Migration (Optional)

If you have existing data, you may want to migrate:

```sql
-- Migrate old location VARCHAR to structured fields
-- Example: Update based on location name patterns

UPDATE issue_reports 
SET main_community_id = (
    SELECT id FROM locations 
    WHERE name = issue_reports.location 
    AND type = 'community' 
    LIMIT 1
)
WHERE main_community_id IS NULL AND location IS NOT NULL;

-- Set default issue type for existing records
UPDATE issue_reports 
SET issue_type = 'community_based' 
WHERE issue_type IS NULL;
```

## Rollback Instructions

If you need to rollback the migrations, use the rollback script:

```bash
mysql -u root -p const_dev_db < data/migrations/rollback_all_migrations.sql
```

Then restore from backup:

```bash
mysql -u root -p const_dev_db < backup_YYYYMMDD_HHMMSS.sql
```

## Troubleshooting

### Error: Foreign key constraint fails

**Cause**: Referenced table doesn't exist or has no matching records

**Solution**:
```sql
-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;
-- Run migration
-- Re-enable checks
SET FOREIGN_KEY_CHECKS = 1;
```

### Error: Duplicate column name

**Cause**: Column already exists (migration ran partially before)

**Solution**: Check which columns exist and modify migration to skip existing ones

```sql
-- Check existing columns
SHOW COLUMNS FROM issue_reports LIKE '%sector%';
```

### Error: ENUM value too long

**Cause**: Status ENUM has too many values

**Solution**: Split into multiple migrations or use VARCHAR with validation in application

## Testing Checklist

After migration, test these scenarios:

- [ ] Create new issue with sector/sub-sector
- [ ] Create issue with structured location (main community + suburb)
- [ ] Submit issue with constituent information (including gender)
- [ ] Officer review workflow (mark as reviewed)
- [ ] Task Force assessment submission
- [ ] Admin resource allocation
- [ ] Filter issues by sector
- [ ] Filter officers by assigned community
- [ ] View sector statistics

## Support

If you encounter issues:

1. Check error logs: `data/migrations/migration_errors.log`
2. Review database error messages
3. Verify backup is intact before attempting rollback
4. Contact development team

---

**Migration Created**: January 17, 2026  
**Database Version**: 1.1 → 2.0  
**Estimated Duration**: 5-10 minutes
