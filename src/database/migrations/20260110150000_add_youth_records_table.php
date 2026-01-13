<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add Youth Records Table
 * 
 * Creates the youth_records table for storing individual youth registration records
 */
class AddYouthRecordsTable extends AbstractMigration
{
    public function change(): void
    {
        // ====================
        // YOUTH RECORDS TABLE
        // ====================
        $this->table('youth_records')
            // Personal Information
            ->addColumn('full_name', 'string', ['limit' => 200])
            ->addColumn('date_of_birth', 'date', ['null' => true])
            ->addColumn('gender', 'enum', [
                'values' => ['male', 'female'],
                'null' => true
            ])
            ->addColumn('national_id', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('hometown', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('community', 'string', ['limit' => 100, 'null' => true])
            
            // Location reference
            ->addColumn('location_id', 'integer', ['null' => true, 'signed' => false])
            
            // Educational Qualifications
            ->addColumn('education_level', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('jhs_completed', 'boolean', ['default' => false])
            ->addColumn('shs_qualification', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('certificate_qualification', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('diploma_qualification', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('degree_qualification', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('postgraduate_qualification', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('professional_qualification', 'string', ['limit' => 200, 'null' => true])
            
            // Employment Information
            ->addColumn('employment_status', 'enum', [
                'values' => ['employed', 'unemployed', 'student', 'self_employed'],
                'default' => 'unemployed'
            ])
            ->addColumn('availability_status', 'enum', [
                'values' => ['available', 'unavailable'],
                'default' => 'available'
            ])
            ->addColumn('current_employment', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('preferred_location', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('salary_expectation', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('employment_notes', 'text', ['null' => true])
            
            // Work Experience (JSON array)
            ->addColumn('work_experiences', 'json', ['null' => true])
            
            // Skills and Interests
            ->addColumn('skills', 'text', ['null' => true])
            ->addColumn('interests', 'text', ['null' => true])
            
            // Administrative
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'approved', 'rejected'],
                'default' => 'pending'
            ])
            ->addColumn('admin_notes', 'text', ['null' => true])
            ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
            
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            
            // Indexes
            ->addIndex(['status'])
            ->addIndex(['employment_status'])
            ->addIndex(['location_id'])
            ->addIndex(['created_at'])
            ->addIndex(['full_name', 'phone'])
            
            // Foreign keys
            ->addForeignKey('location_id', 'locations', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('created_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}
