<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\WebAdmin;
use App\Models\Officer;
use App\Models\Agent;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * WebAdminController
 * 
 * Handles web admin management operations.
 * Super Admins only.
 */
class WebAdminController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get all web admins
     * GET /api/admin/web-admins
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $admins = WebAdmin::with('user')->orderBy('created_at', 'desc')->get();

            return ResponseHelper::success($response, 'Web admins fetched successfully', [
                'admins' => $admins->map(fn($a) => $a->getFullProfile())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch web admins', 500, $e->getMessage());
        }
    }

    /**
     * Get single web admin
     * GET /api/admin/web-admins/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $admin = WebAdmin::with('user')->find($args['id']);

            if (!$admin) {
                return ResponseHelper::error($response, 'Web admin not found', 404);
            }

            return ResponseHelper::success($response, 'Web admin fetched successfully', [
                'admin' => $admin->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch web admin', 500, $e->getMessage());
        }
    }

    /**
     * Create new web admin
     * POST /api/admin/web-admins
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
                'role' => User::ROLE_WEB_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified' => true,
                'first_login' => true,
            ]);

            // Create admin profile
            $admin = WebAdmin::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'] ?? WebAdmin::generateEmployeeId(),
                'admin_level' => $data['admin_level'] ?? WebAdmin::LEVEL_ADMIN,
                'department' => $data['department'] ?? null,
                'permissions' => $data['permissions'] ?? null,
                'profile_image' => $data['profile_image'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Web admin created successfully', [
                'admin' => $admin->getFullProfile(),
                'generated_password' => isset($data['password']) ? null : $password,
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create web admin', 500, $e->getMessage());
        }
    }

    /**
     * Update web admin
     * PUT /api/admin/web-admins/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $admin = WebAdmin::with('user')->find($args['id']);

            if (!$admin) {
                return ResponseHelper::error($response, 'Web admin not found', 404);
            }

            $data = $request->getParsedBody();

            // Update user data
            if ($admin->user) {
                $userData = [];
                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['status'])) $userData['status'] = $data['status'];
                
                if (!empty($userData)) {
                    $admin->user->update($userData);
                }
            }

            // Update admin profile
            $admin->update([
                'admin_level' => $data['admin_level'] ?? $admin->admin_level,
                'department' => $data['department'] ?? $admin->department,
                'permissions' => $data['permissions'] ?? $admin->permissions,
                'profile_image' => $data['profile_image'] ?? $admin->profile_image,
                'notes' => $data['notes'] ?? $admin->notes,
            ]);

            return ResponseHelper::success($response, 'Web admin updated successfully', [
                'admin' => $admin->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update web admin', 500, $e->getMessage());
        }
    }

    /**
     * Delete web admin
     * DELETE /api/admin/web-admins/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $admin = WebAdmin::with('user')->find($args['id']);

            if (!$admin) {
                return ResponseHelper::error($response, 'Web admin not found', 404);
            }

            // Don't allow deleting the last super admin
            if ($admin->isSuperAdmin()) {
                $superAdminCount = WebAdmin::where('admin_level', WebAdmin::LEVEL_SUPER_ADMIN)->count();
                if ($superAdminCount <= 1) {
                    return ResponseHelper::error($response, 'Cannot delete the last super admin', 400);
                }
            }

            // Delete user (will cascade to admin profile)
            if ($admin->user) {
                $admin->user->delete();
            }

            return ResponseHelper::success($response, 'Web admin deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete web admin', 500, $e->getMessage());
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
