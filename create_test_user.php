<?php
/**
 * Create Test User Script
 *
 * Run this script to create a test user for JWT token generation.
 * Usage: php create_test_user.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize database connection
$config = require __DIR__ . '/src/config/database.php';
(require __DIR__ . '/src/config/EloquentBootstrap.php')($config);

use App\Models\User;
use App\Services\AuthService;

try {
    // Create a test officer user
    $userData = [
        'name' => 'Test Officer',
        'email' => 'officer@test.com',
        'phone' => '+233201234567',
        'password' => 'password123', // Will be auto-hashed
        'role' => User::ROLE_OFFICER,
        'status' => User::STATUS_ACTIVE,
        'email_verified' => true,
    ];

    // Check if user already exists
    $existingUser = User::where('email', $userData['email'])->first();

    if ($existingUser) {
        echo "User with email {$userData['email']} already exists with ID: {$existingUser->id}\n";
        $user = $existingUser;
    } else {
        // Create new user
        echo "Creating new test user...\n";
        $user = User::create($userData);
        echo "Created user with ID: {$user->id}\n";
    }

    // Generate JWT token for this user
    $authService = new AuthService();
    $userPayload = $authService->generateUserPayload($user);
    $accessToken = $authService->generateAccessToken($userPayload);

    echo "\n=== JWT TOKEN FOR TESTING ===\n";
    echo "User ID: {$user->id}\n";
    echo "User Email: {$user->email}\n";
    echo "User Role: {$user->role}\n";
    echo "JWT Token:\n";
    echo $accessToken . "\n";
    echo "\n=== COPY THIS TOKEN TO YOUR .env.local ===\n";
    echo "NEXT_PUBLIC_AUTH_TOKEN={$accessToken}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
