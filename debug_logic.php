<?php
// debug_logic.php

echo "Testing CORS Logic...\n";

// MockEnv
$mock_env_val = 'http://example.com,http://localhost:3000';
$res = $mock_env_val !== false ? (string)$mock_env_val : 'http://localhost:3000';
echo "Resolved Config: $res\n";

// Middleware Logic
$corsConfig = ['allowed_origins' => $res];
try {
    $allowedOrigins = explode(',', (string)$corsConfig['allowed_origins']);
    print_r($allowedOrigins);
    
    $origin = 'http://example.com';
    if (in_array($origin, $allowedOrigins)) {
        echo "Origin Match OK\n";
    } else {
        echo "Origin Mismatch\n";
    }
} catch (Throwable $e) {
    echo "Crash: " . $e->getMessage() . "\n";
}

echo "Done.\n";
