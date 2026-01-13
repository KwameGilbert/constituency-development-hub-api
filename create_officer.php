<?php
/**
 * Create Officer User Script
 * 
 * Run this script to create an officer user in the database.
 * Usage: php create_officer.php
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

// Officer details - customize as needed
$officerData = [
    'name' => 'Kwame Asante',
    'email' => 'kwame.asante@constituency.gov.gh',
    'phone' => '+233201234567',
    'password' => 'officer123', // Will be auto-hashed
    'role' => User::ROLE_OFFICER,
    'status' => User::STATUS_ACTIVE,
    'email_verified' => true,
];

try {
    // Check if user already exists
    $existingUser = User::where('email', $officerData['email'])->first();
    
    if ($existingUser) {
        echo "User with email {$officerData['email']} already exists with ID: {$existingUser->id}\n";
        
        // Check if officer profile exists
        $officer = Officer::where('user_id', $existingUser->id)->first();
        if ($officer) {
            echo "Officer profile exists with ID: {$officer->id}\n";
        } else {
            echo "Creating officer profile...\n";
            $officer = Officer::create([
                'user_id' => $existingUser->id,
                'employee_id' => Officer::generateEmployeeId(),
                'department' => 'Operations',
                'position' => 'Field Officer',
                'can_manage_projects' => true,
                'can_manage_reports' => true,
                'can_manage_events' => false,
                'can_publish_content' => false,
            ]);
            echo "Created officer profile with ID: {$officer->id}\n";
        }
    } else {
        // Create new user
        echo "Creating new officer user...\n";
        $user = User::create($officerData);
        echo "Created user with ID: {$user->id}\n";
        
        // Create officer profile
        $officer = Officer::create([
            'user_id' => $user->id,
            'employee_id' => Officer::generateEmployeeId(),
            'department' => 'Operations',
            'position' => 'Field Officer',
            'can_manage_projects' => true,
            'can_manage_reports' => true,
            'can_manage_events' => false,
            'can_publish_content' => false,
        ]);
        echo "Created officer profile with ID: {$officer->id}\n";
    }
    
    echo "\nDone! You can now log in with:\n";
    echo "Email: {$officerData['email']}\n";
    echo "Password: officer123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
