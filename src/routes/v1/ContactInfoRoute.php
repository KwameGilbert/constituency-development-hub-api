<?php

declare(strict_types=1);

use App\Controllers\ContactInfoController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Contact Info Routes
 * 
 * Contact details management
 * Prefix: /v1/contact
 */

return function (App $app) {
    $controller = $app->getContainer()->get(ContactInfoController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/contact', [$controller, 'index']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/contact', function ($group) use ($controller) {
        $group->get('', [$controller, 'adminIndex']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
