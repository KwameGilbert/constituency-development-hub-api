<?php

declare(strict_types=1);

use App\Controllers\ProfileController;
use App\Middleware\AuthMiddleware;
use Slim\App;

/**
 * Profile Routes
 * 
 * User profile management endpoints
 * All routes require authentication
 * Prefix: /v1/profile
 */

return function (App $app) {
    $controller = $app->getContainer()->get(ProfileController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // All profile routes require authentication
    $app->group('/v1/profile', function ($group) use ($controller) {
        // GET /v1/profile - Get current user profile
        $group->get('', [$controller, 'show']);

        // PUT /v1/profile - Update profile
        $group->put('', [$controller, 'update']);

        // POST /v1/profile/avatar - Upload avatar
        $group->post('/avatar', [$controller, 'uploadAvatar']);

        // PUT /v1/profile/password - Change password
        $group->put('/password', [$controller, 'changePassword']);

        // GET /v1/profile/activity - Get activity history
        $group->get('/activity', [$controller, 'activity']);
    })->add($authMiddleware);
};
