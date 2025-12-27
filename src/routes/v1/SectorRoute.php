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

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/sectors', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id:[0-9]+}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/reorder', [$controller, 'reorder']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
