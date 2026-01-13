<?php
/**
 * Debug User Lookup Script
 * 
 * Run this script to check what users exist in the database
 * and compare with the JWT token data.
 * Usage: php debug_user.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize database connection
$config = require __DIR__ . '/src/config/database.php';
(require __DIR__ . '/src/config/EloquentBootstrap.php')($config);

use App\Models\User;
use App\Models\Officer;

echo "=== Database Connection Info ===\n";
echo "DB Host: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB Name: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
echo "DB User: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n\n";

echo "=== All Users in Database ===\n";
$users = User::all();
echo "Total users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Role: {$user->role} | Status: {$user->status}\n";
}

echo "\n=== Officers in Database ===\n";
$officers = Officer::with('user')->get();
echo "Total officers: " . $officers->count() . "\n\n";

foreach ($officers as $officer) {
    $userName = $officer->user ? $officer->user->email : 'NO USER LINKED';
    echo "Officer ID: {$officer->id} | User ID: {$officer->user_id} | User Email: {$userName}\n";
}

echo "\n=== Looking for specific user ===\n";
$email = 'kwame.asante@constituency.gov.gh';
$user = User::where('email', $email)->first();
if ($user) {
    echo "Found user with email {$email}:\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Role: {$user->role}\n";
    echo "  Status: {$user->status}\n";
    
    // Try User::find with this ID
    $foundById = User::find($user->id);
    echo "\nUser::find({$user->id}) returns: " . ($foundById ? "YES (ID: {$foundById->id})" : "NULL") . "\n";
} else {
    echo "No user found with email: {$email}\n";
}

echo "\n=== Check from debug_log ===\n";
echo "The debug_log shows JWT token user ID 15\n";
$user15 = User::find(15);
echo "User::find(15) returns: " . ($user15 ? "User with email {$user15->email}" : "NULL - NO USER WITH ID 15") . "\n";
