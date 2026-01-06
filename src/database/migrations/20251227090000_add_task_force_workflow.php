<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add Task Force and Issue Workflow Tables
 * 
 * Creates:
 * - task_force_members: Resolution Task Force profiles
 * - issue_assessment_reports: Assessment reports from Task Force
 * - issue_resolution_reports: Resolution reports from Task Force
 * 
 * Updates:
 * - issue_reports: Add new workflow columns
 */
class AddTaskForceWorkflow extends AbstractMigration
{
    public function up(): void
    {
        // Create task_force_members table
        if (!$this->hasTable('task_force_members')) {
            $this->table('task_force_members', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('employee_id', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('title', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('specialization', 'enum', [
                    'values' => ['infrastructure', 'health', 'education', 'water_sanitation', 'electricity', 'roads', 'general'],
                    'default' => 'general',
                    'null' => false
                ])
                ->addColumn('assigned_sectors', 'json', ['null' => true])
                ->addColumn('skills', 'json', ['null' => true])
                ->addColumn('can_assess_issues', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_resolve_issues', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_request_resources', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('profile_image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('id_type', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('id_number', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('id_verified', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('id_verified_at', 'timestamp', ['null' => true])
                ->addColumn('address', 'text', ['null' => true])
                ->addColumn('emergency_contact_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('emergency_contact_phone', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('assessments_completed', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('resolutions_completed', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('last_active_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addIndex(['user_id'], ['unique' => true])
                ->addIndex(['employee_id'], ['unique' => true])
                ->addIndex(['specialization'])
                ->addIndex(['id_verified'])
                ->create();
        }

        // Create issue_assessment_reports table
        if (!$this->hasTable('issue_assessment_reports')) {
            $this->table('issue_assessment_reports', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('issue_report_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('submitted_by', 'integer', ['null' => false, 'signed' => false, 'comment' => 'task_force_members.id'])
                ->addColumn('assessment_summary', 'text', ['null' => false])
                ->addColumn('findings', 'text', ['null' => true])
                ->addColumn('issue_confirmed', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('severity', 'enum', [
                    'values' => ['low', 'medium', 'high', 'critical'],
                    'default' => 'medium',
                    'null' => false
                ])
                ->addColumn('estimated_cost', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
                ->addColumn('estimated_duration', 'string', ['limit' => 100, 'null' => true, 'comment' => 'e.g., 2 weeks, 3 days'])
                ->addColumn('required_resources', 'json', ['null' => true])
                ->addColumn('images', 'json', ['null' => true])
                ->addColumn('documents', 'json', ['null' => true])
                ->addColumn('location_verified', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('gps_coordinates', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('recommendations', 'text', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'needs_revision'],
                    'default' => 'draft',
                    'null' => false
                ])
                ->addColumn('reviewed_at', 'timestamp', ['null' => true])
                ->addColumn('reviewed_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('review_notes', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addForeignKey('issue_report_id', 'issue_reports', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('submitted_by', 'task_force_members', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('reviewed_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addIndex(['issue_report_id'])
                ->addIndex(['submitted_by'])
                ->addIndex(['status'])
                ->create();
        }

        // Create issue_resolution_reports table
        if (!$this->hasTable('issue_resolution_reports')) {
            $this->table('issue_resolution_reports', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('issue_report_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('submitted_by', 'integer', ['null' => false, 'signed' => false, 'comment' => 'task_force_members.id'])
                ->addColumn('resolution_summary', 'text', ['null' => false])
                ->addColumn('work_description', 'text', ['null' => true])
                ->addColumn('start_date', 'date', ['null' => true])
                ->addColumn('completion_date', 'date', ['null' => true])
                ->addColumn('actual_cost', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
                ->addColumn('resources_used', 'json', ['null' => true])
                ->addColumn('before_images', 'json', ['null' => true])
                ->addColumn('after_images', 'json', ['null' => true])
                ->addColumn('documents', 'json', ['null' => true])
                ->addColumn('challenges_faced', 'text', ['null' => true])
                ->addColumn('additional_notes', 'text', ['null' => true])
                ->addColumn('requires_followup', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('followup_notes', 'text', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'needs_revision'],
                    'default' => 'draft',
                    'null' => false
                ])
                ->addColumn('reviewed_at', 'timestamp', ['null' => true])
                ->addColumn('reviewed_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('review_notes', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addForeignKey('issue_report_id', 'issue_reports', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('submitted_by', 'task_force_members', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('reviewed_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addIndex(['issue_report_id'])
                ->addIndex(['submitted_by'])
                ->addIndex(['status'])
                ->create();
        }

        // Update issue_reports table with new workflow columns
        $issueReports = $this->table('issue_reports');
        
        if (!$issueReports->hasColumn('submitted_by_officer_id')) {
            $issueReports->addColumn('submitted_by_officer_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'submitted_by_agent_id']);
        }
        if (!$issueReports->hasColumn('assigned_task_force_id')) {
            $issueReports->addColumn('assigned_task_force_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'assigned_officer_id']);
        }
        if (!$issueReports->hasColumn('allocated_budget')) {
            $issueReports->addColumn('allocated_budget', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true, 'after' => 'priority']);
        }
        if (!$issueReports->hasColumn('allocated_resources')) {
            $issueReports->addColumn('allocated_resources', 'json', ['null' => true, 'after' => 'allocated_budget']);
        }
        if (!$issueReports->hasColumn('forwarded_to_admin_at')) {
            $issueReports->addColumn('forwarded_to_admin_at', 'timestamp', ['null' => true, 'after' => 'acknowledged_by']);
        }
        if (!$issueReports->hasColumn('assigned_to_task_force_at')) {
            $issueReports->addColumn('assigned_to_task_force_at', 'timestamp', ['null' => true, 'after' => 'forwarded_to_admin_at']);
        }
        if (!$issueReports->hasColumn('resources_allocated_at')) {
            $issueReports->addColumn('resources_allocated_at', 'timestamp', ['null' => true, 'after' => 'assigned_to_task_force_at']);
        }
        if (!$issueReports->hasColumn('resources_allocated_by')) {
            $issueReports->addColumn('resources_allocated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'resources_allocated_at']);
        }
        
        $issueReports->save();

        // Add foreign keys for new columns
        if ($this->hasTable('issue_reports')) {
            $this->execute("ALTER TABLE issue_reports ADD CONSTRAINT fk_issue_reports_task_force 
                FOREIGN KEY (assigned_task_force_id) REFERENCES task_force_members(id) 
                ON DELETE SET NULL ON UPDATE CASCADE");
        }
    }

    public function down(): void
    {
        // Remove foreign key
        if ($this->hasTable('issue_reports')) {
            $this->execute("ALTER TABLE issue_reports DROP FOREIGN KEY IF EXISTS fk_issue_reports_task_force");
        }

        // Drop tables
        $tables = ['issue_resolution_reports', 'issue_assessment_reports', 'task_force_members'];
        foreach ($tables as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }

        // Remove columns from issue_reports
        $issueReports = $this->table('issue_reports');
        $columnsToRemove = [
            'submitted_by_officer_id', 'assigned_task_force_id', 'allocated_budget', 
            'allocated_resources', 'forwarded_to_admin_at', 'assigned_to_task_force_at',
            'resources_allocated_at', 'resources_allocated_by'
        ];
        foreach ($columnsToRemove as $column) {
            if ($issueReports->hasColumn($column)) {
                $issueReports->removeColumn($column);
            }
        }
        $issueReports->save();
    }
}
