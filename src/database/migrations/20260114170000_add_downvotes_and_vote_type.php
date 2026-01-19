<?php

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add downvotes to community_ideas and type to community_idea_votes
 */
class AddDownvotesAndVoteType extends AbstractMigration
{
    public function up()
    {
        // Add downvotes column to community_ideas
        $table = $this->table('community_ideas');
        if (!$table->hasColumn('downvotes')) {
            $table->addColumn('downvotes', 'integer', ['default' => 0, 'after' => 'votes'])
                  ->update();
        }

        // Add type column to community_idea_votes
        $tableVotes = $this->table('community_idea_votes');
        if (!$tableVotes->hasColumn('type')) {
            $tableVotes->addColumn('type', 'enum', ['values' => ['up', 'down'], 'default' => 'up', 'after' => 'user_id'])
                       ->update();
        }
    }

    public function down()
    {
        // Remove columns
        $table = $this->table('community_ideas');
        if ($table->hasColumn('downvotes')) {
            $table->removeColumn('downvotes')
                  ->save();
        }

        $tableVotes = $this->table('community_idea_votes');
        if ($tableVotes->hasColumn('type')) {
            $tableVotes->removeColumn('type')
                       ->save();
        }
    }
}
