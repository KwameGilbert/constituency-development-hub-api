<?php

/**
 * CORS Configuration
 * 
 * Cross-Origin Resource Sharing settings
 */

return [
    // Allowed Origins
    // Production: set the env var ALLOWED_ORIGINS to a comma-separated list
    // Development: you may set '*' to allow all origins (not recommended for prod)
    'allowed_origins' => (function() {
        $env = getenv('ALLOWED_ORIGINS');
        if ($env && trim($env) !== '') {
            return $env;
        }

        // Older setups may populate $_ENV; fall back to that
        if (!empty($_ENV['ALLOWED_ORIGINS'])) {
            return $_ENV['ALLOWED_ORIGINS'];
        }

        // Fallback: if no env is set, reflect the incoming Origin header at runtime
        // Note: middleware expects a comma-separated string; reflect will be handled there
        return $_SERVER['HTTP_ORIGIN'] ?? '*';
    })(),

    // Allowed Headers
    'allowed_headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-HTTP-Method-Override',

    // Allowed Methods
    'allowed_methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',

    // Max Age
    'max_age' => 86400, // 24 hours

    // Allow Credentials
    // Returns 'true' when ALLOWED_ORIGINS is not a wildcard
    'allow_credentials' => function($allowedOrigins) {
        return trim((string)$allowedOrigins) !== '*' ? 'true' : 'false';
    }
];
