<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Add User Relationships to Content and Issues
 * 
 * This migration adds foreign key relationships to track:
 * - Web Admins: who created/updated blog posts, events, hero slides, FAQs, etc.
 * - Officers: who is assigned to handle issue reports
 * - Agents: who submitted issue reports from the field
 */
final class AddUserRelationships extends AbstractMigration
{
    public function up(): void
    {
        // =====================================================
        // 1. BLOG POSTS - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('blog_posts')) {
            $this->table('blog_posts')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 2. CONSTITUENCY EVENTS - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('constituency_events')) {
            $this->table('constituency_events')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 3. HERO SLIDES - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('hero_slides')) {
            $this->table('hero_slides')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 4. FAQs - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('faqs')) {
            $this->table('faqs')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 5. COMMUNITY STATS - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('community_stats')) {
            $this->table('community_stats')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 6. CONTACT INFO - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('contact_info')) {
            $this->table('contact_info')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 7. SECTORS - Add created_by (web_admin)
        // =====================================================
        if ($this->hasTable('sectors')) {
            $this->table('sectors')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 8. PROJECTS - Add created_by (web_admin) and managing_officer
        // =====================================================
        if ($this->hasTable('projects')) {
            $this->table('projects')
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'id'])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'created_by'])
                ->addColumn('managing_officer_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'updated_by', 'comment' => 'Officer overseeing the project'])
                ->addIndex(['created_by'])
                ->addIndex(['updated_by'])
                ->addIndex(['managing_officer_id'])
                ->addForeignKey('created_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('updated_by', 'web_admins', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('managing_officer_id', 'officers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 9. ISSUE REPORTS - Update with officer/agent relationships
        // =====================================================
        if ($this->hasTable('issue_reports')) {
            $this->table('issue_reports')
                // Remove the old string-based assigned_to and add proper FKs
                ->removeColumn('assigned_to')
                ->addColumn('submitted_by_agent_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'reporter_phone', 'comment' => 'Agent who submitted this report'])
                ->addColumn('assigned_officer_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'submitted_by_agent_id', 'comment' => 'Officer assigned to handle this issue'])
                ->addColumn('assigned_agent_id', 'integer', ['null' => true, 'signed' => false, 'after' => 'assigned_officer_id', 'comment' => 'Agent assigned for field work'])
                ->addColumn('acknowledged_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'acknowledged_at', 'comment' => 'Officer who acknowledged'])
                ->addColumn('resolved_by', 'integer', ['null' => true, 'signed' => false, 'after' => 'resolved_at', 'comment' => 'Officer/Agent who resolved'])
                ->addIndex(['submitted_by_agent_id'])
                ->addIndex(['assigned_officer_id'])
                ->addIndex(['assigned_agent_id'])
                ->addIndex(['acknowledged_by'])
                ->addIndex(['resolved_by'])
                ->addForeignKey('submitted_by_agent_id', 'agents', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('assigned_officer_id', 'officers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('assigned_agent_id', 'agents', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('acknowledged_by', 'officers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addForeignKey('resolved_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->update();
        }

        // =====================================================
        // 10. ISSUE REPORT COMMENTS TABLE (For officer/agent communication)
        // =====================================================
        if (!$this->hasTable('issue_report_comments')) {
            $this->table('issue_report_comments', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('issue_report_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false, 'comment' => 'Officer or Agent who commented'])
                ->addColumn('comment', 'text', ['null' => false])
                ->addColumn('is_internal', 'boolean', ['default' => true, 'null' => false, 'comment' => 'If true, only visible to staff'])
                ->addColumn('attachments', 'json', ['null' => true, 'comment' => 'Array of file URLs'])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['issue_report_id'])
                ->addIndex(['user_id'])
                ->addIndex(['is_internal'])
                ->addForeignKey('issue_report_id', 'issue_reports', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 11. ISSUE REPORT STATUS HISTORY TABLE (Track status changes)
        // =====================================================
        if (!$this->hasTable('issue_report_status_history')) {
            $this->table('issue_report_status_history', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('issue_report_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('changed_by', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('old_status', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('new_status', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('notes', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['issue_report_id'])
                ->addIndex(['changed_by'])
                ->addIndex(['new_status'])
                ->addForeignKey('issue_report_id', 'issue_reports', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('changed_by', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop new tables
        if ($this->hasTable('issue_report_status_history')) {
            $this->table('issue_report_status_history')->drop()->save();
        }

        if ($this->hasTable('issue_report_comments')) {
            $this->table('issue_report_comments')->drop()->save();
        }

        // Remove added columns from existing tables (reverse order)
        $tablesWithCreatedBy = [
            'blog_posts',
            'constituency_events',
            'hero_slides',
            'faqs',
            'community_stats',
            'contact_info',
            'sectors',
        ];

        foreach ($tablesWithCreatedBy as $tableName) {
            if ($this->hasTable($tableName)) {
                $table = $this->table($tableName);
                if ($table->hasColumn('created_by')) {
                    $table->removeColumn('created_by')->update();
                }
                if ($table->hasColumn('updated_by')) {
                    $table->removeColumn('updated_by')->update();
                }
            }
        }

        // Projects - remove additional columns
        if ($this->hasTable('projects')) {
            $table = $this->table('projects');
            if ($table->hasColumn('created_by')) {
                $table->removeColumn('created_by')->update();
            }
            if ($table->hasColumn('updated_by')) {
                $table->removeColumn('updated_by')->update();
            }
            if ($table->hasColumn('managing_officer_id')) {
                $table->removeColumn('managing_officer_id')->update();
            }
        }

        // Issue reports - restore original structure
        if ($this->hasTable('issue_reports')) {
            $table = $this->table('issue_reports');
            
            // Remove new columns
            $columnsToRemove = [
                'submitted_by_agent_id',
                'assigned_officer_id',
                'assigned_agent_id',
                'acknowledged_by',
                'resolved_by',
            ];
            
            foreach ($columnsToRemove as $column) {
                if ($table->hasColumn($column)) {
                    $table->removeColumn($column);
                }
            }
            
            // Add back the original assigned_to column
            $table->addColumn('assigned_to', 'string', ['limit' => 255, 'null' => true])
                  ->update();
        }
    }
}
