<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Officer;
use App\Models\TaskForce;
use App\Models\IssueReport;
use App\Models\Project;
use App\Models\Announcement;
use App\Models\EmploymentJob;
use App\Models\CommunityIdea;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Carbon\Carbon;

/**
 * AdminDataController
 * 
 * Provides admin dashboard data endpoints for serving various data types.
 * This controller serves both database data and static JSON data from the data folder.
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
class AdminDataController
{
    public function __construct()
    {
        // No static data dependencies
    }

    /**
     * Get agents data
     * GET /v1/admin/data/agents
     * 
     * Returns agent data from the database with fallback to static JSON
     */
    public function getAgents(Request $request, Response $response): Response
    {
        try {
            $agents = Agent::with('user')->get();

            // Transform database data to match expected format
            $agentsData = $agents->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->user->name ?? 'Unknown',
                    'email' => $agent->user->email ?? '',
                    'phone' => $agent->user->phone ?? '',
                    'location' => $agent->assigned_location ?? '',
                    'status' => $agent->user->status ?? 'inactive',
                    'role' => 'Field Agent',
                    'lastLogin' => $agent->user->last_login_at ?? null,
                    'dateAdded' => $agent->created_at ?? null,
                    'issuesHandled' => $agent->reports_submitted ?? 0,
                    'activeIssues' => IssueReport::where('submitted_by_agent_id', $agent->id)
                        ->whereNotIn('status', ['resolved', 'closed'])
                        ->count(),
                    'performance' => $this->calculatePerformance($agent->reports_submitted ?? 0),
                ];
            });

            $summary = [
                'total' => $agents->count(),
                'active' => $agents->filter(fn($a) => $a->user && $a->user->status === 'active')->count(),
                'inactive' => $agents->filter(fn($a) => !$a->user || $a->user->status !== 'active')->count(),
            ];

            return ResponseHelper::success($response, 'Agents data retrieved', [
                'agents' => $agentsData,
                'summary' => $summary,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve agents data', 500, $e->getMessage());
        }
    }

    /**
     * Calculate performance rating based on issues handled
     */
    private function calculatePerformance(int $issuesHandled): string
    {
        if ($issuesHandled >= 40) return 'Excellent';
        if ($issuesHandled >= 25) return 'Good';
        if ($issuesHandled >= 10) return 'Average';
        return 'Developing';
    }

    /**
     * Get analytics charts data
     * GET /v1/admin/data/analytics/charts
     */
    public function getAnalyticsCharts(Request $request, Response $response): Response
    {
        try {
            // Try to generate from database
            $issuesByStatus = [
                ['name' => 'Resolved', 'value' => IssueReport::where('status', 'resolved')->count(), 'color' => '#10b981'],
                ['name' => 'In Progress', 'value' => IssueReport::where('status', 'in_progress')->count(), 'color' => '#f59e0b'],
                ['name' => 'Pending', 'value' => IssueReport::whereIn('status', ['pending', 'pending_review'])->count(), 'color' => '#3b82f6'],
                ['name' => 'New', 'value' => IssueReport::where('status', 'submitted')->count(), 'color' => '#ef4444'],
            ];

            // Monthly trends (last 6 months)
            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                
                $monthlyTrends[] = [
                    'name' => $date->format('Y-m'),
                    'issues' => IssueReport::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'resolved' => IssueReport::whereBetween('resolved_at', [$monthStart, $monthEnd])->count(),
                ];
            }

            // Category distribution
            $categories = IssueReport::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get();

            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6b7280'];
            $categoryDistribution = $categories->map(function ($cat, $index) use ($colors) {
                return [
                    'name' => ucfirst($cat->category ?? 'Other'),
                    'value' => $cat->count,
                    'color' => $colors[$index % count($colors)],
                ];
            });

            $data = [
                'charts' => [
                    'issueStatusDistribution' => $issuesByStatus,
                    'monthlyTrends' => $monthlyTrends,
                    'categoryDistribution' => $categoryDistribution,
                    'budgetDistribution' => [
                        [
                            'name' => 'Project Budget',
                            'value' => (float) Project::sum('budget'),
                            'color' => '#f59e0b', // Amber
                        ],
                        [
                            'name' => 'Issues Budget',
                            'value' => (float) IssueReport::sum('allocated_budget'),
                            'color' => '#6366f1', // Indigo
                        ],
                    ],
                    'budgetTrends' => $monthlyTrends, // Using same monthly trends for now, ideally would be separate budget query
                ],
            ];

            return ResponseHelper::success($response, 'Analytics charts data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve analytics charts data', 500, $e->getMessage());
        }
    }

    /**
     * Get analytics insights data
     * GET /v1/admin/data/analytics/insights
     */
    public function getAnalyticsInsights(Request $request, Response $response): Response
    {
        try {
            // Get top performers from agents
            $topAgents = Agent::with('user')
                ->orderBy('reports_submitted', 'desc')
                ->limit(5)
                ->get();

            $topPerformers = $topAgents->map(function ($agent, $index) {
                $total = $agent->reports_submitted ?? 0;
                $resolved = IssueReport::where('submitted_by_agent_id', $agent->id)
                    ->where('status', 'resolved')
                    ->count();
                
                return [
                    'id' => $agent->id,
                    'name' => $agent->user->name ?? 'Unknown',
                    'role' => 'Field Agent',
                    'resolvedCount' => $resolved,
                    'totalCount' => $total,
                    'resolutionRate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
                    'rank' => $index + 1,
                ];
            });

            // Community insights by location
            $locationStats = IssueReport::selectRaw('location, COUNT(*) as total, 
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved')
                ->groupBy('location')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            $communityInsights = $locationStats->map(function ($stat) {
                $resolutionRate = $stat->total > 0 ? round(($stat->resolved / $stat->total) * 100, 1) : 0;
                return [
                    'location' => $stat->location ?? 'Unknown',
                    'issuesReported' => $stat->total,
                    'avgResolutionTime' => '3.5 days', // Placeholder - would need status history for accurate calculation
                    'resolutionRate' => $resolutionRate,
                ];
            });

            $data = [
                'insights' => [
                    'topPerformers' => $topPerformers,
                    'communityInsights' => $communityInsights,
                ],
            ];

            return ResponseHelper::success($response, 'Analytics insights data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve analytics insights data', 500, $e->getMessage());
        }
    }

    /**
     * Get analytics metrics data
     * GET /v1/admin/data/analytics/metrics
     */
    public function getAnalyticsMetrics(Request $request, Response $response): Response
    {
        try {
            $now = Carbon::now();
            $lastWeek = $now->copy()->subWeek();
            $twoWeeksAgo = $now->copy()->subWeeks(2);

            // Current metrics
            $totalIssues = IssueReport::count();
            $activeStaff = User::where('status', 'active')->count();
            $totalProjects = Project::count();
            $activeBudget = Project::whereIn('status', ['planning', 'ongoing'])->sum('budget');
            $newIssuesThisWeek = IssueReport::where('created_at', '>=', $lastWeek)->count();
            $resolvedThisWeek = IssueReport::where('resolved_at', '>=', $lastWeek)->count();
            $activeUsers7Days = User::where('last_login_at', '>=', $now->copy()->subDays(7))->count();
            $ongoingProjects = Project::where('status', 'ongoing')->count();

            // Previous week metrics for trends
            $newIssuesLastWeek = IssueReport::whereBetween('created_at', [$twoWeeksAgo, $lastWeek])->count();
            $resolvedLastWeek = IssueReport::whereBetween('resolved_at', [$twoWeeksAgo, $lastWeek])->count();

            // Calculate trends (percentage change)
            $calcTrend = fn($current, $previous) => $previous > 0 
                ? round((($current - $previous) / $previous) * 100, 1) 
                : ($current > 0 ? 100 : 0);

            $data = [
                'metrics' => [
                    'totalIssues' => $totalIssues,
                    'activeStaff' => $activeStaff,
                    'totalProjects' => $totalProjects,
                    'activeBudget' => (float) $activeBudget,
                    'newIssuesThisWeek' => $newIssuesThisWeek,
                    'resolvedThisWeek' => $resolvedThisWeek,
                    'activeUsers7Days' => $activeUsers7Days,
                    'ongoingProjects' => $ongoingProjects,
                ],
                'trends' => [
                    'issuesChange' => $calcTrend($totalIssues, $totalIssues - $newIssuesThisWeek),
                    'staffChange' => 0, // Would need historical data
                    'projectsChange' => 0,
                    'budgetChange' => 0,
                    'newIssuesChange' => $calcTrend($newIssuesThisWeek, $newIssuesLastWeek),
                    'resolvedChange' => $calcTrend($resolvedThisWeek, $resolvedLastWeek),
                    'activeUsersChange' => 0,
                    'ongoingProjectsChange' => 0,
                ],
            ];

            return ResponseHelper::success($response, 'Analytics metrics data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve analytics metrics data', 500, $e->getMessage());
        }
    }

    /**
     * Get announcements data
     * GET /v1/admin/data/announcements
     */
    public function getAnnouncements(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 10);
            $status = $params['status'] ?? null;

            $query = Announcement::query();

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $announcements = $query
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            $data = [
                'announcements' => $announcements->map(fn($a) => $a->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
            ];

            return ResponseHelper::success($response, 'Announcements data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve announcements data', 500, $e->getMessage());
        }
    }

    /**
     * Get audit logs data
     * GET /v1/admin/data/audit-logs
     */
    public function getAuditLogs(Request $request, Response $response): Response
    {
        try {
            // Get query parameters for pagination
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 50);

            // Try to get from database (audit_logs table exists)
            $query = \Illuminate\Database\Capsule\Manager::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select([
                    'audit_logs.id',
                    'users.name as user',
                    'audit_logs.action',
                    'audit_logs.entity_type as resource',
                    'audit_logs.ip_address as ip',
                    'audit_logs.created_at as timestamp',
                    'audit_logs.user_agent',
                    'audit_logs.metadata',
                ]);

            $total = $query->count();
            $logs = $query
                ->orderBy('audit_logs.created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            $auditLogs = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user ?? 'System',
                    'action' => $log->action,
                    'resource' => $log->resource ?? 'Unknown',
                    'ip' => $log->ip ?? 'localhost',
                    'timestamp' => $log->timestamp,
                    'status' => 'success',
                    'user_agent' => $log->user_agent ?? 'Unknown',
                    'session_id' => 'sess_' . substr(md5((string) $log->id), 0, 6),
                ];
            });

            $data = [
                'auditLogs' => $auditLogs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ],
                'summary' => [
                    'total_logs' => $total,
                    'success_count' => $total,
                    'failed_count' => 0,
                    'warning_count' => 0,
                    'last_updated' => $logs->first()->timestamp ?? null,
                ],
            ];

            return ResponseHelper::success($response, 'Audit logs data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve audit logs data', 500, $e->getMessage());
        }
    }

    /**
     * Get employment jobs data
     * GET /v1/admin/data/employment-jobs
     */
    public function getEmploymentJobs(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 20);
            $status = $params['status'] ?? null;
            $category = $params['category'] ?? null;

            $query = EmploymentJob::query();

            if ($status) {
                $query->where('status', $status);
            }
            if ($category) {
                $query->where('category', $category);
            }

            $total = $query->count();
            $jobs = $query
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            $data = [
                'jobs' => $jobs->map(fn($job) => $job->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
                'statistics' => EmploymentJob::getStatistics(),
            ];

            return ResponseHelper::success($response, 'Employment jobs data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve employment jobs data', 500, $e->getMessage());
        }
    }

    /**
     * Get community ideas data
     * GET /v1/admin/data/ideas
     */
    public function getIdeas(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 20);
            $status = $params['status'] ?? null;
            $category = $params['category'] ?? null;

            $query = CommunityIdea::query();

            if ($status) {
                $query->where('status', $status);
            }
            if ($category) {
                $query->where('category', $category);
            }

            $total = $query->count();
            $ideas = $query
                ->orderBy('votes', 'desc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            $data = [
                'ideas' => $ideas->map(fn($idea) => $idea->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
                'statistics' => CommunityIdea::getStatistics(),
            ];

            return ResponseHelper::success($response, 'Ideas data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve ideas data', 500, $e->getMessage());
        }
    }

    /**
     * Get summary and entity metrics data
     * GET /v1/admin/data/metrics
     */
    public function getMetrics(Request $request, Response $response): Response
    {
        try {
            // Summary metrics
            $totalIssues = IssueReport::count();
            $pendingReview = IssueReport::whereIn('status', ['pending', 'pending_review'])->count();
            $activeUsers = User::where('status', 'active')->count();
            $totalUsers = User::count();
            $totalProjects = Project::count();
            $ongoingProjects = Project::where('status', 'ongoing')->count();
            $totalBudget = Project::sum('budget');

            // Entity metrics
            $fieldAgents = Agent::count();
            $officers = Officer::count();
            $administrators = User::where('role', 'web_admin')->count();
            $jobOpportunities = EmploymentJob::where('status', 'published')->count();

            $data = [
                'summaryMetrics' => [
                    [
                        'id' => 'totalIssues',
                        'label' => 'Total Issues',
                        'value' => $totalIssues,
                        'subtitle' => "{$pendingReview} pending review",
                        'icon' => 'ClipboardList',
                        'color' => 'blue',
                    ],
                    [
                        'id' => 'activeUsers',
                        'label' => 'Active Users',
                        'value' => $activeUsers,
                        'subtitle' => "{$totalUsers} total registered",
                        'icon' => 'Users',
                        'color' => 'emerald',
                    ],
                    [
                        'id' => 'projects',
                        'label' => 'Projects',
                        'value' => $totalProjects,
                        'subtitle' => "{$ongoingProjects} ongoing",
                        'icon' => 'FolderKanban',
                        'color' => 'purple',
                    ],
                    [
                        'id' => 'totalBudget',
                        'label' => 'Total Budget',
                        'value' => '₵' . number_format((float) $totalBudget, 0),
                        'subtitle' => 'Project allocations',
                        'icon' => 'Wallet',
                        'color' => 'amber',
                    ],
                ],
                'entityMetrics' => [
                    [
                        'id' => 'fieldAgents',
                        'label' => 'Field Agents',
                        'value' => $fieldAgents,
                        'icon' => 'Users',
                        'color' => 'blue',
                    ],
                    [
                        'id' => 'officers',
                        'label' => 'Officers',
                        'value' => $officers,
                        'icon' => 'ShieldCheck',
                        'color' => 'indigo',
                    ],
                    [
                        'id' => 'administrators',
                        'label' => 'Administrators',
                        'value' => $administrators,
                        'icon' => 'UserCog',
                        'color' => 'red',
                    ],
                    [
                        'id' => 'jobOpportunities',
                        'label' => 'Job Opportunities',
                        'value' => $jobOpportunities,
                        'icon' => 'Briefcase',
                        'color' => 'green',
                    ],
                ],
            ];

            return ResponseHelper::success($response, 'Metrics data retrieved', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve metrics data', 500, $e->getMessage());
        }
    }

    /**
     * Get recent issues data
     * GET /v1/admin/data/recent-issues
     */
    public function getRecentIssues(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = (int) ($params['limit'] ?? 10);

            $issues = IssueReport::with(['submittedByAgent.user', 'assignedOfficer.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $recentIssues = $issues->map(function ($issue) {
                return [
                    'id' => $issue->case_id ?? 'ISS-' . str_pad((string) $issue->id, 4, '0', STR_PAD_LEFT),
                    'title' => $issue->title,
                    'description' => $issue->description ?? '',
                    'agent' => $issue->submittedByAgent?->user?->name ?? 'Unknown',
                    'status' => $this->formatStatus($issue->status),
                    'severity' => ucfirst($issue->priority ?? 'medium'),
                    'date' => $issue->created_at ? Carbon::parse($issue->created_at)->format('Y-m-d') : null,
                    'category' => ucfirst($issue->category ?? 'General'),
                ];
            });

            return ResponseHelper::success($response, 'Recent issues data retrieved', [
                'recentIssues' => $recentIssues,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve recent issues data', 500, $e->getMessage());
        }
    }

    /**
     * Format status for display
     */
    private function formatStatus(string $status): string
    {
        $statusMap = [
            'submitted' => 'New',
            'pending' => 'Pending Review',
            'pending_review' => 'Pending Review',
            'acknowledged' => 'Acknowledged',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'rejected' => 'Rejected',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get all admin dashboard data combined
     * GET /v1/admin/data/all
     * 
     * @deprecated Use individual endpoints instead for real-time database data
     */
    public function getAllData(Request $request, Response $response): Response
    {
        return ResponseHelper::error(
            $response, 
            'This endpoint is deprecated. Please use individual data endpoints (e.g., /admin/data/analytics/charts, /admin/data/metrics, etc.)', 
            410
        );
    }

    /**
     * Generate a custom report based on user-selected parameters
     * POST /v1/admin/data/reports/generate
     */
    public function generateReport(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody();
            $reportType = $body['reportType'] ?? 'issues';
            $columns = $body['columns'] ?? [];
            $filters = $body['filters'] ?? [];
            $dateRange = $body['dateRange'] ?? 'all';
            $page = (int) ($body['page'] ?? 1);
            $limit = (int) ($body['limit'] ?? 50);

            $data = [];
            $total = 0;

            switch ($reportType) {
                case 'issues':
                    $result = $this->generateIssuesReport($columns, $filters, $dateRange, $page, $limit);
                    $data = $result['data'];
                    $total = $result['total'];
                    break;
                    
                case 'projects':
                    $result = $this->generateProjectsReport($columns, $filters, $dateRange, $page, $limit);
                    $data = $result['data'];
                    $total = $result['total'];
                    break;
                    
                case 'users':
                    $result = $this->generateUsersReport($columns, $filters, $dateRange, $page, $limit);
                    $data = $result['data'];
                    $total = $result['total'];
                    break;
                    
                default:
                    return ResponseHelper::error($response, 'Invalid report type', 400);
            }

            return ResponseHelper::success($response, 'Report generated successfully', [
                'reportType' => $reportType,
                'columns' => $columns,
                'rows' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to generate report', 500, $e->getMessage());
        }
    }

    /**
     * Generate Issues report
     */
    private function generateIssuesReport(array $columns, array $filters, string $dateRange, int $page, int $limit): array
    {
        $query = IssueReport::with(['submittedByAgent.user', 'assignedOfficer.user']);

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'any') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['severity']) && $filters['severity'] !== 'any') {
            $query->where('priority', $filters['severity']);
        }
        if (!empty($filters['category']) && $filters['category'] !== 'any') {
            $query->where('category', $filters['category']);
        }

        // Apply date range
        $this->applyDateRange($query, $dateRange);

        $total = $query->count();
        $issues = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        // Map data to selected columns
        $data = $issues->map(function ($issue) use ($columns) {
            $row = [];
            $columnMap = [
                'id' => $issue->case_id ?? 'ISS-' . str_pad((string) $issue->id, 4, '0', STR_PAD_LEFT),
                'title' => $issue->title,
                'status' => $this->formatStatus($issue->status),
                'severity' => ucfirst($issue->priority ?? 'medium'),
                'type' => $issue->type ?? 'General',
                'category' => ucfirst($issue->category ?? 'Unknown'),
                'sector' => $issue->sector ?? 'N/A',
                'subsector' => $issue->subsector ?? 'N/A',
                'agent' => $issue->submittedByAgent?->user?->name ?? 'Unknown',
                'officer' => $issue->assignedOfficer?->user?->name ?? 'Unassigned',
                'people' => $issue->people_affected ?? 0,
                'budget' => $issue->estimated_budget ?? 0,
                'created' => $issue->created_at ? Carbon::parse($issue->created_at)->format('Y-m-d H:i') : null,
                'resolved' => $issue->resolved_at ? Carbon::parse($issue->resolved_at)->format('Y-m-d H:i') : null,
                'community' => $issue->location ?? 'Unknown',
                'smaller' => $issue->smaller_community ?? 'N/A',
                'suburb' => $issue->suburb ?? 'N/A',
                'cottage' => $issue->cottage ?? 'N/A',
            ];

            foreach ($columns as $col) {
                $row[$col] = $columnMap[$col] ?? null;
            }
            return $row;
        })->toArray();

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Generate Projects report
     */
    private function generateProjectsReport(array $columns, array $filters, string $dateRange, int $page, int $limit): array
    {
        $query = Project::query();

        if (!empty($filters['status']) && $filters['status'] !== 'any') {
            $query->where('status', $filters['status']);
        }

        $this->applyDateRange($query, $dateRange);

        $total = $query->count();
        $projects = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = $projects->map(function ($project) use ($columns) {
            $row = [];
            $columnMap = [
                'id' => $project->id,
                'title' => $project->title,
                'status' => ucfirst($project->status ?? 'unknown'),
                'sector' => $project->sector ?? 'N/A',
                'budget' => $project->budget ?? 0,
                'created' => $project->created_at ? Carbon::parse($project->created_at)->format('Y-m-d') : null,
                'community' => $project->location ?? 'Unknown',
            ];

            foreach ($columns as $col) {
                $row[$col] = $columnMap[$col] ?? null;
            }
            return $row;
        })->toArray();

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Generate Users report
     */
    private function generateUsersReport(array $columns, array $filters, string $dateRange, int $page, int $limit): array
    {
        $query = User::query();

        if (!empty($filters['status']) && $filters['status'] !== 'any') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['role']) && $filters['role'] !== 'any') {
            $query->where('role', $filters['role']);
        }

        $this->applyDateRange($query, $dateRange);

        $total = $query->count();
        $users = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = $users->map(function ($user) use ($columns) {
            $row = [];
            $columnMap = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => ucfirst($user->role ?? 'user'),
                'status' => ucfirst($user->status ?? 'active'),
                'created' => $user->created_at ? Carbon::parse($user->created_at)->format('Y-m-d') : null,
            ];

            foreach ($columns as $col) {
                $row[$col] = $columnMap[$col] ?? null;
            }
            return $row;
        })->toArray();

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Apply date range filter to query
     */
    private function applyDateRange($query, string $dateRange): void
    {
        $now = Carbon::now();
        
        switch ($dateRange) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;
            case 'week':
                $query->where('created_at', '>=', $now->copy()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', $now->copy()->startOfMonth());
                break;
            case 'quarter':
                $query->where('created_at', '>=', $now->copy()->subMonths(3));
                break;
            case 'year':
                $query->where('created_at', '>=', $now->copy()->startOfYear());
                break;
            // 'all' or default - no filter
        }
    }
}

