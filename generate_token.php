<?php

/**
 * Generate fresh JWT token for existing admin user
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret = $_ENV['JWT_SECRET'];
$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
$issuer = $_ENV['JWT_ISSUER'] ?? 'eventic-api';
$expiry = $_ENV['JWT_EXPIRE'] ?? 99999999999999999;

// User data from database check (user ID 3)
$userData = [
    'id' => 3,
    'email' => 'john.mensah@constituency.gov.gh',
    'role' => 'web_admin',
    'status' => 'active'
];

$payload = [
    'iss' => $issuer,
    'iat' => time(),
    'exp' => time() + $expiry,
    'data' => $userData
];

$token = JWT::encode($payload, $secret, $algorithm);

echo "=== Generated Fresh JWT Token ===\n\n";
echo "User: {$userData['email']}\n";
echo "Role: {$userData['role']}\n";
echo "ID: {$userData['id']}\n\n";
echo "Token:\n";
echo $token . "\n\n";
echo "=== Update your .env.local ===\n";
echo "Replace NEXT_PUBLIC_AUTH_TOKEN with the token above\n";
