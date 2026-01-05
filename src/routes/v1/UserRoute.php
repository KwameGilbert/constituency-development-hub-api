<?php

declare(strict_types=1);

/**
 * User Routes (v1 API)
 * 
 * Admin user management endpoints
 * Prefix: /v1/admin/users
 */

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $userController = $app->getContainer()->get(UserController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Admin user management routes (require admin or web_admin role)
    $app->group('/v1/admin/users', function ($group) use ($userController) {
        // GET /v1/admin/users/stats - Get user statistics (must be before /{id})
        $group->get('/stats', [$userController, 'stats']);

        // GET /v1/admin/users - List all users with filtering and pagination
        $group->get('', [$userController, 'index']);

        // GET /v1/admin/users/{id} - Get single user details
        $group->get('/{id}', [$userController, 'show']);

        // POST /v1/admin/users - Create new user
        $group->post('', [$userController, 'create']);

        // PUT /v1/admin/users/{id} - Update user details
        $group->put('/{id}', [$userController, 'update']);

        // DELETE /v1/admin/users/{id} - Delete user
        $group->delete('/{id}', [$userController, 'delete']);

        // PUT /v1/admin/users/{id}/role - Update user role
        $group->put('/{id}/role', [$userController, 'updateRole']);

        // PUT /v1/admin/users/{id}/status - Update user status
        $group->put('/{id}/status', [$userController, 'updateStatus']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};