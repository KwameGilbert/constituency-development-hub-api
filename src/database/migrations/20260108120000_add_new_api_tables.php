<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add Locations, Youth Programs, and Notifications tables
 * 
 * This migration creates the database tables for:
 * - Locations (constituency areas and zones)
 * - Youth Programs (program tracking with enrollment)
 * - Youth Program Participants (enrollment records)
 * - Notifications (user notification system)
 */
class AddNewApiTables extends AbstractMigration
{
    public function change(): void
    {
        // ====================
        // LOCATIONS TABLE
        // ====================
        $this->table('locations')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('type', 'enum', [
                'values' => ['community', 'suburb', 'cottage', 'smaller_community'],
                'default' => 'community'
            ])
            ->addColumn('parent_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('population', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('area_size', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true])
            ->addColumn('longitude', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['active', 'inactive'],
                'default' => 'active'
            ])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'])
            ->addIndex(['type'])
            ->addIndex(['parent_id'])
            ->addIndex(['status'])
            ->addForeignKey('parent_id', 'locations', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->create();

        // ====================
        // YOUTH PROGRAMS TABLE
        // ====================
        $this->table('youth_programs')
            ->addColumn('title', 'string', ['limit' => 200])
            ->addColumn('slug', 'string', ['limit' => 220])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('category', 'enum', [
                'values' => ['education', 'employment', 'entrepreneurship', 'skills_training', 'sports', 'arts_culture', 'technology', 'health', 'other'],
                'default' => 'other'
            ])
            ->addColumn('start_date', 'date', ['null' => true])
            ->addColumn('end_date', 'date', ['null' => true])
            ->addColumn('registration_deadline', 'date', ['null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['draft', 'upcoming', 'active', 'registration_closed', 'completed', 'cancelled'],
                'default' => 'draft'
            ])
            ->addColumn('max_participants', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('current_enrollment', 'integer', ['default' => 0, 'signed' => false])
            ->addColumn('location_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('venue', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('image_url', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('requirements', 'json', ['null' => true])
            ->addColumn('benefits', 'json', ['null' => true])
            ->addColumn('contact_email', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('contact_phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['category'])
            ->addIndex(['status'])
            ->addIndex(['start_date'])
            ->addIndex(['location_id'])
            ->addForeignKey('location_id', 'locations', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('created_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->create();

        // ====================
        // YOUTH PROGRAM PARTICIPANTS TABLE
        // ====================
        $this->table('youth_program_participants')
            ->addColumn('program_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('full_name', 'string', ['limit' => 100])
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('date_of_birth', 'date', ['null' => true])
            ->addColumn('gender', 'enum', [
                'values' => ['male', 'female', 'other'],
                'null' => true
            ])
            ->addColumn('address', 'text', ['null' => true])
            ->addColumn('emergency_contact_name', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('emergency_contact_phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'approved', 'rejected', 'withdrawn', 'completed'],
                'default' => 'pending'
            ])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('registered_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('completed_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['program_id'])
            ->addIndex(['user_id'])
            ->addIndex(['email'])
            ->addIndex(['status'])
            ->addIndex(['program_id', 'email'], ['unique' => true])
            ->addForeignKey('program_id', 'youth_programs', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->create();

        // ====================
        // NOTIFICATIONS TABLE
        // ====================
        $this->table('notifications')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('type', 'enum', [
                'values' => ['info', 'success', 'warning', 'error', 'issue', 'project', 'announcement', 'assignment', 'system'],
                'default' => 'info'
            ])
            ->addColumn('title', 'string', ['limit' => 200])
            ->addColumn('message', 'text')
            ->addColumn('action_url', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('action_text', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('data', 'json', ['null' => true])
            ->addColumn('is_read', 'boolean', ['default' => false])
            ->addColumn('read_at', 'timestamp', ['null' => true])
            ->addColumn('expires_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id'])
            ->addIndex(['type'])
            ->addIndex(['is_read'])
            ->addIndex(['created_at'])
            ->addIndex(['user_id', 'is_read'])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}
