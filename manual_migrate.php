<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use App\Config\EloquentBootstrap;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Dotenv\Dotenv;

function logMsg($msg) {
    echo $msg . "\n";
    file_put_contents(__DIR__ . '/src/logs/migration_debug.log', $msg . "\n", FILE_APPEND);
}

logMsg("Starting migration script...");

try {
    require_once __DIR__ . '/src/config/Constants.php';
    logMsg("Loaded Constants.php");
    
    if (!defined('BASE')) {
        throw new Exception("BASE constant not defined");
    }

    require_once BASE . 'vendor/autoload.php';
    logMsg("Loaded Autoload");

    require_once CONFIG . 'EloquentBootstrap.php';
    logMsg("Loaded EloquentBootstrap");

    // Load environment variables
    $dotenv = Dotenv::createImmutable(BASE);
    $dotenv->load();
    logMsg("Loaded .env");

    // Boot Eloquent
    EloquentBootstrap::boot();
    logMsg("Booted Eloquent");

    // Run migrations
    if (!Capsule::schema()->hasColumn('community_ideas', 'downvotes')) {
        logMsg("Adding downvotes column to community_ideas...");
        Capsule::schema()->table('community_ideas', function (Blueprint $table) {
            $table->integer('downvotes')->default(0)->after('votes');
        });
        logMsg("Added downvotes column.");
    } else {
        logMsg("Column 'downvotes' already exists.");
    }

    if (!Capsule::schema()->hasColumn('community_idea_votes', 'type')) {
        logMsg("Adding type column to community_idea_votes...");
        Capsule::schema()->table('community_idea_votes', function (Blueprint $table) {
            $table->enum('type', ['up', 'down'])->default('up')->after('user_id');
        });
        logMsg("Added type column.");
    } else {
        logMsg("Column 'type' already exists.");
    }

    logMsg("Migration completed successfully.");

} catch (Throwable $e) {
    logMsg("FATAL ERROR: " . $e->getMessage());
    logMsg($e->getTraceAsString());
}
