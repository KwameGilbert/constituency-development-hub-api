<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\TaskForce;
use App\Models\IssueReport;
use App\Models\IssueAssessmentReport;
use App\Models\IssueResolutionReport;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use App\Services\UploadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;
use Exception;

/**
 * TaskForceController
 * 
 * Handles Task Force member management and their workflow operations.
 */
class TaskForceController
{
    private AuthService $authService;
    private UploadService $uploadService;

    public function __construct(AuthService $authService, UploadService $uploadService)
    {
        $this->authService = $authService;
        $this->uploadService = $uploadService;
    }

    /* -----------------------------------------------------------------
     |  Admin Routes - Task Force Management
     | -----------------------------------------------------------------
     */

    /**
     * Get all task force members (Admin)
     * GET /api/admin/task-force
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $specialization = $params['specialization'] ?? null;
            $verified = isset($params['verified']) ? filter_var($params['verified'], FILTER_VALIDATE_BOOLEAN) : null;

            $query = TaskForce::with('user')->orderBy('created_at', 'desc');

            if ($specialization) {
                $query->where('specialization', $specialization);
            }
            if ($verified !== null) {
                $query->where('id_verified', $verified);
            }

            $members = $query->get();

            return ResponseHelper::success($response, 'Task force members fetched successfully', [
                'members' => $members->map(fn($m) => $m->getFullProfile())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch task force members', 500, $e->getMessage());
        }
    }

    /**
     * Get single task force member (Admin)
     * GET /api/admin/task-force/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $member = TaskForce::with('user')->find($args['id']);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force member not found', 404);
            }

            return ResponseHelper::success($response, 'Task force member fetched successfully', [
                'member' => $member->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch task force member', 500, $e->getMessage());
        }
    }

    /**
     * Create new task force member (Admin)
     * POST /api/admin/task-force
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Validation
            if (empty($data['name'])) {
                return ResponseHelper::error($response, 'Name is required', 400);
            }
            if (empty($data['email'])) {
                return ResponseHelper::error($response, 'Email is required', 400);
            }
            if (User::emailExists($data['email'])) {
                return ResponseHelper::error($response, 'Email already exists', 400);
            }

            // Handle profile image upload
            $profileImageUrl = $data['profile_image'] ?? null;
            $imageFile = $uploadedFiles['profile_image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $profileImageUrl = $this->uploadService->uploadFile($imageFile, 'image', 'task-force');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Profile image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Generate password if not provided
            $password = $data['password'] ?? $this->generatePassword();

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $this->authService->hashPassword($password),
                'role' => User::ROLE_TASK_FORCE,
                'status' => User::STATUS_PENDING,
                'email_verified' => false,
                'first_login' => true,
            ]);

            // Create task force profile
            $member = TaskForce::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'] ?? TaskForce::generateEmployeeId(),
                'title' => $data['title'] ?? null,
                'specialization' => $data['specialization'] ?? TaskForce::SPEC_GENERAL,
                'assigned_sectors' => $data['assigned_sectors'] ?? null,
                'skills' => $data['skills'] ?? null,
                'can_assess_issues' => $data['can_assess_issues'] ?? true,
                'can_resolve_issues' => $data['can_resolve_issues'] ?? true,
                'can_request_resources' => $data['can_request_resources'] ?? false,
                'profile_image' => $profileImageUrl,
                'id_type' => $data['id_type'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Task force member created successfully', [
                'member' => $member->getFullProfile(),
                'generated_password' => isset($data['password']) ? null : $password,
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create task force member', 500, $e->getMessage());
        }
    }

    /**
     * Update task force member (Admin)
     * PUT /api/admin/task-force/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $member = TaskForce::with('user')->find($args['id']);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force member not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Handle profile image upload
            $profileImageUrl = $data['profile_image'] ?? $member->profile_image;
            $imageFile = $uploadedFiles['profile_image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $profileImageUrl = $this->uploadService->replaceFile($imageFile, $member->profile_image, 'image', 'task-force');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Profile image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Update user data
            if ($member->user) {
                $userData = [];
                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['status'])) $userData['status'] = $data['status'];
                
                if (!empty($userData)) {
                    $member->user->update($userData);
                }
            }

            // Update task force profile
            $member->update([
                'title' => $data['title'] ?? $member->title,
                'specialization' => $data['specialization'] ?? $member->specialization,
                'assigned_sectors' => $data['assigned_sectors'] ?? $member->assigned_sectors,
                'skills' => $data['skills'] ?? $member->skills,
                'can_assess_issues' => $data['can_assess_issues'] ?? $member->can_assess_issues,
                'can_resolve_issues' => $data['can_resolve_issues'] ?? $member->can_resolve_issues,
                'can_request_resources' => $data['can_request_resources'] ?? $member->can_request_resources,
                'profile_image' => $profileImageUrl,
                'id_type' => $data['id_type'] ?? $member->id_type,
                'id_number' => $data['id_number'] ?? $member->id_number,
                'address' => $data['address'] ?? $member->address,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? $member->emergency_contact_name,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? $member->emergency_contact_phone,
            ]);

            return ResponseHelper::success($response, 'Task force member updated successfully', [
                'member' => $member->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update task force member', 500, $e->getMessage());
        }
    }

    /**
     * Delete task force member (Admin)
     * DELETE /api/admin/task-force/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $member = TaskForce::with('user')->find($args['id']);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force member not found', 404);
            }

            // Delete user (will cascade to task force profile)
            if ($member->user) {
                $member->user->delete();
            }

            return ResponseHelper::success($response, 'Task force member deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete task force member', 500, $e->getMessage());
        }
    }

    /**
     * Verify task force member ID (Admin)
     * POST /api/admin/task-force/{id}/verify
     */
    public function verify(Request $request, Response $response, array $args): Response
    {
        try {
            $member = TaskForce::with('user')->find($args['id']);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force member not found', 404);
            }

            $member->markAsVerified();

            // Activate user account
            if ($member->user) {
                $member->user->update(['status' => User::STATUS_ACTIVE]);
            }

            return ResponseHelper::success($response, 'Task force member verified successfully', [
                'member' => $member->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to verify task force member', 500, $e->getMessage());
        }
    }

    /* -----------------------------------------------------------------
     |  Task Force Dashboard Routes
     | -----------------------------------------------------------------
     */

    /**
     * Get dashboard statistics
     * GET /api/task-force/dashboard
     */
    public function dashboard(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $userId = is_object($user) ? (int) $user->id : (int) ($user['id'] ?? 0);

            // Get task force profile
            $taskForce = TaskForce::where('user_id', $userId)->first();

            // Issues assigned to task force
            $pendingAssessment = IssueReport::where('status', IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE)->count();
            $inProgress = IssueReport::where('status', IssueReport::STATUS_ASSESSMENT_IN_PROGRESS)->count();
            $assessmentSubmitted = IssueReport::where('status', IssueReport::STATUS_ASSESSMENT_SUBMITTED)->count();
            $resolutionInProgress = IssueReport::where('status', IssueReport::STATUS_RESOLUTION_IN_PROGRESS)->count();
            $resolved = IssueReport::whereIn('status', [
                IssueReport::STATUS_RESOLVED,
                IssueReport::STATUS_CLOSED
            ])->count();

            // My assignments (if task force member)
            $myPending = 0;
            $myInProgress = 0;
            $myCompleted = 0;
            if ($taskForce) {
                $myPending = IssueReport::where('assigned_task_force_id', $taskForce->id)
                    ->where('status', IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE)
                    ->count();
                $myInProgress = IssueReport::where('assigned_task_force_id', $taskForce->id)
                    ->whereIn('status', [
                        IssueReport::STATUS_ASSESSMENT_IN_PROGRESS,
                        IssueReport::STATUS_RESOLUTION_IN_PROGRESS
                    ])
                    ->count();
                $myCompleted = IssueReport::where('assigned_task_force_id', $taskForce->id)
                    ->whereIn('status', [
                        IssueReport::STATUS_RESOLVED,
                        IssueReport::STATUS_CLOSED
                    ])
                    ->count();
            }

            // Team stats
            $totalTeamMembers = TaskForce::count();
            $activeMembers = TaskForce::whereHas('user', function($query) {
                $query->where('status', 'active');
            })->count();

            // Priority breakdown
            $urgentIssues = IssueReport::where('priority', 'urgent')
                ->whereNotIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])
                ->count();
            $highPriorityIssues = IssueReport::where('priority', 'high')
                ->whereNotIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])
                ->count();

            return ResponseHelper::success($response, 'Dashboard stats fetched successfully', [
                'overview' => [
                    'pending_assessment' => $pendingAssessment,
                    'assessment_in_progress' => $inProgress,
                    'assessment_submitted' => $assessmentSubmitted,
                    'resolution_in_progress' => $resolutionInProgress,
                    'resolved' => $resolved,
                ],
                'my_assignments' => [
                    'pending' => $myPending,
                    'in_progress' => $myInProgress,
                    'completed' => $myCompleted,
                ],
                'team' => [
                    'total_members' => $totalTeamMembers,
                    'active_members' => $activeMembers,
                ],
                'priority' => [
                    'urgent' => $urgentIssues,
                    'high' => $highPriorityIssues,
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch dashboard stats', 500, $e->getMessage());
        }
    }

    /**
     * Get team members with performance stats
     * GET /api/task-force/team
     */
    public function team(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            $specialization = $params['specialization'] ?? null;

            $query = TaskForce::with('user');

            if ($specialization) {
                $query->where('specialization', $specialization);
            }

            $members = $query->limit($limit)->get()->map(function ($member) {
                // Count assigned issues
                $assignedCount = IssueReport::where('assigned_task_force_id', $member->id)->count();
                $completedCount = IssueReport::where('assigned_task_force_id', $member->id)
                    ->whereIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])
                    ->count();
                $activeCount = IssueReport::where('assigned_task_force_id', $member->id)
                    ->whereNotIn('status', [IssueReport::STATUS_RESOLVED, IssueReport::STATUS_CLOSED])
                    ->count();

                $completionRate = $assignedCount > 0 ? round(($completedCount / $assignedCount) * 100, 1) : 0;

                return [
                    'id' => $member->id,
                    'name' => $member->user->name ?? 'Unknown',
                    'email' => $member->user->email ?? null,
                    'phone' => $member->user->phone ?? null,
                    'status' => $member->user->status ?? 'inactive',
                    'employee_id' => $member->employee_id,
                    'title' => $member->title,
                    'specialization' => $member->specialization,
                    'skills' => $member->skills,
                    'id_verified' => $member->id_verified,
                    'assessments_completed' => $member->assessments_completed,
                    'resolutions_completed' => $member->resolutions_completed,
                    'assigned_count' => $assignedCount,
                    'completed_count' => $completedCount,
                    'active_count' => $activeCount,
                    'completion_rate' => $completionRate,
                    'last_active_at' => $member->last_active_at?->toDateTimeString(),
                ];
            });

            // Get specialization options
            $specializations = [
                ['value' => TaskForce::SPEC_INFRASTRUCTURE, 'label' => 'Infrastructure'],
                ['value' => TaskForce::SPEC_HEALTH, 'label' => 'Health'],
                ['value' => TaskForce::SPEC_EDUCATION, 'label' => 'Education'],
                ['value' => TaskForce::SPEC_WATER_SANITATION, 'label' => 'Water & Sanitation'],
                ['value' => TaskForce::SPEC_ELECTRICITY, 'label' => 'Electricity'],
                ['value' => TaskForce::SPEC_ROADS, 'label' => 'Roads'],
                ['value' => TaskForce::SPEC_GENERAL, 'label' => 'General'],
            ];

            return ResponseHelper::success($response, 'Team members fetched successfully', [
                'members' => $members,
                'total' => TaskForce::count(),
                'specializations' => $specializations,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch team members', 500, $e->getMessage());
        }
    }

    /**
     * Get task force member's profile
     * GET /api/task-force/profile
     */
    public function profile(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $userId = is_object($user) ? (int) $user->id : (int) ($user['id'] ?? 0);
            
            $dbUser = User::find($userId);
            if (!$dbUser) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            $member = TaskForce::with('user')->where('user_id', $userId)->first();

            if (!$member) {
                // If checking as admin/officer without task force profile, return basic user info
                return ResponseHelper::success($response, 'Profile fetched successfully', [
                    'member' => [
                        'id' => $dbUser->id,
                        'name' => $dbUser->name,
                        'email' => $dbUser->email,
                        'phone' => $dbUser->phone,
                        'status' => 'active',
                        'role' => $dbUser->role,
                        'assessments_completed' => 0,
                        'resolutions_completed' => 0,
                        'assigned_count' => 0,
                        'completion_rate' => 0,
                        'id_verified' => true,
                        // Add other required fields with defaults
                        'specialization' => null,
                        'employee_id' => null,
                        'title' => null,
                        'skills' => []
                    ]
                ]);
            }

            return ResponseHelper::success($response, 'Profile fetched successfully', [
                'member' => $member->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch profile', 500, $e->getMessage());
        }
    }

    /**
     * Get assigned issues for task force member
     * GET /api/task-force/issues
     */
    public function myIssues(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = $this->ensureTaskForceProfile($user);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            $issues = $member->assignedIssues()
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success($response, 'Assigned issues fetched successfully', [
                'issues' => $issues->map(fn($i) => $i->toFullArray())->toArray(),
                'stats' => [
                    'total_assigned' => $issues->count(),
                    'awaiting_assessment' => $issues->filter(fn($i) => $i->isAwaitingAssessment())->count(),
                    'awaiting_resolution' => $issues->filter(fn($i) => $i->isAwaitingResolution())->count(),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch assigned issues', 500, $e->getMessage());
        }
    }

    /**
     * Get all task force issues (Pool)
     * GET /api/task-force/all
     */
    public function issues(Request $request, Response $response): Response
    {
        try {
            // Get filter params
            $params = $request->getQueryParams();
            $status = $params['status'] ?? null;
            $priority = $params['priority'] ?? null;
            $category = $params['category'] ?? null;
            $limit = isset($params['limit']) ? (int) $params['limit'] : 20;
            $page = isset($params['page']) ? (int) $params['page'] : 1;

            // Base query - issues that are assigned to task force but not yet resolved?
            // Actually, "Pool" implies issues waiting for pickup.
            // But specific status filter 'assigned_to_task_force' is passed by frontend.
            $query = IssueReport::with(['submittedByAgent.user', 'assignedTaskForce.user']);

            if ($status) {
                $query->where('status', $status);
            } else {
                // Default: show relevant issues
                 $query->whereIn('status', [
                    IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE,
                    IssueReport::STATUS_ASSESSMENT_IN_PROGRESS,
                    IssueReport::STATUS_RESOURCES_ALLOCATED,
                    IssueReport::STATUS_RESOLUTION_IN_PROGRESS,
                ]);
            }

            if ($priority) {
                $query->where('priority', $priority);
            }

            if ($category) {
                $query->where('category', $category);
            }

            $total = $query->count();
            
            $issues = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            return ResponseHelper::success($response, 'Issues fetched successfully', [
                'issues' => $issues->map(fn($i) => $i->toFullArray())->toArray(),
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issues', 500, $e->getMessage());
        }
    }

    /**
     * Get single issue details
     * GET /api/task-force/issues/{id}
     */
    public function getIssue(Request $request, Response $response, array $args): Response
    {
        try {
            $issue = IssueReport::find($args['id']);

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not found', 404);
            }

            return ResponseHelper::success($response, 'Issue fetched successfully', [
                'issue' => $issue->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issue', 500, $e->getMessage());
        }
    }

    /**
     * Start assessment on an issue
     * POST /api/task-force/issues/{id}/start-assessment
     */
    public function startAssessment(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = $this->ensureTaskForceProfile($user);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            $issue = IssueReport::where('id', $args['id'])
                ->where('assigned_task_force_id', $member->id)
                ->first();

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not assigned to you', 404);
            }

            $issue->startAssessment();
            $member->updateLastActive();

            return ResponseHelper::success($response, 'Assessment started', [
                'issue' => $issue->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to start assessment', 500, $e->getMessage());
        }
    }

    /**
     * Submit assessment report
     * POST /api/task-force/issues/{id}/assessment
     */
    public function submitAssessment(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = $this->ensureTaskForceProfile($user);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            if (!$member->canAssessIssues()) {
                return ResponseHelper::error($response, 'You do not have permission to assess issues', 403);
            }

            // Fetch the issue
            $issue = IssueReport::find($args['id']);

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not found', 404);
            }

            // Check assignment - Allow Admins to assess any issue
            $userId = is_object($user) ? $user->id : ($user['id'] ?? 0);
            $dbUser = User::find($userId);
            $isAdmin = $dbUser && in_array($dbUser->role, [User::ROLE_ADMIN, User::ROLE_OFFICER, User::ROLE_WEB_ADMIN]);

            error_log("submitAssessment: User ID: $userId, Role: " . ($dbUser ? $dbUser->role : 'null') . ", isAdmin: " . ($isAdmin ? 'true' : 'false'));
            error_log("submitAssessment: Issue ID: {$issue->id}, assigned_task_force_id: " . ($issue->assigned_task_force_id ?? 'null') . ", member->id: {$member->id}");

            // Perform strict check only if issue is already assigned
            if (!$isAdmin && $issue->assigned_task_force_id && $issue->assigned_task_force_id != $member->id) {
                return ResponseHelper::error($response, 'Issue is assigned to another member', 403);
            }

            // Auto-assign if unassigned (especially for Admins testing)
            if (!$issue->assigned_task_force_id) {
                error_log("submitAssessment: Auto-assigning issue {$issue->id} to member {$member->id}");
                $issue->assigned_task_force_id = $member->id;
                $issue->save();
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Validation
            if (empty($data['assessment_summary'])) {
                return ResponseHelper::error($response, 'Assessment summary is required', 400);
            }

            // Handle images upload
            $imagesJson = $data['images'] ?? null;
            $imageFiles = $uploadedFiles['images'] ?? [];
            if (!empty($imageFiles)) {
                if (!is_array($imageFiles)) $imageFiles = [$imageFiles];
                try {
                    $uploadedImages = $this->uploadService->uploadMultipleFiles($imageFiles, 'image', 'assessments');
                    if (!empty($uploadedImages)) {
                        $imagesJson = json_encode($uploadedImages);
                    }
                } catch (Exception $e) {
                    error_log('Assessment images upload failed: ' . $e->getMessage());
                }
            }

            // Handle documents upload
            $documentsJson = $data['documents'] ?? null;
            $documentFiles = $uploadedFiles['documents'] ?? [];
            if (!empty($documentFiles)) {
                if (!is_array($documentFiles)) $documentFiles = [$documentFiles];
                try {
                    $uploadedDocs = $this->uploadService->uploadMultipleFiles($documentFiles, 'document', 'assessments');
                    if (!empty($uploadedDocs)) {
                        $documentsJson = json_encode($uploadedDocs);
                    }
                } catch (Exception $e) {
                    error_log('Assessment documents upload failed: ' . $e->getMessage());
                }
            }

            // Create assessment report
            error_log('submitAssessment: Creating assessment report record...');
            $assessment = IssueAssessmentReport::create([
                'issue_report_id' => $issue->id,
                'submitted_by' => $member->id,
                'assessment_summary' => $data['assessment_summary'],
                'findings' => $data['findings'] ?? null,
                'issue_confirmed' => $data['issue_confirmed'] ?? true,
                'severity' => $data['severity'] ?? IssueAssessmentReport::SEVERITY_MEDIUM,
                'estimated_cost' => $data['estimated_cost'] ?? null,
                'estimated_duration' => $data['estimated_duration'] ?? null,
                'required_resources' => $data['required_resources'] ?? null,
                'images' => $imagesJson,
                'documents' => $documentsJson,
                'location_verified' => $data['location_verified'] ?? null,
                'gps_coordinates' => $data['gps_coordinates'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'status' => IssueAssessmentReport::STATUS_SUBMITTED,
            ]);
            error_log('submitAssessment: Assessment report created. ID: ' . $assessment->id);

            // Update issue status
            $issue->markAssessmentSubmitted();
            error_log('submitAssessment: Issue status updated.');

            // Increment member's assessment count
            $member->incrementAssessments();
            $member->updateLastActive();

            return ResponseHelper::success($response, 'Assessment report submitted successfully', [
                'assessment' => $assessment->toPublicArray(),
                'issue' => $issue->fresh()->toFullArray()
            ], 201);
        } catch (\Throwable $e) {
            error_log('submitAssessment: Exception caught: ' . $e->getMessage());
            error_log('submitAssessment: Trace: ' . $e->getTraceAsString());
            return ResponseHelper::error($response, '[Debug] Failed to submit assessment: ' . $e->getMessage(), 500, $e->getMessage());
        }
    }

    /**
     * Start resolution work on an issue
     * POST /api/task-force/issues/{id}/start-resolution
     */
    public function startResolution(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = $this->ensureTaskForceProfile($user);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            $issue = IssueReport::where('id', $args['id'])
                ->where('assigned_task_force_id', $member->id)
                ->where('status', IssueReport::STATUS_RESOURCES_ALLOCATED)
                ->first();

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not ready for resolution or not assigned to you', 404);
            }

            $issue->startResolution();
            $member->updateLastActive();

            return ResponseHelper::success($response, 'Resolution work started', [
                'issue' => $issue->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to start resolution', 500, $e->getMessage());
        }
    }

    /**
     * Submit resolution report
     * POST /api/task-force/issues/{id}/resolution
     */
    public function submitResolution(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = $this->ensureTaskForceProfile($user);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            if (!$member->canResolveIssues()) {
                return ResponseHelper::error($response, 'You do not have permission to resolve issues', 403);
            }

            $issue = IssueReport::where('id', $args['id'])
                ->where('assigned_task_force_id', $member->id)
                ->first();

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not assigned to you', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Validation
            if (empty($data['resolution_summary'])) {
                return ResponseHelper::error($response, 'Resolution summary is required', 400);
            }

            // Handle before_images upload
            $beforeImagesJson = $data['before_images'] ?? null;
            $beforeImageFiles = $uploadedFiles['before_images'] ?? [];
            if (!empty($beforeImageFiles)) {
                if (!is_array($beforeImageFiles)) $beforeImageFiles = [$beforeImageFiles];
                try {
                    $uploadedBefore = $this->uploadService->uploadMultipleFiles($beforeImageFiles, 'image', 'resolutions/before');
                    if (!empty($uploadedBefore)) {
                        $beforeImagesJson = json_encode($uploadedBefore);
                    }
                } catch (Exception $e) {
                    error_log('Before images upload failed: ' . $e->getMessage());
                }
            }

            // Handle after_images upload
            $afterImagesJson = $data['after_images'] ?? null;
            $afterImageFiles = $uploadedFiles['after_images'] ?? [];
            if (!empty($afterImageFiles)) {
                if (!is_array($afterImageFiles)) $afterImageFiles = [$afterImageFiles];
                try {
                    $uploadedAfter = $this->uploadService->uploadMultipleFiles($afterImageFiles, 'image', 'resolutions/after');
                    if (!empty($uploadedAfter)) {
                        $afterImagesJson = json_encode($uploadedAfter);
                    }
                } catch (Exception $e) {
                    error_log('After images upload failed: ' . $e->getMessage());
                }
            }

            // Handle documents upload
            $documentsJson = $data['documents'] ?? null;
            $documentFiles = $uploadedFiles['documents'] ?? [];
            if (!empty($documentFiles)) {
                if (!is_array($documentFiles)) $documentFiles = [$documentFiles];
                try {
                    $uploadedDocs = $this->uploadService->uploadMultipleFiles($documentFiles, 'document', 'resolutions');
                    if (!empty($uploadedDocs)) {
                        $documentsJson = json_encode($uploadedDocs);
                    }
                } catch (Exception $e) {
                    error_log('Resolution documents upload failed: ' . $e->getMessage());
                }
            }

            // Create resolution report
            $resolution = IssueResolutionReport::create([
                'issue_report_id' => $issue->id,
                'submitted_by' => $member->id,
                'resolution_summary' => $data['resolution_summary'],
                'work_description' => $data['work_description'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'completion_date' => $data['completion_date'] ?? date('Y-m-d'),
                'actual_cost' => $data['actual_cost'] ?? null,
                'resources_used' => $data['resources_used'] ?? null,
                'before_images' => $beforeImagesJson,
                'after_images' => $afterImagesJson,
                'documents' => $documentsJson,
                'challenges_faced' => $data['challenges_faced'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
                'requires_followup' => $data['requires_followup'] ?? false,
                'followup_notes' => $data['followup_notes'] ?? null,
                'status' => IssueResolutionReport::STATUS_SUBMITTED,
            ]);

            // Update issue status
            $issue->markResolutionSubmitted();

            // Increment member's resolution count
            $member->incrementResolutions();
            $member->updateLastActive();

            return ResponseHelper::success($response, 'Resolution report submitted successfully', [
                'resolution' => $resolution->toPublicArray(),
                'issue' => $issue->fresh()->toFullArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to submit resolution', 500, $e->getMessage());
        }
    }

    /**
     * Get reports/analytics for task force
     * GET /api/task-force/reports
     */
    public function reports(Request $request, Response $response): Response
    {
        try {
            // Status distribution
            $statusCounts = IssueReport::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Priority distribution
            $priorityCounts = IssueReport::select('priority', DB::raw('COUNT(*) as count'))
                ->whereNotNull('priority')
                ->groupBy('priority')
                ->get()
                ->pluck('count', 'priority')
                ->toArray();

            // Category distribution
            $categoryCounts = IssueReport::select('category', DB::raw('COUNT(*) as count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->get()
                ->map(fn($item) => [
                    'name' => $item->category,
                    'count' => $item->count
                ])
                ->toArray();

            // Monthly trends (last 6 months)
            $trends = [];
            for ($i = 5; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                
                $submitted = IssueReport::whereBetween('created_at', [$startDate, $endDate])->count();
                $resolved = IssueReport::whereBetween('resolved_at', [$startDate, $endDate])->count();
                
                $trends[] = [
                    'month' => $startDate->format('M Y'),
                    'submitted' => $submitted,
                    'resolved' => $resolved,
                ];
            }

            // Top performers
            $topPerformers = TaskForce::with('user')
                ->orderByDesc('resolutions_completed')
                ->limit(5)
                ->get()
                ->map(fn($m) => [
                    'name' => $m->user?->name ?? 'Unknown',
                    'assessments' => $m->assessments_completed,
                    'resolutions' => $m->resolutions_completed,
                ]);

            // Average resolution time
            $avgResolutionDays = IssueReport::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_days')
                ->first()
                ->avg_days ?? 0;

            return ResponseHelper::success($response, 'Reports fetched successfully', [
                'status_distribution' => $statusCounts,
                'priority_distribution' => $priorityCounts,
                'category_distribution' => $categoryCounts,
                'monthly_trends' => $trends,
                'top_performers' => $topPerformers,
                'avg_resolution_days' => round((float)$avgResolutionDays, 1),
                'total_issues' => IssueReport::count(),
                'resolved_issues' => IssueReport::whereIn('status', [
                    IssueReport::STATUS_RESOLVED,
                    IssueReport::STATUS_CLOSED
                ])->count(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch reports', 500, $e->getMessage());
        }
    }

    /**
     * Generate random passphraseld
     */
    private function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Helper to get or auto-create task force profile
     */
    private function ensureTaskForceProfile($requestUser): ?TaskForce
    {
        $userId = is_object($requestUser) ? $requestUser->id : ($requestUser['id'] ?? 0);
        $member = TaskForce::findByUserId($userId);

        if ($member) {
            return $member;
        }

        // Auto-create for Admin/Officer or Task Force role with missing profile
        $dbUser = User::find($userId);
        
        if ($dbUser) {
            error_log("TaskForceController: Checking profile for User ID: {$dbUser->id}, Role: {$dbUser->role}");
            
            $allowedRoles = [
                User::ROLE_ADMIN, 
                User::ROLE_OFFICER, 
                User::ROLE_WEB_ADMIN,
                User::ROLE_TASK_FORCE
            ];

            if (in_array($dbUser->role, $allowedRoles)) {
                try {
                    error_log("TaskForceController: Auto-creating profile for User ID: {$dbUser->id}");
                    return TaskForce::create([
                        'user_id' => $dbUser->id,
                        'employee_id' => TaskForce::generateEmployeeId(),
                        'title' => ($dbUser->role === User::ROLE_TASK_FORCE) ? 'Task Force Member' : 'System Administrator',
                        'specialization' => TaskForce::SPEC_GENERAL,
                        'can_assess_issues' => true,
                        'can_resolve_issues' => true,
                        'can_request_resources' => true,
                        'id_verified' => true
                    ]);
                } catch (Exception $e) {
                    error_log("Failed to auto-create profile: " . $e->getMessage());
                }
            } else {
                error_log("TaskForceController: Role '{$dbUser->role}' not allowed for auto-creation.");
            }
        } else {
            error_log("TaskForceController: User not found for ID: $userId");
        }

        return null;
    }
}
