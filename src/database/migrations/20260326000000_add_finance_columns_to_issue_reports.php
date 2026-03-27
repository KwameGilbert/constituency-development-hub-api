<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFinanceColumnsToIssueReports extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     */
    public function change(): void
    {
        $table = $this->table('issue_reports');

        // 1. Rename columns to match what the Eloquent Model expects
        // (Rename if old name exists AND new name doesn't)
        if ($table->hasColumn('contact_name') && !$table->hasColumn('reporter_name')) {
            $table->renameColumn('contact_name', 'reporter_name');
        }
        if ($table->hasColumn('contact_phone') && !$table->hasColumn('reporter_phone')) {
            $table->renameColumn('contact_phone', 'reporter_phone');
        }
        if ($table->hasColumn('report_code') && !$table->hasColumn('case_id')) {
            $table->renameColumn('report_code', 'case_id');
        }
        if ($table->hasColumn('location_name') && !$table->hasColumn('location')) {
            $table->renameColumn('location_name', 'location');
        }
        if ($table->hasColumn('severity') && !$table->hasColumn('priority')) {
            $table->renameColumn('severity', 'priority');
        }

        // 2. Add reporter_email if missing
        if (!$table->hasColumn('reporter_email')) {
            $table->addColumn('reporter_email', 'string', [
                'limit' => 255, 
                'null' => true, 
                'after' => $table->hasColumn('reporter_name') ? 'reporter_name' : 'description'
            ]);
        }

        // 3. Add missing finance columns
        if (!$table->hasColumn('allocated_budget')) {
            $table->addColumn('allocated_budget', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => true,
                'after' => 'status' 
            ]);
        }

        if (!$table->hasColumn('allocated_resources')) {
            $table->addColumn('allocated_resources', 'json', [
                'null' => true,
                'after' => 'allocated_budget'
            ]);
        }

        if (!$table->hasColumn('resolution_notes')) {
            $table->addColumn('resolution_notes', 'text', [
                'null' => true,
                'after' => 'allocated_resources'
            ]);
        }

        // 4. Add additional constituent columns used by the improved model
        if (!$table->hasColumn('constituent_name')) {
            $table->addColumn('constituent_name', 'string', [
                'limit' => 255, 
                'null' => true, 
                'after' => 'id' // Safest anchor
            ]);
        }
        if (!$table->hasColumn('constituent_email')) {
            $table->addColumn('constituent_email', 'string', ['limit' => 255, 'null' => true, 'after' => 'constituent_name']);
        }
        if (!$table->hasColumn('constituent_contact')) {
            $table->addColumn('constituent_contact', 'string', ['limit' => 50, 'null' => true, 'after' => 'constituent_email']);
        }

        $table->update();
    }
}
