<?php

declare(strict_types=1);

/**
 * Youth Record Routes (v1 API)
 * 
 * Admin youth record management endpoints
 */

use App\Controllers\YouthRecordController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(YouthRecordController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Admin youth record routes (require admin role)
    $app->group('/v1/admin/youth-records', function ($group) use ($controller) {
        // GET /v1/admin/youth-records - List all records
        $group->get('', [$controller, 'index']);

        // GET /v1/admin/youth-records/stats - Get statistics
        $group->get('/stats', [$controller, 'stats']);

        // GET /v1/admin/youth-records/{id} - Get record details
        $group->get('/{id}', [$controller, 'show']);

        // POST /v1/admin/youth-records - Create record
        $group->post('', [$controller, 'create']);

        // PUT /v1/admin/youth-records/{id} - Update record
        $group->put('/{id}', [$controller, 'update']);

        // PUT /v1/admin/youth-records/{id}/status - Update record status
        $group->put('/{id}/status', [$controller, 'updateStatus']);

        // DELETE /v1/admin/youth-records/{id} - Delete record
        $group->delete('/{id}', [$controller, 'delete']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
