<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateEmploymentJobsCategoryAndSector extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('employment_jobs');
        
        // 1. Change 'category' from ENUM to VARCHAR(255)
        $table->changeColumn('category', 'string', [
            'limit' => 255, 
            'null' => false, 
            'default' => 'Other',
            'comment' => 'Job category (e.g. Health Services)'
        ])->save();
              
        // 2. Add 'sector' column
        if (!$table->hasColumn('sector')) {
            $table->addColumn('sector', 'string', [
                'limit' => 255, 
                'null' => true, 
                'after' => 'category', 
                'comment' => 'Specific sector (e.g. Nursing)'
            ]);
        }
        
        // 3. Add Beneficiary Information
        if (!$table->hasColumn('beneficiary_name')) {
            $table->addColumn('beneficiary_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'sector',
                'comment' => 'Name of the beneficiary/candidate'
            ]);
        }
        
        if (!$table->hasColumn('beneficiary_gender')) {
            $table->addColumn('beneficiary_gender', 'enum', [
                'values' => ['male', 'female', 'other'],
                'null' => true,
                'after' => 'beneficiary_name',
                'comment' => 'Gender of the beneficiary'
            ]);
        }
        
        // Ensure standard contact fields exist/are compatible (contact_phone, application_email already exist)
        
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('employment_jobs');
        
        if ($table->hasColumn('beneficiary_gender')) {
            $table->removeColumn('beneficiary_gender');
        }
        
        if ($table->hasColumn('beneficiary_name')) {
            $table->removeColumn('beneficiary_name');
        }
        
        if ($table->hasColumn('sector')) {
            $table->removeColumn('sector');
        }
        
        $table->save();
    }
}
