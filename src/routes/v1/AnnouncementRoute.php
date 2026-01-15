<?php

declare(strict_types=1);

use App\Controllers\AnnouncementController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Announcement Routes
 * 
 * Endpoints for managing announcements
 * 
 * Public Endpoints:
 * - GET /v1/announcements/public - Get active announcements (public)
 * - GET /v1/announcements/{id} - Get single announcement (public)
 * 
 * Admin Endpoints:
 * - GET /v1/announcements - List all announcements
 * - POST /v1/announcements - Create announcement
 * - PUT /v1/announcements/{id} - Update announcement
 * - DELETE /v1/announcements/{id} - Delete announcement
 * - POST /v1/announcements/{id}/publish - Publish announcement
 * - POST /v1/announcements/{id}/archive - Archive announcement
 */

return function (App $app) {
    $controller = $app->getContainer()->get(AnnouncementController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes (no authentication required)
    $app->get('/v1/announcements/public', [$controller, 'publicList']);
    $app->get('/v1/announcements/{id}', [$controller, 'show']);

    // Admin routes (require admin or web_admin role)
    $app->group('/v1/admin/announcements', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->delete('/{id}', [$controller, 'destroy']);
        $group->post('/{id}/publish', [$controller, 'publish']);
        $group->post('/{id}/archive', [$controller, 'archive']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
