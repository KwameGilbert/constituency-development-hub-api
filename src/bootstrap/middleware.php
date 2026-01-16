<?php

/**
 * Middleware Configuration
 * 
 * Registers all application middleware
 */
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use App\Helper\ErrorHandler as ErrorHandler;
use App\Middleware\RequestResponseLoggerMiddleware;
use App\Middleware\JsonBodyParserMiddleware as JsonBodyParserMiddleware;
use App\Middleware\RateLimitMiddleware as RateLimitMiddleware;

return function ($app, $container, $config) {
    
    // Get configurations
    $environment = $config['env'];
    $corsConfig = require CONFIG . '/Cors.php';
    
    // ==================== ERROR HANDLING ====================
    
    // Configure error middleware with custom handler
    $errorMiddleware = $app->addErrorMiddleware(
        displayErrorDetails: $environment === 'development',
        logErrors: true,
        logErrorDetails: $environment === 'development',
        logger: $container->get('logger')
    );
    
    // Add Method Override Middleware (Important for Forms/PUT requests)
    $app->add(new MethodOverrideMiddleware());
    
    // Set custom error handler
    $errorHandler = new ErrorHandler(
        $container->get('logger'),
        $environment
    );
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
    
    // ==================== HTTP LOGGING ====================
    
    // Add HTTP logger middleware
    if ($container->has('httpLogger')) {
        $app->add(new RequestResponseLoggerMiddleware($container->get('httpLogger')));
    }
    
    // ==================== RATE LIMITING ====================
    
    // Add Rate Limit middleware
    $app->add(new RateLimitMiddleware());

    // ==================== JSON BODY PARSING ====================
    
    $app->add($container->get(JsonBodyParserMiddleware::class));

    // ==================== CORS ====================
    
    // Add CORS middleware - Added LAST so it runs FIRST (wrapping all others like JsonBodyParser)
    $app->add(function ($request, $handler) use ($corsConfig) {
        $response = $handler->handle($request);
        $allowedOrigins = explode(',', (string)$corsConfig['allowed_origins']);
        $origin = $request->getHeaderLine('Origin');

        // Check if origin is allowed
        if (in_array($origin, $allowedOrigins)) {
            $allowedOrigin = $origin;
        } else {
            // Default to first allowed origin if not matched (or keep strict?)
            // For security, usually better to NOT return ACAO if not matched, 
            // but for this patch we'll fallback to the first one or just return null
            $allowedOrigin = $allowedOrigins[0]; 
        }

        // Handle wildcard case
        if (trim($corsConfig['allowed_origins']) === '*') {
            $allowedOrigin = '*';
        }
        
        $allowCredentials = is_callable($corsConfig['allow_credentials']) 
            ? $corsConfig['allow_credentials']($allowedOrigin) 
            : $corsConfig['allow_credentials'];
            
        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Headers', $corsConfig['allowed_headers'])
            ->withHeader('Access-Control-Allow-Methods', $corsConfig['allowed_methods'])
            ->withHeader('Access-Control-Allow-Credentials', $allowCredentials)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Access-Control-Max-Age', (string)$corsConfig['max_age'])
            ->withHeader('Content-Type', 'application/json');
    });
    
    // Handle preflight OPTIONS requests
    $app->options('/{routes:.+}', function ($request, $response) use ($corsConfig) {
        $allowedOrigins = explode(',', (string)$corsConfig['allowed_origins']);
        $origin = $request->getHeaderLine('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $allowedOrigin = $origin;
        } else {
            $allowedOrigin = $allowedOrigins[0];
        }

        if (trim($corsConfig['allowed_origins']) === '*') {
            $allowedOrigin = '*';
        }

        $allowCredentials = is_callable($corsConfig['allow_credentials']) 
            ? $corsConfig['allow_credentials']($allowedOrigin) 
            : $corsConfig['allow_credentials'];
            
        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Headers', $corsConfig['allowed_headers'])
            ->withHeader('Access-Control-Allow-Methods', $corsConfig['allowed_methods'])
            ->withHeader('Access-Control-Allow-Credentials', $allowCredentials)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Access-Control-Max-Age', (string)$corsConfig['max_age'])
            ->withHeader('Content-Type', 'application/json');
    });
    
    // ==================== CONTENT LENGTH ====================
    
    // $app->add(new ContentLengthMiddleware());
    
    return $app;
};
