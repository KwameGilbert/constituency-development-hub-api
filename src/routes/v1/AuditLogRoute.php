<?php

declare(strict_types=1);

use App\Controllers\AuditLogController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app) {
    $controller = $app->getContainer()->get(AuditLogController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    $app->group('/v1/admin/audit-logs', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
    })->add(new RoleMiddleware(['web_admin', 'admin']))->add($authMiddleware);
};
