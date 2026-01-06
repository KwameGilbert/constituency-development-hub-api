<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Add Admin Dashboard Tables Migration
 * 
 * This migration creates tables for admin dashboard features:
 * - announcements (public announcements and notices)
 * - employment_jobs (job listings and opportunities)
 * - community_ideas (community-submitted ideas and proposals)
 */
final class AddAdminDashboardTables extends AbstractMigration
{
    public function up(): void
    {
        // =====================================================
        // 1. ANNOUNCEMENTS TABLE (Public announcements and notices)
        // =====================================================
        if (!$this->hasTable('announcements')) {
            $this->table('announcements', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('content', 'text', ['null' => false])
                ->addColumn('category', 'enum', [
                    'values' => ['general', 'events', 'infrastructure', 'health', 'education', 'emergency', 'other'],
                    'default' => 'general',
                    'null' => false
                ])
                ->addColumn('priority', 'enum', [
                    'values' => ['low', 'medium', 'high', 'urgent'],
                    'default' => 'medium',
                    'null' => false
                ])
                ->addColumn('status', 'enum', [
                    'values' => ['draft', 'published', 'archived'],
                    'default' => 'draft',
                    'null' => false
                ])
                ->addColumn('publish_date', 'date', ['null' => true])
                ->addColumn('expiry_date', 'date', ['null' => true])
                ->addColumn('image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('attachment', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('views', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('is_pinned', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('published_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['category'])
                ->addIndex(['priority'])
                ->addIndex(['status'])
                ->addIndex(['publish_date'])
                ->addIndex(['expiry_date'])
                ->addIndex(['is_pinned'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 2. EMPLOYMENT JOBS TABLE (Job listings and opportunities)
        // =====================================================
        if (!$this->hasTable('employment_jobs')) {
            $this->table('employment_jobs', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => false])
                ->addColumn('company', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Organization or department offering the job'])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('job_type', 'enum', [
                    'values' => ['full_time', 'part_time', 'contract', 'internship', 'temporary', 'volunteer'],
                    'default' => 'full_time',
                    'null' => false
                ])
                ->addColumn('salary_range', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('salary_min', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
                ->addColumn('salary_max', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true])
                ->addColumn('requirements', 'text', ['null' => true])
                ->addColumn('responsibilities', 'text', ['null' => true])
                ->addColumn('benefits', 'text', ['null' => true])
                ->addColumn('application_deadline', 'date', ['null' => true])
                ->addColumn('application_url', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('application_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('contact_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['draft', 'published', 'closed', 'archived'],
                    'default' => 'draft',
                    'null' => false
                ])
                ->addColumn('category', 'enum', [
                    'values' => ['administration', 'technical', 'health', 'education', 'social_services', 'finance', 'communications', 'monitoring_evaluation', 'other'],
                    'default' => 'other',
                    'null' => false
                ])
                ->addColumn('experience_level', 'enum', [
                    'values' => ['entry', 'mid', 'senior', 'executive'],
                    'default' => 'entry',
                    'null' => false
                ])
                ->addColumn('applicants_count', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('views', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('published_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['status'])
                ->addIndex(['category'])
                ->addIndex(['job_type'])
                ->addIndex(['experience_level'])
                ->addIndex(['application_deadline'])
                ->addIndex(['is_featured'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 3. COMMUNITY IDEAS TABLE (Community-submitted ideas and proposals)
        // =====================================================
        if (!$this->hasTable('community_ideas')) {
            $this->table('community_ideas', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => false])
                ->addColumn('category', 'enum', [
                    'values' => ['infrastructure', 'education', 'healthcare', 'environment', 'social', 'economic', 'governance', 'other'],
                    'default' => 'other',
                    'null' => false
                ])
                ->addColumn('submitter_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('submitter_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('submitter_contact', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('submitter_user_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'If submitted by a registered user'])
                ->addColumn('status', 'enum', [
                    'values' => ['pending', 'under_review', 'approved', 'rejected', 'implemented'],
                    'default' => 'pending',
                    'null' => false
                ])
                ->addColumn('priority', 'enum', [
                    'values' => ['low', 'medium', 'high'],
                    'default' => 'medium',
                    'null' => false
                ])
                ->addColumn('votes', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
                ->addColumn('estimated_cost', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('estimated_cost_min', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true])
                ->addColumn('estimated_cost_max', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('target_beneficiaries', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('implementation_timeline', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('images', 'json', ['null' => true])
                ->addColumn('documents', 'json', ['null' => true])
                ->addColumn('admin_notes', 'text', ['null' => true])
                ->addColumn('reviewed_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('reviewed_at', 'timestamp', ['null' => true])
                ->addColumn('implemented_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['status'])
                ->addIndex(['category'])
                ->addIndex(['priority'])
                ->addIndex(['votes'])
                ->addIndex(['submitter_email'])
                ->addForeignKey('submitter_user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('reviewed_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 4. IDEA VOTES TABLE (Track votes on community ideas)
        // =====================================================
        if (!$this->hasTable('community_idea_votes')) {
            $this->table('community_idea_votes', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('idea_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('voter_ip', 'string', ['limit' => 45, 'null' => true])
                ->addColumn('voter_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['idea_id', 'user_id'], ['unique' => true, 'name' => 'unique_user_vote'])
                ->addIndex(['idea_id', 'voter_ip'], ['name' => 'unique_ip_vote'])
                ->addForeignKey('idea_id', 'community_ideas', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order
        $tables = [
            'community_idea_votes',
            'community_ideas',
            'employment_jobs',
            'announcements',
        ];

        foreach ($tables as $tableName) {
            if ($this->hasTable($tableName)) {
                $this->table($tableName)->drop()->save();
            }
        }
    }
}
