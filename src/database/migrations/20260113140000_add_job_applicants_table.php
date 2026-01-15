<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddJobApplicantsTable extends AbstractMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create job_applicants table
        if (!$this->hasTable('job_applicants')) {
            $table = $this->table('job_applicants');
            $table
                ->addColumn('job_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('resume_url', 'string', ['limit' => 500, 'null' => true])
                ->addColumn('cover_letter', 'text', ['null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['pending', 'reviewed', 'shortlisted', 'rejected', 'accepted'],
                    'default' => 'pending',
                    'null' => false
                ])
                ->addColumn('applied_at', 'datetime', ['null' => true])
                ->addColumn('created_at', 'datetime', ['null' => true])
                ->addColumn('updated_at', 'datetime', ['null' => true])
                ->addIndex(['job_id'])
                ->addIndex(['email'])
                ->addIndex(['status'])
                ->addIndex(['applied_at'])
                ->addForeignKey('job_id', 'employment_jobs', 'id', [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE'
                ])
                ->create();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->hasTable('job_applicants')) {
            $this->table('job_applicants')->drop()->save();
        }
    }
}
