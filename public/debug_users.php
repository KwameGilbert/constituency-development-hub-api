<?php
// public/debug_users.php

// Define application paths
require_once __DIR__ . '/../src/config/Constants.php';

// Load Composer autoloader
require_once BASE . 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE);
$dotenv->load();

// Bootstrap Eloquent
require_once CONFIG . 'EloquentBootstrap.php';
$capsule = App\Config\EloquentBootstrap::boot();

use App\Models\User;

echo "--- User Debug Info ---\n";
echo "Total Users: " . User::count() . "\n";
echo "List of Users:\n";
$users = User::all();
foreach ($users as $user) {
    echo "ID: " . $user->id . " | Email: " . $user->email . " | Role: " . $user->role . " | Status: " . $user->status . "\n";
}

echo "\nChecking specific ID 15:\n";
$u = User::find(15);
if ($u) {
    echo "User 15 EXISTS.\n";
} else {
    echo "User 15 DOES NOT EXIST.\n";
}
