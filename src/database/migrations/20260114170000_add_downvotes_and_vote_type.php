<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Migration: Add downvotes to community_ideas and type to community_idea_votes
 */
return new class {
    public function up()
    {
        // Add downvotes column to community_ideas
        if (!Capsule::schema()->hasColumn('community_ideas', 'downvotes')) {
            Capsule::schema()->table('community_ideas', function (Blueprint $table) {
                $table->integer('downvotes')->default(0)->after('votes');
            });
        }

        // Add type column to community_idea_votes
        if (!Capsule::schema()->hasColumn('community_idea_votes', 'type')) {
            Capsule::schema()->table('community_idea_votes', function (Blueprint $table) {
                $table->enum('type', ['up', 'down'])->default('up')->after('user_id');
            });
        }
    }

    public function down()
    {
        // Remove columns
        if (Capsule::schema()->hasColumn('community_ideas', 'downvotes')) {
            Capsule::schema()->table('community_ideas', function (Blueprint $table) {
                $table->dropColumn('downvotes');
            });
        }

        if (Capsule::schema()->hasColumn('community_idea_votes', 'type')) {
            Capsule::schema()->table('community_idea_votes', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
