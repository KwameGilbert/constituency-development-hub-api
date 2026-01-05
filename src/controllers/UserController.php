<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Officer;
use App\Models\TaskForce;
use App\Models\WebAdmin;
use App\Models\IssueReport;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Carbon\Carbon;

/**
 * UserController
 * 
 * Handles user management operations for admins:
 * - GET /admin/users - List all users with filtering and pagination
 * - GET /admin/users/:id - Get single user details
 * - POST /admin/users - Create new user
 * - PUT /admin/users/:id - Update user details
 * - DELETE /admin/users/:id - Delete user
 * - PUT /admin/users/:id/role - Update user role
 * - PUT /admin/users/:id/status - Update user status
 * - GET /admin/users/stats - Get user statistics
 */
class UserController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * List all users with filtering and pagination
     * GET /v1/admin/users
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $page = (int)($queryParams['page'] ?? 1);
            $limit = min((int)($queryParams['limit'] ?? 20), 100);
            $role = $queryParams['role'] ?? null;
            $status = $queryParams['status'] ?? null;
            $search = $queryParams['search'] ?? null;

            $query = User::query();

            // Filter by role
            if ($role && in_array($role, ['admin', 'web_admin', 'officer', 'agent', 'task_force'])) {
                $query->where('role', $role);
            }

            // Filter by status
            if ($status && in_array($status, ['active', 'inactive', 'suspended', 'pending'])) {
                $query->where('status', $status);
            }

            // Search by name or email
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $users = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format users with additional statistics
            $formattedUsers = $users->map(function ($user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->status,
                    'avatar' => $user->avatar,
                    'location' => $this->getUserLocation($user),
                    'created_at' => $user->created_at->toISOString(),
                    'last_login' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                ];

                // Add role-specific stats
                if ($user->role === 'agent' || $user->role === 'officer') {
                    $stats = $this->getUserIssueStats($user);
                    $userData['issues_assigned'] = $stats['assigned'];
                    $userData['issues_resolved'] = $stats['resolved'];
                }

                return $userData;
            });

            return ResponseHelper::success($response, 'Users retrieved successfully', [
                'users' => $formattedUsers,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch users', 500, $e->getMessage());
        }
    }

    /**
     * Get single user details
     * GET /v1/admin/users/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            $profile = $user->getFullProfile();

            // Add statistics
            $stats = $this->getUserIssueStats($user);
            $profile['statistics'] = [
                'total_issues' => $stats['total'],
                'resolved_issues' => $stats['resolved'],
                'pending_issues' => $stats['pending'],
                'in_progress' => $stats['in_progress'],
            ];

            // Add permissions (based on role)
            $profile['permissions'] = $this->getRolePermissions($user->role);

            return ResponseHelper::success($response, 'User retrieved successfully', [
                'user' => $profile
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch user', 500, $e->getMessage());
        }
    }

    /**
     * Create new user (Admin)
     * POST /v1/admin/users
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            $requestUser = $request->getAttribute('user');

            // Validate required fields
            $requiredFields = ['name', 'email', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ResponseHelper::error($response, "Field '$field' is required", 400);
                }
            }

            // Validate role
            $validRoles = ['admin', 'web_admin', 'officer', 'agent', 'task_force'];
            if (!in_array($data['role'], $validRoles)) {
                return ResponseHelper::error($response, 'Invalid role specified', 400);
            }

            // Check if email already exists
            if (User::emailExists($data['email'])) {
                return ResponseHelper::error($response, 'Email already exists', 409);
            }

            // Validate password strength
            if (strlen($data['password']) < 8) {
                return ResponseHelper::error($response, 'Password must be at least 8 characters', 400);
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'status' => $data['status'] ?? 'active',
                'email_verified_at' => Carbon::now(), // Admin-created users are pre-verified
            ]);

            // Create role-specific profile
            $this->createRoleProfile($user, $data);

            // Log the action
            AuditLog::create([
                'user_id' => $requestUser->id,
                'action' => 'user_create',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode([
                    'created_user' => $user->email,
                    'role' => $user->role
                ]),
            ]);

            return ResponseHelper::success($response, 'User created successfully', [
                'user' => $user->getFullProfile()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create user', 500, $e->getMessage());
        }
    }

    /**
     * Update user details
     * PUT /v1/admin/users/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $requestUser = $request->getAttribute('user');

            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Allowed fields for update
            $allowedFields = ['name', 'phone', 'location', 'bio'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (!empty($updateData)) {
                $user->update($updateData);

                // Log the action
                AuditLog::create([
                    'user_id' => $requestUser->id,
                    'action' => 'user_update',
                    'entity_type' => 'user',
                    'entity_id' => $user->id,
                    'details' => json_encode([
                        'updated_fields' => array_keys($updateData)
                    ]),
                ]);
            }

            return ResponseHelper::success($response, 'User updated successfully', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'updated_at' => $user->updated_at->toISOString()
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user', 500, $e->getMessage());
        }
    }

    /**
     * Delete user
     * DELETE /v1/admin/users/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $requestUser = $request->getAttribute('user');

            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Prevent self-deletion
            if ((int)$id === $requestUser->id) {
                return ResponseHelper::error($response, 'You cannot delete your own account', 400);
            }

            // Log before deletion
            AuditLog::create([
                'user_id' => $requestUser->id,
                'action' => 'user_delete',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode([
                    'deleted_user' => $user->email,
                    'role' => $user->role
                ]),
            ]);

            $user->delete();

            return ResponseHelper::success($response, 'User deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete user', 500, $e->getMessage());
        }
    }

    /**
     * Update user role
     * PUT /v1/admin/users/{id}/role
     */
    public function updateRole(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $requestUser = $request->getAttribute('user');

            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            if (empty($data['role'])) {
                return ResponseHelper::error($response, 'Role is required', 400);
            }

            $validRoles = ['admin', 'web_admin', 'officer', 'agent', 'task_force'];
            if (!in_array($data['role'], $validRoles)) {
                return ResponseHelper::error($response, 'Invalid role specified', 400);
            }

            // Prevent self role change
            if ((int)$id === $requestUser->id) {
                return ResponseHelper::error($response, 'You cannot change your own role', 400);
            }

            $oldRole = $user->role;
            $user->update(['role' => $data['role']]);

            // Create new role profile if needed
            $this->createRoleProfile($user, []);

            // Log the action
            AuditLog::create([
                'user_id' => $requestUser->id,
                'action' => 'user_role_change',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode([
                    'old_role' => $oldRole,
                    'new_role' => $data['role']
                ]),
            ]);

            return ResponseHelper::success($response, 'User role updated successfully', [
                'user' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'updated_at' => $user->updated_at->toISOString()
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user role', 500, $e->getMessage());
        }
    }

    /**
     * Update user status (activate/deactivate/suspend)
     * PUT /v1/admin/users/{id}/status
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            $requestUser = $request->getAttribute('user');

            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            if (empty($data['status'])) {
                return ResponseHelper::error($response, 'Status is required', 400);
            }

            $validStatuses = ['active', 'inactive', 'suspended'];
            if (!in_array($data['status'], $validStatuses)) {
                return ResponseHelper::error($response, 'Invalid status. Allowed: active, inactive, suspended', 400);
            }

            // Prevent self status change
            if ((int)$id === $requestUser->id) {
                return ResponseHelper::error($response, 'You cannot change your own status', 400);
            }

            $oldStatus = $user->status;
            $user->update(['status' => $data['status']]);

            // Log the action
            AuditLog::create([
                'user_id' => $requestUser->id,
                'action' => 'user_status_change',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode([
                    'old_status' => $oldStatus,
                    'new_status' => $data['status'],
                    'reason' => $data['reason'] ?? null
                ]),
            ]);

            return ResponseHelper::success($response, 'User status updated successfully', [
                'user' => [
                    'id' => $user->id,
                    'status' => $user->status,
                    'updated_at' => $user->updated_at->toISOString()
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user status', 500, $e->getMessage());
        }
    }

    /**
     * Get user statistics by role
     * GET /v1/admin/users/stats
     */
    public function stats(Request $request, Response $response): Response
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('status', 'active')->count();
            $inactiveUsers = User::where('status', 'inactive')->count();
            $suspendedUsers = User::where('status', 'suspended')->count();

            // Users by role
            $byRole = [
                'admin' => User::where('role', 'admin')->count(),
                'web_admin' => User::where('role', 'web_admin')->count(),
                'officer' => User::where('role', 'officer')->count(),
                'agent' => User::where('role', 'agent')->count(),
                'task_force' => User::where('role', 'task_force')->count(),
            ];

            // Recent registrations (last 30 days)
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $recentRegistrations = User::where('created_at', '>=', $thirtyDaysAgo)->count();

            // Recent logins (last 30 days)
            $recentLogins = User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', $thirtyDaysAgo)
                ->count();

            return ResponseHelper::success($response, 'User statistics retrieved successfully', [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
                'suspended_users' => $suspendedUsers,
                'by_role' => $byRole,
                'recent_registrations' => $recentRegistrations,
                'last_30_days_logins' => $recentLogins,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve user statistics', 500, $e->getMessage());
        }
    }

    // =================== HELPER METHODS ===================

    /**
     * Get user's location from role profile
     */
    private function getUserLocation(User $user): ?string
    {
        switch ($user->role) {
            case 'agent':
                $agent = Agent::where('user_id', $user->id)->first();
                return $agent ? $agent->area : null;
            case 'officer':
                $officer = Officer::where('user_id', $user->id)->first();
                return $officer ? $officer->department : null;
            case 'task_force':
                $tf = TaskForce::where('user_id', $user->id)->first();
                return $tf ? $tf->specialization : null;
            default:
                return null;
        }
    }

    /**
     * Get issue statistics for a user
     */
    private function getUserIssueStats(User $user): array
    {
        $stats = ['total' => 0, 'assigned' => 0, 'resolved' => 0, 'pending' => 0, 'in_progress' => 0];

        if ($user->role === 'agent') {
            $agent = Agent::where('user_id', $user->id)->first();
            if ($agent) {
                $stats['total'] = IssueReport::where('reporter_id', $agent->id)->count();
                $stats['assigned'] = $stats['total'];
                $stats['resolved'] = IssueReport::where('reporter_id', $agent->id)
                    ->whereIn('status', ['resolved', 'closed'])->count();
                $stats['pending'] = IssueReport::where('reporter_id', $agent->id)
                    ->whereIn('status', ['pending', 'pending_review'])->count();
                $stats['in_progress'] = IssueReport::where('reporter_id', $agent->id)
                    ->where('status', 'in_progress')->count();
            }
        } elseif ($user->role === 'officer') {
            $officer = Officer::where('user_id', $user->id)->first();
            if ($officer) {
                $stats['total'] = IssueReport::where('assigned_officer_id', $officer->id)->count();
                $stats['assigned'] = $stats['total'];
                $stats['resolved'] = IssueReport::where('assigned_officer_id', $officer->id)
                    ->whereIn('status', ['resolved', 'closed'])->count();
                $stats['pending'] = IssueReport::where('assigned_officer_id', $officer->id)
                    ->whereIn('status', ['pending', 'pending_review'])->count();
                $stats['in_progress'] = IssueReport::where('assigned_officer_id', $officer->id)
                    ->where('status', 'in_progress')->count();
            }
        }

        return $stats;
    }

    /**
     * Get permissions for a role
     */
    private function getRolePermissions(string $role): array
    {
        $permissions = [
            'admin' => ['full_access'],
            'web_admin' => ['manage_users', 'manage_content', 'view_reports', 'manage_issues'],
            'officer' => ['view_issues', 'update_issues', 'create_reports', 'manage_agents'],
            'agent' => ['create_issues', 'view_issues', 'update_own_issues'],
            'task_force' => ['view_assigned_issues', 'create_assessments', 'submit_resolutions'],
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Create role-specific profile for a user
     */
    private function createRoleProfile(User $user, array $data): void
    {
        switch ($user->role) {
            case 'web_admin':
                if (!WebAdmin::where('user_id', $user->id)->exists()) {
                    WebAdmin::create([
                        'user_id' => $user->id,
                        'department' => $data['department'] ?? 'Administration',
                        'permissions' => json_encode(['manage_content', 'manage_users']),
                    ]);
                }
                break;

            case 'officer':
                if (!Officer::where('user_id', $user->id)->exists()) {
                    Officer::create([
                        'user_id' => $user->id,
                        'badge_number' => $data['badge_number'] ?? 'OFF-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                        'department' => $data['department'] ?? 'Field Operations',
                    ]);
                }
                break;

            case 'agent':
                if (!Agent::where('user_id', $user->id)->exists()) {
                    Agent::create([
                        'user_id' => $user->id,
                        'agent_code' => $data['agent_code'] ?? 'AGT-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                        'area' => $data['location'] ?? 'Unassigned',
                    ]);
                }
                break;

            case 'task_force':
                if (!TaskForce::where('user_id', $user->id)->exists()) {
                    TaskForce::create([
                        'user_id' => $user->id,
                        'member_id' => $data['member_id'] ?? 'TF-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                        'specialization' => $data['specialization'] ?? 'General',
                    ]);
                }
                break;
        }
    }
}
