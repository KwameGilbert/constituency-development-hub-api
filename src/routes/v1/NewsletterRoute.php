<?php

declare(strict_types=1);

use App\Controllers\NewsletterController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Newsletter Routes
 * 
 * Newsletter subscriptions
 * Prefix: /v1/newsletter
 */

return function (App $app) {
    $controller = $app->getContainer()->get(NewsletterController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes
    $app->post('/v1/newsletter/subscribe', [$controller, 'subscribe']);
    $app->post('/v1/newsletter/unsubscribe', [$controller, 'unsubscribe']);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/newsletter', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
        $group->get('/export', [$controller, 'export']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
