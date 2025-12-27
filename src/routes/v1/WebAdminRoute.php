<?php

declare(strict_types=1);

use App\Controllers\WebAdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Web Admin Routes
 * 
 * Web administrator management
 * Prefix: /v1/admin/web-admins
 */

return function (App $app) {
    $controller = $app->getContainer()->get(WebAdminController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Super admin only routes
    $app->group('/v1/admin/web-admins', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
