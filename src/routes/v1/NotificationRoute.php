<?php

declare(strict_types=1);

/**
 * Notification Routes (v1 API)
 * 
 * User notification endpoints
 * Prefix: /v1/notifications
 */

use App\Controllers\NotificationController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(NotificationController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Notification routes (require authentication)
    $app->group('/v1/notifications', function ($group) use ($controller) {
        // GET /v1/notifications/unread-count - Get unread count (must be before /{id})
        $group->get('/unread-count', [$controller, 'unreadCount']);

        // PUT /v1/notifications/read-all - Mark all as read (must be before /{id})
        $group->put('/read-all', [$controller, 'markAllAsRead']);

        // GET /v1/notifications - Get user's notifications
        $group->get('', [$controller, 'index']);

        // GET /v1/notifications/{id} - Get single notification
        $group->get('/{id}', [$controller, 'show']);

        // PUT /v1/notifications/{id}/read - Mark notification as read
        $group->put('/{id}/read', [$controller, 'markAsRead']);

        // DELETE /v1/notifications/{id} - Delete notification
        $group->delete('/{id}', [$controller, 'delete']);
    })->add($authMiddleware);

    // Admin notification routes (for testing/admin purposes)
    $app->group('/v1/admin/notifications', function ($group) use ($controller) {
        // POST /v1/admin/notifications/test - Create test notification
        $group->post('/test', [$controller, 'createTest']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
