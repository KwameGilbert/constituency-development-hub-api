<?php
// reproduce_cors.php

require __DIR__ . '/vendor/autoload.php';

// Mock Config
$config = [];
$config['env'] = 'production';

// Mock ENV
$_ENV['ALLOWED_ORIGINS'] = 'http://localhost:3000,http://example.com';

// Load Cors Config
$corsConfig = require __DIR__ . '/src/config/Cors.php';

echo "Allowed Origins Raw: " . $corsConfig['allowed_origins'] . "\n";

// Mock Slim Request/Response classes would be complex, 
// so let's just test the logic block from middleware.php directly.

$allowedOrigins = explode(',', $corsConfig['allowed_origins']);
print_r($allowedOrigins);

$origin = 'http://example.com'; // Simulate matching origin

if (in_array($origin, $allowedOrigins)) {
    echo "Match found!\n";
    $allowedOrigin = $origin;
} else {
    echo "No match.\n";
    $allowedOrigin = $allowedOrigins[0]; 
}

echo "Set Allowed Origin: " . $allowedOrigin . "\n";

// Test Fallback
$origin = 'http://evil.com';
if (in_array($origin, $allowedOrigins)) {
     echo "Match found!\n";
} else {
     echo "No match. Fallback to: " . $allowedOrigins[0] . "\n";
}
