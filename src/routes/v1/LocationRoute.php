<?php

declare(strict_types=1);

/**
 * Location Routes (v1 API)
 * 
 * Admin location management endpoints
 * Prefix: /v1/admin/locations
 */

use App\Controllers\LocationController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(LocationController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public/Shared location routes
    $app->get('/v1/locations', [$controller, 'index']);

    // Admin location management routes (require admin or web_admin role)
    $app->group('/v1/admin/locations', function ($group) use ($controller) {
        // GET /v1/admin/locations/types - Get location types summary (must be before /{id})
        $group->get('/types', [$controller, 'types']);

        // GET /v1/admin/locations/dashboard-stats - Get dashboard statistics (must be before /{id})
        $group->get('/dashboard-stats', [$controller, 'dashboardStats']);

        // GET /v1/admin/locations - List all locations with filtering and pagination
        $group->get('', [$controller, 'index']);

        // GET /v1/admin/locations/{id} - Get single location details
        $group->get('/{id}', [$controller, 'show']);

        // GET /v1/admin/locations/{id}/stats - Get location statistics
        $group->get('/{id}/stats', [$controller, 'stats']);

        // POST /v1/admin/locations - Create new location
        $group->post('', [$controller, 'create']);

        // PUT /v1/admin/locations/{id} - Update location details
        $group->put('/{id}', [$controller, 'update']);

        // DELETE /v1/admin/locations/{id} - Delete location
        $group->delete('/{id}', [$controller, 'delete']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
