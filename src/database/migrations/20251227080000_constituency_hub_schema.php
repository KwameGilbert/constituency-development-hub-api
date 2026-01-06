<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Constituency Development Hub Schema Migration
 * 
 * This migration creates the database schema for the Constituency Development Hub.
 * 
 * Tables created:
 * - sectors (project categories)
 * - projects (development projects)
 * - blog_posts (news and articles)
 * - events (upcoming community events)
 * - faqs (frequently asked questions)
 * - hero_slides (homepage carousel)
 * - community_stats (statistics display)
 * - contact_info (contact details)
 * - issue_reports (community issue reporting)
 */
final class ConstituencyHubSchema extends AbstractMigration
{
    public function up(): void
    {
        // =====================================================
        // 1. SECTORS TABLE (Education, Healthcare, Infrastructure, etc.)
        // =====================================================
        if (!$this->hasTable('sectors')) {
            $this->table('sectors', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('icon', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Icon class or image path'])
                ->addColumn('color', 'string', ['limit' => 20, 'null' => true, 'comment' => 'Hex color for UI'])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['status'])
                ->create();
        }

        // =====================================================
        // 2. PROJECTS TABLE (Development projects)
        // =====================================================
        if (!$this->hasTable('projects')) {
            $this->table('projects', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('sector_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['planning', 'ongoing', 'completed', 'on_hold', 'cancelled'],
                    'default' => 'planning',
                    'null' => false
                ])
                ->addColumn('start_date', 'date', ['null' => true])
                ->addColumn('end_date', 'date', ['null' => true])
                ->addColumn('budget', 'decimal', ['precision' => 15, 'scale' => 2, 'null' => true])
                ->addColumn('spent', 'decimal', ['precision' => 15, 'scale' => 2, 'default' => 0.00, 'null' => true])
                ->addColumn('progress_percent', 'integer', ['default' => 0, 'null' => false, 'comment' => '0-100'])
                ->addColumn('beneficiaries', 'integer', ['null' => true, 'comment' => 'Number of people benefiting'])
                ->addColumn('image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('gallery', 'json', ['null' => true, 'comment' => 'Array of image URLs'])
                ->addColumn('contractor', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('contact_person', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('contact_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('views', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['sector_id'])
                ->addIndex(['status'])
                ->addIndex(['is_featured'])
                ->addIndex(['location'])
                ->addForeignKey('sector_id', 'sectors', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 3. BLOG POSTS TABLE (News and articles)
        // =====================================================
        if (!$this->hasTable('blog_posts')) {
            $this->table('blog_posts', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('excerpt', 'text', ['null' => true])
                ->addColumn('content', 'text', ['null' => true])
                ->addColumn('image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('author', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('category', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('tags', 'json', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['draft', 'published', 'archived'],
                    'default' => 'draft',
                    'null' => false
                ])
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('views', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('published_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['status'])
                ->addIndex(['is_featured'])
                ->addIndex(['published_at'])
                ->create();
        }

        // =====================================================
        // 4. EVENTS TABLE (Community events)
        // =====================================================
        if (!$this->hasTable('constituency_events')) {
            $this->table('constituency_events', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('event_date', 'date', ['null' => false])
                ->addColumn('start_time', 'time', ['null' => true])
                ->addColumn('end_time', 'time', ['null' => true])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('venue_address', 'text', ['null' => true])
                ->addColumn('map_url', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('organizer', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('contact_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('contact_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['upcoming', 'ongoing', 'completed', 'cancelled', 'postponed'],
                    'default' => 'upcoming',
                    'null' => false
                ])
                ->addColumn('is_featured', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('max_attendees', 'integer', ['null' => true])
                ->addColumn('registration_required', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['event_date'])
                ->addIndex(['status'])
                ->addIndex(['is_featured'])
                ->create();
        }

        // =====================================================
        // 5. FAQs TABLE (Frequently asked questions)
        // =====================================================
        if (!$this->hasTable('faqs')) {
            $this->table('faqs', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('question', 'text', ['null' => false])
                ->addColumn('answer', 'text', ['null' => false])
                ->addColumn('category', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['category'])
                ->addIndex(['status'])
                ->create();
        }

        // =====================================================
        // 6. HERO SLIDES TABLE (Homepage carousel)
        // =====================================================
        if (!$this->hasTable('hero_slides')) {
            $this->table('hero_slides', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('subtitle', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('image', 'string', ['limit' => 500, 'null' => false])
                ->addColumn('cta_label', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Call to action button text'])
                ->addColumn('cta_link', 'string', ['limit' => 500, 'null' => true, 'comment' => 'Call to action URL'])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('starts_at', 'timestamp', ['null' => true, 'comment' => 'When slide becomes visible'])
                ->addColumn('ends_at', 'timestamp', ['null' => true, 'comment' => 'When slide stops being visible'])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['status'])
                ->addIndex(['display_order'])
                ->create();
        }

        // =====================================================
        // 7. COMMUNITY STATS TABLE (Statistics for display)
        // =====================================================
        if (!$this->hasTable('community_stats')) {
            $this->table('community_stats', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('label', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('value', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('icon', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['status'])
                ->create();
        }

        // =====================================================
        // 8. CONTACT INFO TABLE (Office contact details)
        // =====================================================
        if (!$this->hasTable('contact_info')) {
            $this->table('contact_info', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('type', 'enum', [
                    'values' => ['address', 'phone', 'email', 'social'],
                    'null' => false
                ])
                ->addColumn('label', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('value', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('icon', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('link', 'string', ['limit' => 500, 'null' => true, 'comment' => 'URL for social/email links'])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['type'])
                ->addIndex(['status'])
                ->create();
        }

        // =====================================================
        // 9. ISSUE REPORTS TABLE (Community issue reporting)
        // =====================================================
        if (!$this->hasTable('issue_reports')) {
            $this->table('issue_reports', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('case_id', 'string', ['limit' => 50, 'null' => false, 'comment' => 'Unique case reference'])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => false])
                ->addColumn('category', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 8, 'null' => true])
                ->addColumn('longitude', 'decimal', ['precision' => 11, 'scale' => 8, 'null' => true])
                ->addColumn('images', 'json', ['null' => true, 'comment' => 'Array of uploaded image URLs'])
                ->addColumn('reporter_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('reporter_email', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('reporter_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['submitted', 'acknowledged', 'in_progress', 'resolved', 'closed', 'rejected'],
                    'default' => 'submitted',
                    'null' => false
                ])
                ->addColumn('priority', 'enum', [
                    'values' => ['low', 'medium', 'high', 'urgent'],
                    'default' => 'medium',
                    'null' => false
                ])
                ->addColumn('assigned_to', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('resolution_notes', 'text', ['null' => true])
                ->addColumn('acknowledged_at', 'timestamp', ['null' => true])
                ->addColumn('resolved_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['case_id'], ['unique' => true])
                ->addIndex(['status'])
                ->addIndex(['priority'])
                ->addIndex(['category'])
                ->addIndex(['location'])
                ->addIndex(['created_at'])
                ->create();
        }

        // =====================================================
        // 10. NEWSLETTER SUBSCRIBERS TABLE
        // =====================================================
        if (!$this->hasTable('newsletter_subscribers')) {
            $this->table('newsletter_subscribers', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'unsubscribed'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('subscribed_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('unsubscribed_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['email'], ['unique' => true])
                ->addIndex(['status'])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order
        $tables = [
            'newsletter_subscribers',
            'issue_reports',
            'contact_info',
            'community_stats',
            'hero_slides',
            'faqs',
            'constituency_events',
            'blog_posts',
            'projects',
            'sectors',
        ];

        foreach ($tables as $tableName) {
            if ($this->hasTable($tableName)) {
                $this->table($tableName)->drop()->save();
            }
        }
    }
}
