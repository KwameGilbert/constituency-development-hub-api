<?php

declare(strict_types=1);

use App\Controllers\FAQController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * FAQ Routes
 * 
 * Frequently asked questions
 * Prefix: /v1/faqs
 */

return function (App $app) {
    $controller = $app->getContainer()->get(FAQController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/faqs', [$controller, 'index']);
    $app->get('/v1/faqs/categories', [$controller, 'categories']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/faqs', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/reorder', [$controller, 'reorder']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
