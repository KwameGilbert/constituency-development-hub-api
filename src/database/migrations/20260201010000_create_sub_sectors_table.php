<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSubSectorsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('sub_sectors')) {
            $this->table('sub_sectors', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('sector_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('code', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('icon', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('display_order', 'integer', ['default' => 0, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addForeignKey('sector_id', 'sectors', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->addIndex(['sector_id'])
                ->addIndex(['status'])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('sub_sectors')) {
            $this->table('sub_sectors')->drop()->save();
        }
    }
}
