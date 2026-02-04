<?php

/**
 * CORS Configuration
 * 
 * Cross-Origin Resource Sharing settings
 */

return [
    // Allowed Origins

    // In production, set to specific domains: 'https://yourdomain.com,https://app.yourdomain.com'
    // In development, can use '*' for testing
    'allowed_origins' => (function() {
        $env = $_SERVER['ALLOWED_ORIGINS'] ?? getenv('ALLOWED_ORIGINS') ?: '';
        $defaults = [
            'https://kofibenteh.com',
            'https://admin.kofibenteh.com',
            'https://www.kofibenteh.com',
            'https://api.kofibenteh.com',
            'https://kofibentehafful.com',
            'https://www.kofibentehafful.com',
            'https://admin.kofibentehafful.com',
            'https://agent.kofibentehafful.com',
            'https://app.kofibentehafful.com'
        ];
        
        if ($env === '*') return '*';
        
        $envOrigins = $env ? explode(',', $env) : [];
        $allOrigins = array_unique(array_merge($envOrigins, $defaults));
        
        return implode(',', $allOrigins);
    })(),
    
    // Allowed Headers
    'allowed_headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-HTTP-Method-Override',
    
    // Allowed Methods
    'allowed_methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    
    // Max Age
    'max_age' => 86400, // 24 hours
    
    // Allow Credentials
    // Automatically set to false if allowed_origins is '*'
    'allow_credentials' => function($allowedOrigins) {
        return $allowedOrigins !== '*' ? 'true' : 'false';
    }
];
