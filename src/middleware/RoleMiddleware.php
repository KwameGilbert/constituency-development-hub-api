<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Helper\ResponseHelper;

/**
 * Role Middleware
 *
 * Validates that the authenticated user has one of the required roles.
 * Must be used AFTER AuthMiddleware to ensure user data is available.
 */
class RoleMiddleware
{
    /**
     * @var array List of allowed roles for this middleware instance
     */
    private array $allowedRoles;

    /**
     * Constructor
     *
     * @param array $allowedRoles List of roles that are allowed to access the route
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * Process incoming request and validate user role
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Get user data from request (set by AuthMiddleware)
        $user = $request->getAttribute('user');

        // Enhanced debug logging
        error_log('=== RoleMiddleware Debug ===');
        error_log('Request URI: ' . $request->getUri()->getPath());
        error_log('User object exists: ' . ($user ? 'YES' : 'NO'));
        
        if ($user) {
            error_log('User object type: ' . gettype($user));
            error_log('User object JSON: ' . json_encode($user));
        }

        if (!$user) {
            error_log('RoleMiddleware Error: User not authenticated');
            return $this->createForbiddenResponse('User not authenticated');
        }

        // Get user role - try multiple access methods
        $userRole = null;
        
        // Try object property access
        if (is_object($user) && isset($user->role)) {
            $userRole = $user->role;
            error_log('Role extracted via object property: ' . $userRole);
        }
        // Try array access
        elseif (is_array($user) && isset($user['role'])) {
            $userRole = $user['role'];
            error_log('Role extracted via array access: ' . $userRole);
        }
        
        error_log('Final extracted role: ' . ($userRole ?? 'NULL'));
        error_log('Allowed roles: ' . json_encode($this->allowedRoles));

        if (!$userRole) {
            error_log('RoleMiddleware Error: User role not found in user data');
            return $this->createForbiddenResponse('User role not found');
        }

        // Check if user's role is in the allowed roles
        $roleMatch = in_array($userRole, $this->allowedRoles);
        error_log('Role match result: ' . ($roleMatch ? 'TRUE' : 'FALSE'));
        
        if (!$roleMatch) {
            error_log('RoleMiddleware Error: Role "' . $userRole . '" not in allowed roles');
            return $this->createForbiddenResponse(
                'Access denied. Required role: ' . implode(' or ', $this->allowedRoles)
            );
        }

        error_log('RoleMiddleware: Access granted for role "' . $userRole . '"');
        error_log('=== End RoleMiddleware Debug ===');

        // User has required role, continue with the request
        return $handler->handle($request);
    }

    /**
     * Create a forbidden response
     *
     * @param string $message
     * @return Response
     */
    private function createForbiddenResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        return ResponseHelper::error($response, $message, 403);
    }
}
