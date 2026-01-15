<?php

declare(strict_types=1);

namespace App\Routes\V1;

use App\Controllers\GalleryController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Gallery Routes
 * 
 * Prefix: /v1/gallery
 */
return function (App $app) {
    $container = $app->getContainer();
    $controller = $container->get(GalleryController::class);
    $authMiddleware = $container->get(AuthMiddleware::class);

    // Public routes
    $app->get('/v1/gallery', [$controller, 'publicIndex']);
    $app->get('/v1/gallery/{id}', [$controller, 'show']);

    // Admin routes
    $app->group('/v1/admin/gallery', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
        $group->post('', [$controller, 'store']);
        $group->get('/{id}', [$controller, 'show']);
        
        // Support FormData update via POST + _method override
        $group->post('/{id}', [$controller, 'update']);
        $group->put('/{id}', [$controller, 'update']);
        
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))
      ->add($authMiddleware);
};
