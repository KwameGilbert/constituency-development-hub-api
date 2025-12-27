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
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * TaskForceController
 * 
 * Handles Task Force member management and their workflow operations.
 */
class TaskForceController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
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
            $data = $request->getParsedBody();

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
                'profile_image' => $data['profile_image'] ?? null,
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

            $data = $request->getParsedBody();

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
                'profile_image' => $data['profile_image'] ?? $member->profile_image,
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
     * Get task force member's profile
     * GET /api/task-force/profile
     */
    public function profile(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = TaskForce::with('user')->where('user_id', $user->id)->first();

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
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
            $member = TaskForce::findByUserId($user->id);

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
     * Start assessment on an issue
     * POST /api/task-force/issues/{id}/start-assessment
     */
    public function startAssessment(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $member = TaskForce::findByUserId($user->id);

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
            $member = TaskForce::findByUserId($user->id);

            if (!$member) {
                return ResponseHelper::error($response, 'Task force profile not found', 404);
            }

            if (!$member->canAssessIssues()) {
                return ResponseHelper::error($response, 'You do not have permission to assess issues', 403);
            }

            $issue = IssueReport::where('id', $args['id'])
                ->where('assigned_task_force_id', $member->id)
                ->first();

            if (!$issue) {
                return ResponseHelper::error($response, 'Issue not assigned to you', 404);
            }

            $data = $request->getParsedBody();

            // Validation
            if (empty($data['assessment_summary'])) {
                return ResponseHelper::error($response, 'Assessment summary is required', 400);
            }

            // Create assessment report
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
                'images' => $data['images'] ?? null,
                'documents' => $data['documents'] ?? null,
                'location_verified' => $data['location_verified'] ?? null,
                'gps_coordinates' => $data['gps_coordinates'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'status' => IssueAssessmentReport::STATUS_SUBMITTED,
            ]);

            // Update issue status
            $issue->markAssessmentSubmitted();

            // Increment member's assessment count
            $member->incrementAssessments();
            $member->updateLastActive();

            return ResponseHelper::success($response, 'Assessment report submitted successfully', [
                'assessment' => $assessment->toPublicArray(),
                'issue' => $issue->fresh()->toFullArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to submit assessment', 500, $e->getMessage());
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
            $member = TaskForce::findByUserId($user->id);

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
            $member = TaskForce::findByUserId($user->id);

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

            $data = $request->getParsedBody();

            // Validation
            if (empty($data['resolution_summary'])) {
                return ResponseHelper::error($response, 'Resolution summary is required', 400);
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
                'before_images' => $data['before_images'] ?? null,
                'after_images' => $data['after_images'] ?? null,
                'documents' => $data['documents'] ?? null,
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
     * Generate random password
     */
    private function generatePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
}
