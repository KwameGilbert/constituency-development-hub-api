<?php

require_once __DIR__ . '/src/config/Constants.php';
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap the application (initializes Eloquent)
$app = require_once __DIR__ . '/src/bootstrap/app.php';

use App\Models\User;
use App\Models\WebAdmin;
use Illuminate\Database\Capsule\Manager as Capsule;

echo "--- Create Web Admin User ---\n";

try {
    // 1. Create User
    $email = 'webadmin@example.com';
    $password = 'password123';
    
    // Check if user exists
    $existing = User::where('email', $email)->first();
    if ($existing) {
        echo "User with email {$email} already exists. Deleting to recreate...\n";
        $existing->delete(); // This should cascade delete the profile due to DB constraints
    }

    echo "Creating user...\n";
    $user = User::create([
        'name' => 'Web Administrator',
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'role' => 'web_admin',
        'status' => 'active',
        'email_verified' => true,
        'email_verified_at' => date('Y-m-d H:i:s'),
    ]);

    echo "User created (ID: {$user->id})\n";

    // 2. Create WebAdmin Profile
    echo "Creating WebAdmin profile...\n";
    
    // Generate an employee ID
    $employeeId = 'WEB-ADM-' . time();
    
    $webAdmin = WebAdmin::create([
        'user_id' => $user->id,
        'employee_id' => $employeeId,
        'admin_level' => 'admin', // Using 'admin' as ENUM allowed values are super_admin, admin, moderator
        'department' => 'Web Administration',
        'notes' => 'Created via script',
    ]);

    echo "WebAdmin profile created (ID: {$webAdmin->id})\n";
    echo "------------------------------------------------\n";
    echo "SUCCESS! Login credentials:\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
    echo "------------------------------------------------\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
