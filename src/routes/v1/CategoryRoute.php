<?php

declare(strict_types=1);

/**
 * Category Routes (v1 API)
 * 
 * Admin category management endpoints
 * Prefix: /v1/categories and /v1/admin/categories
 */

use App\Controllers\CategoryController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(CategoryController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public category routes
    $app->get('/v1/categories', [$controller, 'index']);
    $app->get('/v1/categories/{slug}', [$controller, 'showBySlug']);

    // Admin category management routes (require web_admin role)
    $app->group('/v1/admin/categories', function ($group) use ($controller) {
        // GET /v1/admin/categories - List all categories
        $group->get('', [$controller, 'adminIndex']);

        // PUT /v1/admin/categories/reorder - Reorder categories (must be before /{id})
        $group->put('/reorder', [$controller, 'reorder']);

        // GET /v1/admin/categories/{id} - Get single category
        $group->get('/{id}', [$controller, 'show']);

        // POST /v1/admin/categories - Create new category
        $group->post('', [$controller, 'store']);

        // PUT /v1/admin/categories/{id} - Update category
        $group->put('/{id}', [$controller, 'update']);

        // DELETE /v1/admin/categories/{id} - Delete category
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['admin', 'super_admin', 'web_admin']))->add($authMiddleware);
};
