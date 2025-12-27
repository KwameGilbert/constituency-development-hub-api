<?php

declare(strict_types=1);

use App\Controllers\HeroSlideController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Hero Slide Routes
 * 
 * Homepage carousel slides
 * Prefix: /v1/hero-slides
 */

return function (App $app) {
    $controller = $app->getContainer()->get(HeroSlideController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/hero-slides', [$controller, 'index']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/hero-slides', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/reorder', [$controller, 'reorder']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
