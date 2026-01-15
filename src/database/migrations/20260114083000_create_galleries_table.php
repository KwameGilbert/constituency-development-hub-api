<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Create Galleries Table Migration
 */
final class CreateGalleriesTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('galleries')) {
            $this->table('galleries', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('category', 'string', ['limit' => 50, 'null' => false])
                ->addColumn('date', 'date', ['null' => false])
                ->addColumn('location', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('cover_image', 'string', ['limit' => 500, 'null' => false])
                ->addColumn('images', 'json', ['null' => true, 'comment' => 'Array of image objects {url, caption}'])
                ->addColumn('status', 'enum', [
                    'values' => ['active', 'inactive'],
                    'default' => 'active',
                    'null' => false
                ])
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('updated_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => true])
                ->addIndex(['slug'], ['unique' => true])
                ->addIndex(['category'])
                ->addIndex(['status'])
                ->addIndex(['date'])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('galleries')) {
            $this->table('galleries')->drop()->save();
        }
    }
}
