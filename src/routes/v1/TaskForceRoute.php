<?php

declare(strict_types=1);

use App\Controllers\TaskForceController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Task Force Routes
 * 
 * Task Force member management and workflow
 * Prefix: /v1/task-force, /v1/admin/task-force
 */

return function (App $app) {
    $controller = $app->getContainer()->get(TaskForceController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Task Force dashboard routes (require task_force role)
    $app->group('/v1/task-force', function ($group) use ($controller) {
        // Profile
        $group->get('/profile', [$controller, 'profile']);
        
        // Assigned issues
        $group->get('/issues', [$controller, 'myIssues']);
        
        // Assessment workflow
        $group->post('/issues/{id}/start-assessment', [$controller, 'startAssessment']);
        $group->post('/issues/{id}/assessment', [$controller, 'submitAssessment']);
        
        // Resolution workflow
        $group->post('/issues/{id}/start-resolution', [$controller, 'startResolution']);
        $group->post('/issues/{id}/resolution', [$controller, 'submitResolution']);
    })->add(new RoleMiddleware(['task_force']))->add($authMiddleware);

    // Admin routes (require web_admin role)
    $app->group('/v1/admin/task-force', function ($group) use ($controller) {
        $group->get('', [$controller, 'index']);
        $group->get('/{id}', [$controller, 'show']);
        $group->post('', [$controller, 'store']);
        $group->put('/{id}', [$controller, 'update']);
        $group->post('/{id}/verify', [$controller, 'verify']);
        $group->delete('/{id}', [$controller, 'destroy']);
    })->add(new RoleMiddleware(['web_admin']))->add($authMiddleware);
};
