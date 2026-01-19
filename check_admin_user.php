<?php

/**
 * Quick database check script to verify admin user and role
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\EloquentBootstrap;
use Illuminate\Database\Capsule\Manager as DB;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Eloquent
EloquentBootstrap::boot();

echo "=== Checking Database ===\n\n";

try {
    // Check if we can connect
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n\n";
    
    // Check users table
    echo "--- Users with web_admin role ---\n";
    $webAdmins = DB::table('users')
        ->where('role', 'web_admin')
        ->get(['id', 'email', 'role', 'status']);
    
    if ($webAdmins->count() > 0) {
        foreach ($webAdmins as $user) {
            echo "  ID: {$user->id}, Email: {$user->email}, Status: {$user->status}\n";
        }
    } else {
        echo "  ⚠ No users with 'web_admin' role found!\n";
    }
    
    echo "\n--- All users in database ---\n";
    $allUsers = DB::table('users')
        ->get(['id', 'email', 'role', 'status']);
    
    foreach ($allUsers as $user) {
        echo "  ID: {$user->id}, Email: {$user->email}, Role: {$user->role}, Status: {$user->status}\n";
    }
    
    echo "\n--- User with ID 15 (from JWT token) ---\n";
    $user15 = DB::table('users')
        ->where('id', 15)
        ->first();
    
    if ($user15) {
        echo "  ID: {$user15->id}\n";
        echo "  Email: {$user15->email}\n";
        echo "  Role: {$user15->role}\n";
        echo "  Status: {$user15->status}\n";
    } else {
        echo "  ⚠ User with ID 15 not found in database!\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Check ===\n";
