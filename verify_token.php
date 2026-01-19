<?php

/**
 * Test script to verify the new JWT token is valid
 */

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// The new token from .env.local
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJldmVudGljLWFwaSIsImlhdCI6MTc2ODc3MDI3OSwiZXhwIjoxMDAwMDAwMDE3Njg3NzAyNzgsImRhdGEiOnsiaWQiOjMsImVtYWlsIjoiam9obi5tZW5zYWhAY29uc3RpdHVlbmN5Lmdvdi5naCIsInJvbGUiOiJ3ZWJfYWRtaW4iLCJzdGF0dXMiOiJhY3RpdmUifX0.H0TVhZskuWoZjbBETb76ffO7yvQ6eTP0AFb84MiZgjI';

$secret = 'ERGRT3X'; // From your .env
$algorithm = 'HS256';

echo "=== Verifying JWT Token ===\n\n";

try {
    $decoded = JWT::decode($token, new Key($secret, $algorithm));
    
    echo "✓ Token is VALID\n\n";
    echo "Decoded payload:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "User Data:\n";
    echo "  ID: " . $decoded->data->id . "\n";
    echo "  Email: " . $decoded->data->email . "\n";
    echo "  Role: " . $decoded->data->role . "\n";
    echo "  Status: " . $decoded->data->status . "\n\n";
    
    // Verify role is correct
    if ($decoded->data->role === 'web_admin') {
        echo "✓ Role is 'web_admin' - CORRECT!\n";
    } else {
        echo "✗ Role is '{$decoded->data->role}' - UNEXPECTED!\n";
    }
    
} catch (Exception $e) {
    echo "✗ Token is INVALID\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Verification ===\n";
