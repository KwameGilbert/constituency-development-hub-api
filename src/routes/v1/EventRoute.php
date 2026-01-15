<?php

declare(strict_types=1);

use App\Controllers\ConstituencyEventController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Constituency Event Routes
 * 
 * Community events
 * Prefix: /v1/events
 */

return function (App $app) {
    $controller = $app->getContainer()->get(ConstituencyEventController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/events', [$controller, 'index']);
    $app->get('/v1/events/upcoming', [$controller, 'upcoming']);
    $app->get('/v1/events/{slug}', [$controller, 'showBySlug']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/events', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id:[0-9]+}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->post('/{id}', [$controller, 'update']); // POST also supported for FormData updates
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
