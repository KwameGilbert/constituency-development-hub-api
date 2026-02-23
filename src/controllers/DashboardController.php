<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Officer;
use App\Models\TaskForce;
use App\Models\IssueReport;
use App\Models\IssueAssessmentReport;
use App\Models\IssueResolutionReport;
use App\Models\Project;
use App\Models\BlogPost;
use App\Models\ConstituencyEvent;
use App\Models\HeroSlide;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Carbon\Carbon;



/**
 * DashboardController
 * 
 * Provides role-specific dashboard statistics:
 * - GET /admin/dashboard/stats - Admin dashboard stats
 * - GET /officer/dashboard/stats - Officer dashboard stats
 * - GET /agent/dashboard/stats - Agent dashboard stats
 * - GET /task-force/dashboard/stats - Task Force dashboard stats
 */
class DashboardController
{
    /**
     * Get admin dashboard statistics
     * GET /v1/admin/dashboard/stats
     */
    public function adminStats(Request $request, Response $response): Response
    {
        try {
            // Overview counts
            $totalIssues = IssueReport::count();
            $activeUsers = User::where('status', 'active')->count();
            $totalProjects = Project::count();
            
            // Calculate total budget from projects
            $totalBudget = Project::sum('budget') ?? 0;

            // Calculate total budget from issues
            $totalIssuesBudget = IssueReport::sum('allocated_budget') ?? 0;

            // Total Funds Available
            $grandTotalBudget = $totalBudget + $totalIssuesBudget;

            // Users by role
            $usersByRole = [
                'admin' => User::where('role', 'admin')->count(),
                'web_admin' => User::where('role', 'web_admin')->count(),
                'officer' => User::where('role', 'officer')->count(),
                'agent' => User::where('role', 'agent')->count(),
                'task_force' => User::where('role', 'task_force')->count(),
            ];

            // Issues by status
            $issuesByStatus = [
                'pending_review' => IssueReport::where('status', 'pending_review')->count(),
                'assigned' => IssueReport::where('status', 'assigned')->count(),
                'in_progress' => IssueReport::where('status', 'in_progress')->count(),
                'resolved' => IssueReport::where('status', 'resolved')->count(),
                'closed' => IssueReport::where('status', 'closed')->count(),
            ];

            // Projects by status
            $projectsByStatus = [
                'planning' => Project::where('status', 'planning')->count(),
                'ongoing' => Project::where('status', 'ongoing')->count(),
                'completed' => Project::where('status', 'completed')->count(),
                'on_hold' => Project::where('status', 'on_hold')->count(),
            ];

            // Content Stats (Blog, Events, Carousel)
            $contentStats = [
                'blog_posts' => BlogPost::count(),
                'events' => ConstituencyEvent::count(),
                'upcoming_events' => ConstituencyEvent::upcoming()->count(),
                'carousel_items' => HeroSlide::count(),
            ];

            // Recent activity stats (last 30 days)
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $recentIssues = IssueReport::where('created_at', '>=', $thirtyDaysAgo)->count();
            $recentProjects = Project::where('created_at', '>=', $thirtyDaysAgo)->count();
            $recentUsers = User::where('created_at', '>=', $thirtyDaysAgo)->count();

            return ResponseHelper::success($response, 'Dashboard statistics retrieved', [
                'overview' => [
                    'total_issues' => $totalIssues,
                    'active_users' => $activeUsers,
                    'total_projects' => $totalProjects,
                    'total_budget' => (float)$totalBudget,
                    'total_issues_budget' => (float)$totalIssuesBudget,
                    'grand_total_budget' => (float)$grandTotalBudget,
                ],
                'users_by_role' => $usersByRole,
                'issues' => $issuesByStatus,
                'projects' => $projectsByStatus,
                'content_stats' => $contentStats,
                'recent_activity' => [
                    'new_issues_30_days' => $recentIssues,
                    'new_projects_30_days' => $recentProjects,
                    'new_users_30_days' => $recentUsers,
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve dashboard statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get officer dashboard statistics
     * GET /v1/officer/dashboard/stats
     */
    public function officerStats(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $officer = Officer::where('user_id', $requestUser->id)->first();

            if (!$officer) {
                // Auto-create officer profile if missing (e.g. user created via SQL import or admin panel)
                try {
                    $officer = Officer::create([
                        'user_id' => $requestUser->id,
                        'employee_id' => Officer::generateEmployeeId(),
                        'title' => 'Officer',
                        'department' => 'Operations',
                        'can_manage_projects' => true,
                        'can_manage_reports' => true,
                        'can_manage_events' => false,
                        'can_publish_content' => false,
                    ]);
                } catch (Exception $createEx) {
                    // If auto-creation fails, return empty stats gracefully
                    return ResponseHelper::success($response, 'Officer dashboard statistics (No Profile)', [
                        'my_issues' => [
                            'total' => 0,
                            'pending_review' => 0,
                            'in_progress' => 0,
                            'resolved' => 0,
                        ],
                        'performance' => [
                            'average_review_time_hours' => 0,
                            'issues_reviewed_this_month' => 0,
                        ],
                        'team' => [
                            'total_agents' => 0,
                            'active_agents' => 0,
                        ]
                    ]);
                }
            }

            // Get all issues (matching OfficerReportsController scope)
            $myIssuesTotal = IssueReport::count();
            $pendingReview = IssueReport::whereIn('status', [
                IssueReport::STATUS_SUBMITTED,
                IssueReport::STATUS_UNDER_OFFICER_REVIEW
            ])->count();
            $inProgress = IssueReport::whereIn('status', [
                IssueReport::STATUS_FORWARDED_TO_ADMIN,
                IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE,
                IssueReport::STATUS_ASSESSMENT_IN_PROGRESS,
                IssueReport::STATUS_ASSESSMENT_SUBMITTED,
                IssueReport::STATUS_RESOURCES_ALLOCATED,
                IssueReport::STATUS_RESOLUTION_IN_PROGRESS,
                IssueReport::STATUS_RESOLUTION_SUBMITTED,
            ])->count();
            $resolved = IssueReport::whereIn('status', [
                IssueReport::STATUS_RESOLVED,
                IssueReport::STATUS_CLOSED
            ])->count();

            // Performance metrics (this month)
            $startOfMonth = Carbon::now()->startOfMonth();
            $issuesReviewedThisMonth = IssueReport::where('updated_at', '>=', $startOfMonth)
                ->whereIn('status', ['in_progress', 'resolved', 'closed'])
                ->count();

            // Average review time (in hours) - calculate from actual data
            $avgReviewTime = IssueReport::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->first()
                ->avg_hours;
            $avgReviewTimeHours = $avgReviewTime ? round((float)$avgReviewTime, 1) : 0;

            // Team info - get all agents
            $totalAgents = Agent::count();
            $activeAgents = Agent::whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })->count();

            return ResponseHelper::success($response, 'Officer dashboard statistics', [
                'my_issues' => [
                    'total' => $myIssuesTotal,
                    'pending_review' => $pendingReview,
                    'in_progress' => $inProgress,
                    'resolved' => $resolved,
                ],
                'performance' => [
                    'average_review_time_hours' => $avgReviewTimeHours,
                    'issues_reviewed_this_month' => $issuesReviewedThisMonth,
                ],
                'team' => [
                    'total_agents' => $totalAgents,
                    'active_agents' => $activeAgents,
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve officer dashboard statistics: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500, $e->getTraceAsString());
        }
    }

    /**
     * Get agent dashboard statistics
     * GET /v1/agent/dashboard/stats
     */
    public function agentStats(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $agent = Agent::where('user_id', $requestUser->id)->first();

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent profile not found', 404);
            }

            // Get issues reported by this agent
            $myIssuesTotal = IssueReport::where('submitted_by_agent_id', $agent->id)->count();
            
            $pending = IssueReport::where('submitted_by_agent_id', $agent->id)
                ->whereIn('status', [
                    IssueReport::STATUS_SUBMITTED,
                    IssueReport::STATUS_UNDER_OFFICER_REVIEW,
                    IssueReport::STATUS_FORWARDED_TO_ADMIN
                ])->count();
                
            $inProgress = IssueReport::where('submitted_by_agent_id', $agent->id)
                ->whereIn('status', [
                    IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE,
                    IssueReport::STATUS_ASSESSMENT_IN_PROGRESS,
                    IssueReport::STATUS_RESOURCES_ALLOCATED,
                    IssueReport::STATUS_RESOLUTION_IN_PROGRESS
                ])->count();
                
            $resolved = IssueReport::where('submitted_by_agent_id', $agent->id)
                ->whereIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])->count();

            // Performance metrics (this month)
            $startOfMonth = Carbon::now()->startOfMonth();
            $issuesHandledThisMonth = IssueReport::where('submitted_by_agent_id', $agent->id)
                ->where('created_at', '>=', $startOfMonth)
                ->count();

            // Average response time (placeholder - can be calculated from actual data)
            $avgResponseTimeHours = 2.5;

            // Satisfaction rating (placeholder - can be implemented with feedback system)
            $satisfactionRating = 4.7;

            return ResponseHelper::success($response, 'Agent dashboard statistics', [
                'my_issues' => [
                    'total' => $myIssuesTotal,
                    'pending' => $pending,
                    'in_progress' => $inProgress,
                    'resolved' => $resolved,
                ],
                'performance' => [
                    'average_response_time_hours' => $avgResponseTimeHours,
                    'issues_handled_this_month' => $issuesHandledThisMonth,
                    'satisfaction_rating' => $satisfactionRating,
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve agent dashboard statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get task force dashboard statistics
     * GET /v1/task-force/dashboard/stats
     */
    public function taskForceStats(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $taskForce = TaskForce::where('user_id', $requestUser->id)->first();

            if (!$taskForce) {
                return ResponseHelper::error($response, 'Task Force profile not found', 404);
            }

            // Get task force member's assigned issues
            $myAssignmentsTotal = IssueReport::where('assigned_task_force_id', $taskForce->id)->count();
            $pendingAssessment = IssueReport::where('assigned_task_force_id', $taskForce->id)
                ->where('status', 'assigned')->count();
            $inProgress = IssueReport::where('assigned_task_force_id', $taskForce->id)
                ->where('status', 'in_progress')->count();
            $completed = IssueReport::where('assigned_task_force_id', $taskForce->id)
                ->whereIn('status', ['resolved', 'closed'])->count();

            // Task force info
            $taskForceName = $taskForce->specialization ?? 'General Task Force';

            // Count all active task force members in the same specialization
            $totalMembers = TaskForce::where('specialization', $taskForce->specialization)
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })->count();

            // Active assignments across the task force group
            $activeAssignments = IssueReport::whereIn('assigned_task_force_id', 
                TaskForce::where('specialization', $taskForce->specialization)->pluck('id')
            )->whereIn('status', ['assigned', 'in_progress'])->count();

            // Team performance (this month)
            $startOfMonth = Carbon::now()->startOfMonth();
            $assignmentsCompletedThisMonth = IssueReport::where('assigned_task_force_id', $taskForce->id)
                ->where('updated_at', '>=', $startOfMonth)
                ->whereIn('status', ['resolved', 'closed'])
                ->count();

            // Average completion time (placeholder)
            $avgCompletionDays = 8.5;

            return ResponseHelper::success($response, 'Task force dashboard statistics', [
                'my_task_force' => [
                    'name' => $taskForceName,
                    'total_members' => $totalMembers,
                    'active_assignments' => $activeAssignments,
                ],
                'my_assignments' => [
                    'total' => $myAssignmentsTotal,
                    'pending_assessment' => $pendingAssessment,
                    'in_progress' => $inProgress,
                    'completed' => $completed,
                ],
                'team_performance' => [
                    'average_completion_days' => $avgCompletionDays,
                    'assignments_completed_this_month' => $assignmentsCompletedThisMonth,
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve task force dashboard statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get finance overview data
     * GET /v1/admin/dashboard/finance
     *
     * Returns all projects with budget/spent and all issues with
     * allocated_budget, assessment estimated_cost, and resolution actual_cost.
     */
    public function financeOverview(Request $request, Response $response): Response
    {
        try {
            // --- Projects with financial data ---
            $projects = Project::with('sector')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'slug' => $project->slug,
                        'location' => $project->location,
                        'status' => $project->status,
                        'progress_percent' => $project->progress_percent,
                        'budget' => (float)($project->budget ?? 0),
                        'spent' => (float)($project->spent ?? 0),
                        'start_date' => $project->start_date,
                        'end_date' => $project->end_date,
                        'sector' => $project->sector ? [
                            'id' => $project->sector->id,
                            'name' => $project->sector->name,
                        ] : null,
                        'contractor' => $project->contractor,
                        'created_at' => $project->created_at?->toDateTimeString(),
                    ];
                });

            // --- Issues with financial data (eager-load assessment + resolution) ---
            $issues = IssueReport::with(['assessmentReport', 'resolutionReport'])
                ->whereNotIn('status', [
                    IssueReport::STATUS_SUBMITTED,
                    IssueReport::STATUS_UNDER_OFFICER_REVIEW,
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($issue) {
                    $allocatedBudget = (float)($issue->allocated_budget ?? 0);
                    $estimatedCost = 0;
                    $actualCost = 0;

                    if ($issue->assessmentReport) {
                        $estimatedCost = (float)($issue->assessmentReport->estimated_cost ?? 0);
                    }
                    if ($issue->resolutionReport) {
                        $actualCost = (float)($issue->resolutionReport->actual_cost ?? 0);
                    }

                    return [
                        'id' => $issue->id,
                        'case_id' => $issue->case_id,
                        'title' => $issue->title,
                        'category' => $issue->category,
                        'location' => $issue->location,
                        'status' => $issue->status,
                        'priority' => $issue->priority,
                        'allocated_budget' => $allocatedBudget,
                        'estimated_cost' => $estimatedCost,
                        'actual_cost' => $actualCost,
                        'created_at' => $issue->created_at?->toDateTimeString(),
                    ];
                });

            // --- Summary totals ---
            $projectsTotalBudget = $projects->sum('budget');
            $projectsTotalSpent = $projects->sum('spent');
            $issuesTotalAllocated = $issues->sum('allocated_budget');
            $issuesTotalSpent = $issues->sum('actual_cost');

            return ResponseHelper::success($response, 'Finance overview fetched successfully', [
                'projects' => $projects->toArray(),
                'issues' => $issues->toArray(),
                'summary' => [
                    'projects_total_budget' => (float)$projectsTotalBudget,
                    'projects_total_spent' => (float)$projectsTotalSpent,
                    'issues_total_allocated' => (float)$issuesTotalAllocated,
                    'issues_total_spent' => (float)$issuesTotalSpent,
                    'grand_total_budget' => (float)($projectsTotalBudget + $issuesTotalAllocated),
                    'grand_total_spent' => (float)($projectsTotalSpent + $issuesTotalSpent),
                    'projects_count' => $projects->count(),
                    'issues_count' => $issues->count(),
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch finance overview', 500, $e->getMessage());
        }
    }
}
