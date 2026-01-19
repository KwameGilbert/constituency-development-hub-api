<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateIssueReportStatusEnum extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('issue_reports');
        $table->changeColumn('status', 'enum', [
            'values' => [
                'submitted',
                'under_officer_review',
                'forwarded_to_admin',
                'assigned_to_task_force',
                'assessment_in_progress',
                'assessment_submitted',
                'resources_allocated',
                'resolution_in_progress',
                'resolution_submitted',
                'resolved',
                'closed',
                'rejected'
            ],
            'default' => 'submitted',
            'null' => false
        ])->save();
    }

    public function down(): void
    {
        // Warning: This down migration might fail if there are records with new statuses
        // We revert to the original list of statuses
        $table = $this->table('issue_reports');
        $table->changeColumn('status', 'enum', [
            'values' => ['submitted', 'acknowledged', 'in_progress', 'resolved', 'closed', 'rejected'],
            'default' => 'submitted',
            'null' => false
        ])->save();
    }
}
