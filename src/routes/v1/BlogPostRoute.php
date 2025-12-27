<?php

declare(strict_types=1);

use App\Controllers\BlogPostController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Blog Post Routes
 * 
 * News and articles
 * Prefix: /v1/blog
 */

return function (App $app) {
    $controller = $app->getContainer()->get(BlogPostController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/blog', [$controller, 'index']);
    $app->get('/v1/blog/featured', [$controller, 'featured']);
    $app->get('/v1/blog/categories', [$controller, 'categories']);
    $app->get('/v1/blog/{slug}', [$controller, 'showBySlug']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/blog', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id:[0-9]+}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->post('/{id}/publish', [$controller, 'publish']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
