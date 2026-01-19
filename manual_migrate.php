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

    // Update issue_reports status ENUM
    logMsg("Updating issue_reports status ENUM...");
    Capsule::connection()->statement("
        ALTER TABLE issue_reports MODIFY COLUMN status ENUM(
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
        ) NOT NULL DEFAULT 'submitted'
    ");
    logMsg("Updated issue_reports status ENUM successfully.");

    logMsg("Migration completed successfully.");

} catch (Throwable $e) {
    logMsg("FATAL ERROR: " . $e->getMessage());
    logMsg($e->getTraceAsString());
}
