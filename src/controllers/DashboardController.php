<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Officer;
use App\Models\TaskForce;
use App\Models\IssueReport;
use App\Models\Project;
use App\Models\BlogPost;
use App\Models\ConstituencyEvent;
use App\Models\HeroSlide;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Carbon\Carbon;

// Helper function for current time
function now(): Carbon {
    return Carbon::now();
}

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
            $thirtyDaysAgo = now()->subDays(30);
            $recentIssues = IssueReport::where('created_at', '>=', $thirtyDaysAgo)->count();
            $recentProjects = Project::where('created_at', '>=', $thirtyDaysAgo)->count();
            $recentUsers = User::where('created_at', '>=', $thirtyDaysAgo)->count();

            return ResponseHelper::success($response, 'Dashboard statistics retrieved', [
                'overview' => [
                    'total_issues' => $totalIssues,
                    'active_users' => $activeUsers,
                    'total_projects' => $totalProjects,
                    'total_budget' => (float)$totalBudget,
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
                return ResponseHelper::error($response, 'Officer profile not found', 404);
            }

            // Get issues assigned to this officer for review
            $myIssuesTotal = IssueReport::where('assigned_officer_id', $officer->id)->count();
            $pendingReview = IssueReport::where('assigned_officer_id', $officer->id)
                ->where('status', 'pending_review')->count();
            $inProgress = IssueReport::where('assigned_officer_id', $officer->id)
                ->where('status', 'in_progress')->count();
            $resolved = IssueReport::where('assigned_officer_id', $officer->id)
                ->where('status', 'resolved')->count();

            // Performance metrics (this month)
            $startOfMonth = now()->startOfMonth();
            $issuesReviewedThisMonth = IssueReport::where('assigned_officer_id', $officer->id)
                ->where('updated_at', '>=', $startOfMonth)
                ->whereIn('status', ['in_progress', 'resolved', 'closed'])
                ->count();

            // Average review time (in hours) - approximate calculation
            $avgReviewTimeHours = 6.5; // Default placeholder, can be calculated from status history

            // Team info - get agents supervised by this officer
            $totalAgents = Agent::where('assigned_officer_id', $officer->id)->count();
            $activeAgents = Agent::where('assigned_officer_id', $officer->id)
                ->whereHas('user', function ($q) {
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
            return ResponseHelper::error($response, 'Failed to retrieve officer dashboard statistics', 500, $e->getMessage());
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
            $startOfMonth = now()->startOfMonth();
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
            $startOfMonth = now()->startOfMonth();
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
}
