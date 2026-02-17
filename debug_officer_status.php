<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize Eloquent
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'database'  => $_ENV['DB_NAME'] ?? 'constituency_hub',
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Debug Officer Script\n";
echo "--------------------\n";

// 1. Find the user
$userEmail = 'akosua.darko2@constituencyhub.com'; // Guessing the email based on name, or I'll search by name
$userName = 'Akosua Darko2';

$user = \App\Models\User::where('name', 'LIKE', "%$userName%")->first();

if (!$user) {
    echo "User '$userName' not found.\n";
    // List all users to see what's there
    $users = \App\Models\User::all();
    echo "Available Users:\n";
    foreach ($users as $u) {
        echo "- {$u->id}: {$u->name} ({$u->email}) [{$u->role}]\n";
    }
    exit;
}

echo "Found User: {$user->id} | {$user->name} | {$user->email} | Role: {$user->role}\n";

// 2. Check Officer Profile
$officer = \App\Models\Officer::where('user_id', $user->id)->first();

if ($officer) {
    echo "Officer Profile Found: ID {$officer->id}\n";
    echo "Assigned Communities: {$officer->assigned_communities}\n";
} else {
    echo "ERROR: No Officer profile found for user {$user->id}!\n";
    echo "This is likely why stats are failing.\n";
    
    // Auto-fix option?
    echo "Attempting to create officer profile...\n";
    try {
        $newOfficer = new \App\Models\Officer();
        $newOfficer->user_id = $user->id;
        $newOfficer->employee_id = \App\Models\Officer::generateEmployeeId();
        $newOfficer->title = 'Field Officer';
        $newOfficer->department = 'Operations';
        $newOfficer->office_phone = $user->phone ?? '0000000000';
        $newOfficer->office_location = 'Oforikrom, Ashanti';
        $newOfficer->can_manage_projects = true;
        $newOfficer->can_manage_reports = true;
        $newOfficer->save();
        echo "SUCCESS: Officer profile created with ID {$newOfficer->id} and Employee ID {$newOfficer->employee_id}\n";
    } catch (\Exception $e) {
        echo "FAILED to create officer profile: " . $e->getMessage() . "\n";
    }
}
