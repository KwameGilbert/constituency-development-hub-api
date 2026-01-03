<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Officer;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use App\Services\UploadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

/**
 * AgentController
 * 
 * Handles agent management operations.
 */
class AgentController
{
    private AuthService $authService;
    private UploadService $uploadService;

    public function __construct(AuthService $authService, UploadService $uploadService)
    {
        $this->authService = $authService;
        $this->uploadService = $uploadService;
    }

    /**
     * Get all agents
     * GET /api/admin/agents
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $location = $params['location'] ?? null;
            $verified = isset($params['verified']) ? filter_var($params['verified'], FILTER_VALIDATE_BOOLEAN) : null;
            $supervisor = $params['supervisor'] ?? null;

            $query = Agent::with(['user', 'supervisor.user'])->orderBy('created_at', 'desc');

            if ($location) {
                $query->where('assigned_location', $location);
            }
            if ($verified !== null) {
                $query->where('id_verified', $verified);
            }
            if ($supervisor) {
                $query->where('supervisor_id', $supervisor);
            }

            $agents = $query->get();

            return ResponseHelper::success($response, 'Agents fetched successfully', [
                'agents' => $agents->map(fn($a) => $a->getFullProfile())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch agents', 500, $e->getMessage());
        }
    }

    /**
     * Get single agent
     * GET /api/admin/agents/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $agent = Agent::with(['user', 'supervisor.user'])->find($args['id']);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent not found', 404);
            }

            return ResponseHelper::success($response, 'Agent fetched successfully', [
                'agent' => $agent->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch agent', 500, $e->getMessage());
        }
    }

    /**
     * Create new agent
     * POST /api/admin/agents
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
                    $profileImageUrl = $this->uploadService->uploadFile($imageFile, 'image', 'agents');
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
                'role' => User::ROLE_AGENT,
                'status' => User::STATUS_PENDING,
                'email_verified' => false,
                'first_login' => true,
            ]);

            // Create agent profile
            $agent = Agent::create([
                'user_id' => $user->id,
                'agent_code' => $data['agent_code'] ?? Agent::generateAgentCode(),
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'assigned_communities' => $data['assigned_communities'] ?? null,
                'assigned_location' => $data['assigned_location'] ?? null,
                'can_submit_reports' => $data['can_submit_reports'] ?? true,
                'can_collect_data' => $data['can_collect_data'] ?? true,
                'can_register_residents' => $data['can_register_residents'] ?? false,
                'profile_image' => $profileImageUrl,
                'id_type' => $data['id_type'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'id_verified' => false,
                'address' => $data['address'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Agent created successfully', [
                'agent' => $agent->getFullProfile(),
                'generated_password' => isset($data['password']) ? null : $password,
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create agent', 500, $e->getMessage());
        }
    }

    /**
     * Update agent
     * PUT /api/admin/agents/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $agent = Agent::with('user')->find($args['id']);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Handle profile image upload
            $profileImageUrl = $data['profile_image'] ?? $agent->profile_image;
            $imageFile = $uploadedFiles['profile_image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $profileImageUrl = $this->uploadService->replaceFile($imageFile, $agent->profile_image, 'image', 'agents');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Profile image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Update user data
            if ($agent->user) {
                $userData = [];
                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['phone'])) $userData['phone'] = $data['phone'];
                if (isset($data['status'])) $userData['status'] = $data['status'];
                
                if (!empty($userData)) {
                    $agent->user->update($userData);
                }
            }

            // Update agent profile
            $agent->update([
                'supervisor_id' => $data['supervisor_id'] ?? $agent->supervisor_id,
                'assigned_communities' => $data['assigned_communities'] ?? $agent->assigned_communities,
                'assigned_location' => $data['assigned_location'] ?? $agent->assigned_location,
                'can_submit_reports' => $data['can_submit_reports'] ?? $agent->can_submit_reports,
                'can_collect_data' => $data['can_collect_data'] ?? $agent->can_collect_data,
                'can_register_residents' => $data['can_register_residents'] ?? $agent->can_register_residents,
                'profile_image' => $profileImageUrl,
                'id_type' => $data['id_type'] ?? $agent->id_type,
                'id_number' => $data['id_number'] ?? $agent->id_number,
                'address' => $data['address'] ?? $agent->address,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? $agent->emergency_contact_name,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? $agent->emergency_contact_phone,
            ]);

            return ResponseHelper::success($response, 'Agent updated successfully', [
                'agent' => $agent->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update agent', 500, $e->getMessage());
        }
    }

    /**
     * Delete agent
     * DELETE /api/admin/agents/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $agent = Agent::with('user')->find($args['id']);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent not found', 404);
            }

            // Delete user (will cascade to agent profile)
            if ($agent->user) {
                $agent->user->delete();
            }

            return ResponseHelper::success($response, 'Agent deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete agent', 500, $e->getMessage());
        }
    }

    /**
     * Verify agent ID
     * POST /api/admin/agents/{id}/verify
     */
    public function verify(Request $request, Response $response, array $args): Response
    {
        try {
            $agent = Agent::with('user')->find($args['id']);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent not found', 404);
            }

            $agent->markAsVerified();

            // Activate user account
            if ($agent->user) {
                $agent->user->update(['status' => User::STATUS_ACTIVE]);
            }

            return ResponseHelper::success($response, 'Agent verified successfully', [
                'agent' => $agent->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to verify agent', 500, $e->getMessage());
        }
    }

    /**
     * Get agent's submitted reports (for agent dashboard)
     * GET /api/agent/my-reports
     */
    public function myReports(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $agent = Agent::findByUserId($user->id);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent profile not found', 404);
            }

            $reports = $agent->submittedReports()
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success($response, 'Reports fetched successfully', [
                'reports' => $reports->map(fn($r) => $r->toPublicArray())->toArray(),
                'total_submitted' => $agent->reports_submitted,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch reports', 500, $e->getMessage());
        }
    }

    /**
     * Get agent profile (for agent dashboard)
     * GET /api/agent/profile
     */
    public function profile(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $agent = Agent::with(['user', 'supervisor.user'])->where('user_id', $user->id)->first();

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent profile not found', 404);
            }

            return ResponseHelper::success($response, 'Profile fetched successfully', [
                'agent' => $agent->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch profile', 500, $e->getMessage());
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
