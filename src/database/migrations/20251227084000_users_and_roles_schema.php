<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Users and Roles Schema Migration
 * 
 * This migration creates the user management system with role-based access.
 * 
 * Tables created:
 * - users (base user table with authentication)
 * - web_admins (super admins and platform administrators)
 * - officers (constituency officers handling reports and projects)
 * - agents (field agents for community engagement)
 * - password_resets (password reset tokens)
 * - refresh_tokens (JWT refresh tokens)
 */
final class UsersAndRolesSchema extends AbstractMigration
{
    public function up(): void
    {
        // =====================================================
        // 1. USERS TABLE (Base authentication table)
        // =====================================================
        if (!$this->hasTable('users')) {
            $this->table('users', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('role', 'enum', [
                    'values' => ['web_admin', 'officer', 'agent','task_force'],
                    'null' => false
                ])
                ->addColumn('email_verified', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('email_verified_at', 'timestamp', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'suspended', 'pending'],
                    'default' => 'pending',
                    'null' => false
                ])
                ->addColumn('first_login', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('last_login_at', 'timestamp', ['null' => true])
                ->addColumn('last_login_ip', 'string', ['limit' => 45, 'null' => true])
                ->addColumn('remember_token', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['email'], ['unique' => true])
                ->addIndex(['phone'])
                ->addIndex(['role'])
                ->addIndex(['status'])
                ->create();
        }

        // =====================================================
        // 2. WEB ADMINS TABLE (Super admins and platform administrators)
        // =====================================================
        if (!$this->hasTable('web_admins')) {
            $this->table('web_admins', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('employee_id', 'string', ['limit' => 50, 'null' => true, 'comment' => 'Staff ID'])
                ->addColumn('admin_level', 'enum', [
                    'values' => ['super_admin', 'admin', 'moderator'],
                    'default' => 'admin',
                    'null' => false
                ])
                ->addColumn('department', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('permissions', 'json', ['null' => true, 'comment' => 'Specific permissions override'])
                ->addColumn('profile_image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('notes', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'], ['unique' => true])
                ->addIndex(['employee_id'], ['unique' => true])
                ->addIndex(['admin_level'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 3. OFFICERS TABLE (Constituency officers)
        // =====================================================
        if (!$this->hasTable('officers')) {
            $this->table('officers', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('employee_id', 'string', ['limit' => 50, 'null' => true, 'comment' => 'Staff ID'])
                ->addColumn('title', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Job title'])
                ->addColumn('department', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('assigned_sectors', 'json', ['null' => true, 'comment' => 'Array of sector IDs they manage'])
                ->addColumn('assigned_locations', 'json', ['null' => true, 'comment' => 'Array of locations they cover'])
                ->addColumn('can_manage_projects', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_manage_reports', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_manage_events', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('can_publish_content', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('profile_image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('office_location', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('office_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'], ['unique' => true])
                ->addIndex(['employee_id'], ['unique' => true])
                ->addIndex(['department'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 4. AGENTS TABLE (Field agents for community engagement)
        // =====================================================
        if (!$this->hasTable('agents')) {
            $this->table('agents', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('agent_code', 'string', ['limit' => 50, 'null' => true, 'comment' => 'Unique agent identifier'])
                ->addColumn('supervisor_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'Officer supervising this agent'])
                ->addColumn('assigned_communities', 'json', ['null' => true, 'comment' => 'Array of community names/IDs'])
                ->addColumn('assigned_location', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Primary area of operation'])
                ->addColumn('can_submit_reports', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_collect_data', 'boolean', ['default' => true, 'null' => false])
                ->addColumn('can_register_residents', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('profile_image', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('id_type', 'enum', [
                    'values' => ['ghana_card', 'voter_id', 'passport', 'drivers_license'],
                    'null' => true
                ])
                ->addColumn('id_number', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('id_verified', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('id_verified_at', 'timestamp', ['null' => true])
                ->addColumn('address', 'text', ['null' => true])
                ->addColumn('emergency_contact_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('emergency_contact_phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('reports_submitted', 'integer', ['default' => 0, 'null' => false])
                ->addColumn('last_active_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'], ['unique' => true])
                ->addIndex(['agent_code'], ['unique' => true])
                ->addIndex(['supervisor_id'])
                ->addIndex(['assigned_location'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addForeignKey('supervisor_id', 'officers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 5. PASSWORD RESETS TABLE
        // =====================================================
        if (!$this->hasTable('password_resets')) {
            $this->table('password_resets', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('expires_at', 'timestamp', ['null' => false])
                ->addColumn('used', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('used_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['email', 'token'], ['name' => 'password_resets_email_token'])
                ->addIndex(['expires_at'])
                ->create();
        }

        // =====================================================
        // 6. REFRESH TOKENS TABLE (JWT refresh tokens)
        // =====================================================
        if (!$this->hasTable('refresh_tokens')) {
            $this->table('refresh_tokens', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('token_hash', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('device_name', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                ->addColumn('user_agent', 'text', ['null' => true])
                ->addColumn('expires_at', 'timestamp', ['null' => false])
                ->addColumn('revoked', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('revoked_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['token_hash'], ['unique' => true])
                ->addIndex(['user_id'])
                ->addIndex(['expires_at'])
                ->addIndex(['revoked'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 7. AUDIT LOGS TABLE (Track user actions)
        // =====================================================
        if (!$this->hasTable('audit_logs')) {
            $this->table('audit_logs', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('action', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('entity_type', 'string', ['limit' => 100, 'null' => true, 'comment' => 'e.g., project, report, event'])
                ->addColumn('entity_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('old_values', 'json', ['null' => true])
                ->addColumn('new_values', 'json', ['null' => true])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
                ->addColumn('user_agent', 'text', ['null' => true])
                ->addColumn('metadata', 'json', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addIndex(['action'])
                ->addIndex(['entity_type', 'entity_id'])
                ->addIndex(['created_at'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->create();
        }

        // =====================================================
        // 8. EMAIL VERIFICATION TOKENS TABLE
        // =====================================================
        if (!$this->hasTable('email_verification_tokens')) {
            $this->table('email_verification_tokens', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('expires_at', 'timestamp', ['null' => false])
                ->addColumn('used', 'boolean', ['default' => false, 'null' => false])
                ->addColumn('used_at', 'timestamp', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['user_id'])
                ->addIndex(['token'])
                ->addIndex(['expires_at'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->create();
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order (respecting foreign key constraints)
        $tables = [
            'email_verification_tokens',
            'audit_logs',
            'refresh_tokens',
            'password_resets',
            'agents',
            'officers',
            'web_admins',
            'users',
        ];

        foreach ($tables as $tableName) {
            if ($this->hasTable($tableName)) {
                $this->table($tableName)->drop()->save();
            }
        }
    }
}
