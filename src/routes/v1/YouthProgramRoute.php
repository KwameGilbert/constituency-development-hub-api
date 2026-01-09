<?php

declare(strict_types=1);

/**
 * Youth Program Routes (v1 API)
 * 
 * Public and admin youth program management endpoints
 */

use App\Controllers\YouthProgramController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $controller = $app->getContainer()->get(YouthProgramController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public youth program routes (no auth required)
    $app->group('/v1/youth-programs', function ($group) use ($controller) {
        // GET /v1/youth-programs - List active programs (public)
        $group->get('', [$controller, 'publicIndex']);

        // GET /v1/youth-programs/{slug} - Get program by slug (public)
        $group->get('/{slug}', [$controller, 'publicShow']);

        // POST /v1/youth-programs/{id}/enroll - Enroll in a program (public)
        $group->post('/{id}/enroll', [$controller, 'enroll']);
    });

    // Admin youth program routes (require admin or web_admin role)
    $app->group('/v1/admin/youth-programs', function ($group) use ($controller) {
        // GET /v1/admin/youth-programs - List all programs
        $group->get('', [$controller, 'index']);

        // GET /v1/admin/youth-programs/{id} - Get program details
        $group->get('/{id}', [$controller, 'show']);

        // GET /v1/admin/youth-programs/{id}/participants - Get participants
        $group->get('/{id}/participants', [$controller, 'participants']);

        // POST /v1/admin/youth-programs - Create program
        $group->post('', [$controller, 'create']);

        // PUT /v1/admin/youth-programs/{id} - Update program
        $group->put('/{id}', [$controller, 'update']);

        // PUT /v1/admin/youth-programs/{id}/participants/{participantId} - Update participant
        $group->put('/{id}/participants/{participantId}', [$controller, 'updateParticipant']);

        // DELETE /v1/admin/youth-programs/{id} - Delete program
        $group->delete('/{id}', [$controller, 'delete']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
