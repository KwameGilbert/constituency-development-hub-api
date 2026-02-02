<?php

declare(strict_types=1);

use App\Controllers\SectorController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Sector Routes
 * 
 * Project categories/sectors
 * Prefix: /v1/sectors
 */

return function (App $app) {
    $controller = $app->getContainer()->get(SectorController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/sectors', [$controller, 'index']);
    $app->get('/v1/sectors/{slug}', [$controller, 'showBySlug']);

    // Public Sub-sector routes
    $subSectorController = $app->getContainer()->get(\App\Controllers\SubSectorController::class);
    $app->get('/v1/sectors/{sectorId:[0-9]+}/sub-sectors', [$subSectorController, 'index']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/sectors', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id:[0-9]+}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/reorder', [$controller, 'reorder']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['admin', 'super_admin', 'web_admin']))->add($authMiddleware);

    // Sub-sector routes (require web_admin role)
    $app->group('/v1/admin', function ($group) use ($app) {
        $subSectorController = $app->getContainer()->get(\App\Controllers\SubSectorController::class);
        
        // Nested under sectors
        $group->get('/sectors/{sectorId:[0-9]+}/sub-sectors', [$subSectorController, 'index']);
        $group->post('/sectors/{sectorId:[0-9]+}/sub-sectors', [$subSectorController, 'store']);
        
        // Direct sub-sector operations
        $group->put('/sub-sectors/reorder', [$subSectorController, 'reorder']);
        $group->put('/sub-sectors/{id:[0-9]+}', [$subSectorController, 'update']);
        $group->delete('/sub-sectors/{id:[0-9]+}', [$subSectorController, 'destroy']);
    })->add(new RoleMiddleware(['web_admin', 'super_admin', 'admin']))->add($authMiddleware);
};
