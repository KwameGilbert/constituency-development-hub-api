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

        if (!$user) {
            return $this->createForbiddenResponse('User not authenticated');
        }

        // Get user role
        $userRole = $user->role ?? null;

        if (!$userRole) {
            return $this->createForbiddenResponse('User role not found');
        }

        // Check if user's role is in the allowed roles
        if (!in_array($userRole, $this->allowedRoles)) {
            return $this->createForbiddenResponse(
                'Access denied. Required role: ' . implode(' or ', $this->allowedRoles)
            );
        }

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
