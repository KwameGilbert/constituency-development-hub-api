<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\IssueReport;
use App\Models\Agent;
use App\Models\Officer;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;
use Exception;

/**
 * OfficerReportsController
 * 
 * Provides comprehensive reporting data for officer dashboard.
 */
class OfficerReportsController
{
    /**
     * Get summary statistics for reports dashboard
     * GET /api/officer/reports/summary
     */
    public function summary(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $officer = Officer::where('user_id', $user->id)->first();

            // Get base query - optionally filter by officer's assigned issues
            $baseQuery = IssueReport::query();

            $totalIssues = (clone $baseQuery)->count();
            $pendingIssues = (clone $baseQuery)->pending()->count();
            $resolvedIssues = (clone $baseQuery)->whereIn('status', [
                IssueReport::STATUS_RESOLVED,
                IssueReport::STATUS_CLOSED
            ])->count();

            // Calculate average resolution time for resolved issues
            $avgResolutionTime = IssueReport::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_days')
                ->first()
                ->avg_days ?? 0;

            return ResponseHelper::success($response, 'Summary statistics fetched successfully', [
                'total_issues' => $totalIssues,
                'pending_issues' => $pendingIssues,
                'resolved_issues' => $resolvedIssues,
                'avg_resolution_time' => round((float)$avgResolutionTime, 1),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch summary statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get issues breakdown by category and location
     * GET /api/officer/reports/breakdown
     */
    public function breakdown(Request $request, Response $response): Response
    {
        try {
            $total = IssueReport::count();

            // Issues by category
            $byCategory = IssueReport::select('category', DB::raw('COUNT(*) as count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) use ($total) {
                    return [
                        'name' => $item->category,
                        'count' => $item->count,
                        'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
                    ];
                });

            // Issues by location (electoral area)
            $byLocation = IssueReport::select('location', DB::raw('COUNT(*) as count'))
                ->whereNotNull('location')
                ->groupBy('location')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) use ($total) {
                    return [
                        'name' => $item->location,
                        'count' => $item->count,
                        'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0,
                    ];
                });

            return ResponseHelper::success($response, 'Breakdown data fetched successfully', [
                'issues_by_category' => $byCategory,
                'issues_by_location' => $byLocation,
                'total' => $total,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch breakdown data', 500, $e->getMessage());
        }
    }

    /**
     * Get recent activity feed
     * GET /api/officer/reports/recent-activity
     */
    public function recentActivity(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;

            $recentIssues = IssueReport::with(['submittedByAgent.user'])
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get()
                ->map(function ($issue) {
                    $agentName = null;
                    if ($issue->submittedByAgent && $issue->submittedByAgent->user) {
                        $agentName = $issue->submittedByAgent->user->name;
                    }

                    return [
                        'id' => $issue->id,
                        'case_id' => $issue->case_id,
                        'title' => $issue->title,
                        'status' => $issue->status,
                        'category' => $issue->category,
                        'agent_name' => $agentName,
                        'updated_at' => $issue->updated_at?->toDateTimeString(),
                        'formatted_date' => $issue->updated_at?->format('M d, Y H:i'),
                    ];
                });

            return ResponseHelper::success($response, 'Recent activity fetched successfully', [
                'activities' => $recentIssues,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch recent activity', 500, $e->getMessage());
        }
    }

    /**
     * Get monthly trends data for charts
     * GET /api/officer/reports/trends
     */
    public function trends(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $months = isset($params['months']) ? (int)$params['months'] : 12;

            // Get monthly trends for the last N months
            $trends = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                $monthLabel = $startDate->format('Y-m');

                $total = IssueReport::whereBetween('created_at', [$startDate, $endDate])->count();
                $resolved = IssueReport::whereBetween('resolved_at', [$startDate, $endDate])->count();

                $trends[] = [
                    'name' => $monthLabel,
                    'month' => $startDate->format('M Y'),
                    'total' => $total,
                    'resolved' => $resolved,
                ];
            }

            return ResponseHelper::success($response, 'Trends data fetched successfully', [
                'trends' => $trends,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch trends data: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500, $e->getMessage());
        }
    }

    /**
     * Get status distribution for pie chart
     * GET /api/officer/reports/status-distribution
     */
    public function statusDistribution(Request $request, Response $response): Response
    {
        try {
            $statusColors = [
                'submitted' => '#EAB308',
                'under_officer_review' => '#3B82F6',
                'forwarded_to_admin' => '#8B5CF6',
                'assigned_to_task_force' => '#06B6D4',
                'assessment_in_progress' => '#F97316',
                'assessment_submitted' => '#6B7280',
                'resources_allocated' => '#84CC16',
                'resolution_in_progress' => '#EF4444',
                'resolution_submitted' => '#EC4899',
                'resolved' => '#22C55E',
                'closed' => '#10B981',
                'rejected' => '#DC2626',
            ];

            $statusLabels = [
                'submitted' => 'Submitted',
                'under_officer_review' => 'Under Review',
                'forwarded_to_admin' => 'Forwarded',
                'assigned_to_task_force' => 'Assigned',
                'assessment_in_progress' => 'Assessment',
                'assessment_submitted' => 'Assessed',
                'resources_allocated' => 'Resources',
                'resolution_in_progress' => 'In Progress',
                'resolution_submitted' => 'Resolution Submitted',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
                'rejected' => 'Rejected',
            ];

            $distribution = IssueReport::select('status', DB::raw('COUNT(*) as value'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) use ($statusColors, $statusLabels) {
                    return [
                        'name' => $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)),
                        'value' => $item->value,
                        'color' => $statusColors[$item->status] ?? '#6B7280',
                        'status' => $item->status,
                    ];
                });

            return ResponseHelper::success($response, 'Status distribution fetched successfully', [
                'distribution' => $distribution,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch status distribution', 500, $e->getMessage());
        }
    }

    /**
     * Get top agent performance metrics
     * GET /api/officer/reports/agent-performance
     */
    public function agentPerformance(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;

            // Get agents with their issue statistics
            $agents = Agent::with('user')
                ->withCount(['submittedReports as issues_submitted'])
                ->orderByDesc('issues_submitted')
                ->limit($limit)
                ->get()
                ->map(function ($agent) {
                    // Count resolved issues submitted by this agent
                    $resolvedCount = IssueReport::where('submitted_by_agent_id', $agent->id)
                        ->whereIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])
                        ->count();

                    // Calculate average resolution time for this agent's issues
                    $avgTime = IssueReport::where('submitted_by_agent_id', $agent->id)
                        ->whereNotNull('resolved_at')
                        ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_days')
                        ->first()
                        ->avg_days;

                    $submitted = $agent->issues_submitted ?? 0;
                    $resolutionRate = $submitted > 0 ? round(($resolvedCount / $submitted) * 100, 1) : 0;

                    return [
                        'id' => $agent->id,
                        'name' => $agent->user->name ?? 'Unknown Agent',
                        'agent_code' => $agent->agent_code,
                        'issues_submitted' => $submitted,
                        'issues_resolved' => $resolvedCount,
                        'resolution_rate' => $resolutionRate,
                        'avg_resolution_time' => $avgTime ? round((float)$avgTime, 1) . ' days' : 'N/A',
                    ];
                });

            return ResponseHelper::success($response, 'Agent performance data fetched successfully', [
                'agents' => $agents,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch agent performance data', 500, $e->getMessage());
        }
    }

    /**
     * Get officer profile stats for activity overview
     * GET /api/officer/reports/profile-stats
     */
    public function profileStats(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $officer = Officer::where('user_id', $user->id)->first();

            // Get issue counts
            $totalIssues = IssueReport::count();
            $pendingReview = IssueReport::whereIn('status', [
                IssueReport::STATUS_SUBMITTED,
                IssueReport::STATUS_UNDER_OFFICER_REVIEW
            ])->count();
            $resolved = IssueReport::whereIn('status', [
                IssueReport::STATUS_RESOLVED,
                IssueReport::STATUS_CLOSED
            ])->count();

            // Get active agents count
            $activeAgents = Agent::whereHas('user', function($query) {
                $query->where('status', 'active');
            })->count();

            // Officer-specific stats if officer profile exists
            $officerData = null;
            if ($officer) {
                $officerData = [
                    'employee_id' => $officer->employee_id,
                    'department' => $officer->department,
                    'position' => $officer->position,
                    'assigned_locations' => $officer->assigned_locations,
                    'supervised_agents_count' => $officer->getSupervisedAgentsCount(),
                    'pending_reports_count' => $officer->getPendingReportsCount(),
                    'permissions' => [
                        'can_manage_projects' => $officer->can_manage_projects,
                        'can_manage_reports' => $officer->can_manage_reports,
                        'can_manage_events' => $officer->can_manage_events,
                        'can_publish_content' => $officer->can_publish_content,
                    ],
                ];
            }

            return ResponseHelper::success($response, 'Profile stats fetched successfully', [
                'activity' => [
                    'total_issues' => $totalIssues,
                    'pending_review' => $pendingReview,
                    'resolved' => $resolved,
                    'active_agents' => $activeAgents,
                ],
                'officer' => $officerData,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch profile stats', 500, $e->getMessage());
        }
    }
}
