<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSubSectorIdToIssueReports extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('issue_reports');
        
        if (!$table->hasColumn('sub_sector_id')) {
            $table->addColumn('sub_sector_id', 'integer', [
                'signed' => false,
                'null' => true
            ])
            ->addForeignKey('sub_sector_id', 'sub_sectors', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE'
            ])
            ->addIndex(['sub_sector_id'])
            ->save();
        }
    }

    public function down(): void
    {
        $table = $this->table('issue_reports');
        
        if ($table->hasColumn('sub_sector_id')) {
            $table->dropForeignKey('sub_sector_id')
                  ->removeColumn('sub_sector_id')
                  ->save();
        }
    }
}
