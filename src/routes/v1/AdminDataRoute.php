<?php

declare(strict_types=1);

use App\Controllers\AdminDataController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

/**
 * Admin Data Routes
 * 
 * Provides endpoints for admin dashboard data
 * All endpoints require admin or web_admin role authentication
 * 
 * Endpoints:
 * - GET /v1/admin/data/agents - List agents data
 * - GET /v1/admin/data/analytics/charts - Analytics chart data
 * - GET /v1/admin/data/analytics/insights - Analytics insights
 * - GET /v1/admin/data/analytics/metrics - Analytics metrics
 * - GET /v1/admin/data/announcements - Announcements data
 * - GET /v1/admin/data/audit-logs - Audit logs data
 * - GET /v1/admin/data/employment-jobs - Employment jobs data
 * - GET /v1/admin/data/ideas - Community ideas data
 * - GET /v1/admin/data/metrics - Summary and entity metrics
 * - GET /v1/admin/data/recent-issues - Recent issues data
 * - GET /v1/admin/data/all - All admin dashboard data combined
 */

return function (App $app) {
    $controller = $app->getContainer()->get(AdminDataController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Admin data routes (require admin or web_admin role)
    $app->group('/v1/admin/data', function ($group) use ($controller) {
        // GET /v1/admin/data/agents - Get agents data
        $group->get('/agents', [$controller, 'getAgents']);

        // Analytics routes
        $group->get('/analytics/charts', [$controller, 'getAnalyticsCharts']);
        $group->get('/analytics/insights', [$controller, 'getAnalyticsInsights']);
        $group->get('/analytics/metrics', [$controller, 'getAnalyticsMetrics']);

        // GET /v1/admin/data/announcements - Get announcements data
        $group->get('/announcements', [$controller, 'getAnnouncements']);

        // GET /v1/admin/data/audit-logs - Get audit logs data
        $group->get('/audit-logs', [$controller, 'getAuditLogs']);

        // GET /v1/admin/data/employment-jobs - Get employment jobs data
        $group->get('/employment-jobs', [$controller, 'getEmploymentJobs']);

        // GET /v1/admin/data/ideas - Get community ideas data
        $group->get('/ideas', [$controller, 'getIdeas']);

        // GET /v1/admin/data/metrics - Get summary and entity metrics
        $group->get('/metrics', [$controller, 'getMetrics']);

        // GET /v1/admin/data/recent-issues - Get recent issues data
        $group->get('/recent-issues', [$controller, 'getRecentIssues']);

        // GET /v1/admin/data/all - Get all admin dashboard data combined
        $group->get('/all', [$controller, 'getAllData']);
    })->add(new RoleMiddleware(['admin', 'web_admin']))->add($authMiddleware);
};
