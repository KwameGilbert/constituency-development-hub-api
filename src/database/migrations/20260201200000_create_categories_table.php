<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoriesTable extends AbstractMigration
{
    public function up(): void
    {
        // Create categories table
        if (!$this->hasTable('categories')) {
            $this->table('categories', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('created_by', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('updated_by', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('icon', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('color', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('display_order', 'integer', ['default' => 0, 'signed' => false])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['status'])
                ->addIndex(['display_order'])
                ->create();
        }

        // Add category_id to sectors table
        $sectorsTable = $this->table('sectors');
        if (!$sectorsTable->hasColumn('category_id')) {
            $sectorsTable
                ->addColumn('category_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id'])
                ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
                ->addIndex(['category_id'])
                ->update();
        }
    }

    public function down(): void
    {
        // Remove category_id from sectors
        $sectorsTable = $this->table('sectors');
        if ($sectorsTable->hasColumn('category_id')) {
            if ($sectorsTable->hasForeignKey('category_id')) {
                $sectorsTable->dropForeignKey('category_id');
            }
            $sectorsTable->removeColumn('category_id')->update();
        }

        // Drop categories table
        if ($this->hasTable('categories')) {
            $this->table('categories')->drop()->save();
        }
    }
}
