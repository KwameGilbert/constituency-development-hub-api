<?php

declare(strict_types=1);

use App\Controllers\CommunityStatController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Community Stat Routes
 * 
 * Homepage statistics display
 * Prefix: /v1/stats
 */

return function (App $app) {
    $controller = $app->getContainer()->get(CommunityStatController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/stats', [$controller, 'index']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/stats', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/reorder', [$controller, 'reorder']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
