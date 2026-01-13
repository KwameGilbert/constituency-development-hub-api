<?php

declare(strict_types=1);

use App\Controllers\OfficerReportsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Officer Reports Routes
 * 
 * Reporting endpoints for officer dashboard
 * Prefix: /v1/officer/reports
 */

return function (App $app) {
    $controller = $app->getContainer()->get(OfficerReportsController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Officer reports routes (require officer, admin, or web_admin role)
    $app->group('/v1/officer/reports', function ($group) use ($controller) {
        // GET /v1/officer/reports/summary - Summary stats
        $group->get('/summary', [$controller, 'summary']);
        
        // GET /v1/officer/reports/breakdown - Category/Location breakdown
        $group->get('/breakdown', [$controller, 'breakdown']);
        
        // GET /v1/officer/reports/recent-activity - Recent activity feed
        $group->get('/recent-activity', [$controller, 'recentActivity']);
        
        // GET /v1/officer/reports/trends - Monthly trends data
        $group->get('/trends', [$controller, 'trends']);
        
        // GET /v1/officer/reports/status-distribution - Status distribution for charts
        $group->get('/status-distribution', [$controller, 'statusDistribution']);
        
        // GET /v1/officer/reports/agent-performance - Top agent performance
        $group->get('/agent-performance', [$controller, 'agentPerformance']);
        
        // GET /v1/officer/reports/profile-stats - Officer profile activity stats
        $group->get('/profile-stats', [$controller, 'profileStats']);
    })->add(new RoleMiddleware(['officer', 'admin', 'web_admin']))->add($authMiddleware);
};
