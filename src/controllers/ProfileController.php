<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\IssueReport;
use App\Helper\ResponseHelper;
use App\Services\UploadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;
use Carbon\Carbon;

/**
 * ProfileController
 * 
 * Handles user profile management and account settings:
 * - GET /profile - Get current user profile
 * - PUT /profile - Update profile
 * - POST /profile/avatar - Upload avatar
 * - PUT /profile/password - Change password
 * - GET /profile/activity - Get activity history
 */
class ProfileController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Get current user profile
     * GET /v1/profile
     */
    public function show(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $user = User::find($requestUser->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Get full profile with role-specific data
            $profile = $user->getFullProfile();

            // Add preferences (stored as JSON in preferences column if exists)
            $preferences = [
                'email_notifications' => true,
                'sms_notifications' => false,
                'language' => 'en',
                'timezone' => 'Africa/Accra'
            ];

            // If user has preferences stored, decode them
            if (!empty($user->preferences)) {
                $storedPrefs = json_decode($user->preferences, true);
                if (is_array($storedPrefs)) {
                    $preferences = array_merge($preferences, $storedPrefs);
                }
            }

            $profile['preferences'] = $preferences;

            return ResponseHelper::success($response, 'Profile retrieved successfully', [
                'user' => $profile
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve profile', 500, $e->getMessage());
        }
    }

    /**
     * Update profile
     * PUT /v1/profile
     */
    public function update(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $user = User::find($requestUser->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            $data = $request->getParsedBody() ?? [];

            // Allowed fields for profile update
            $allowedFields = ['name', 'phone', 'bio'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Handle preferences separately (store as JSON)
            if (isset($data['preferences']) && is_array($data['preferences'])) {
                $currentPrefs = [];
                if (!empty($user->preferences)) {
                    $currentPrefs = json_decode($user->preferences, true) ?? [];
                }
                $updateData['preferences'] = json_encode(array_merge($currentPrefs, $data['preferences']));
            }

            if (!empty($updateData)) {
                $user->update($updateData);

                // Log the activity
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'profile_update',
                    'entity_type' => 'user',
                    'entity_id' => $user->id,
                    'details' => json_encode(['updated_fields' => array_keys($updateData)]),
                ]);
            }

            return ResponseHelper::success($response, 'Profile updated successfully', [
                'user' => $user->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update profile', 500, $e->getMessage());
        }
    }

    /**
     * Upload avatar image
     * POST /v1/profile/avatar
     */
    public function uploadAvatar(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $user = User::find($requestUser->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            $uploadedFiles = $request->getUploadedFiles();
            $avatarFile = $uploadedFiles['avatar'] ?? null;

            if (!$avatarFile instanceof UploadedFileInterface || $avatarFile->getError() !== UPLOAD_ERR_OK) {
                return ResponseHelper::error($response, 'No valid avatar file uploaded', 400);
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $mimeType = $avatarFile->getClientMediaType();
            if (!in_array($mimeType, $allowedTypes)) {
                return ResponseHelper::error($response, 'Invalid file type. Allowed: JPEG, PNG, WebP', 400);
            }

            // Validate file size (max 5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($avatarFile->getSize() > $maxSize) {
                return ResponseHelper::error($response, 'File size exceeds 5MB limit', 400);
            }

            // Delete old avatar if exists
            if (!empty($user->avatar)) {
                $this->uploadService->deleteFile($user->avatar);
            }

            // Upload new avatar
            $avatarUrl = $this->uploadService->uploadFile($avatarFile, 'avatars');

            // Update user avatar
            $user->update(['avatar' => $avatarUrl]);

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'avatar_upload',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode(['new_avatar' => $avatarUrl]),
            ]);

            return ResponseHelper::success($response, 'Avatar uploaded successfully', [
                'avatar' => $avatarUrl
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to upload avatar', 500, $e->getMessage());
        }
    }

    /**
     * Change password
     * PUT /v1/profile/password
     */
    public function changePassword(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $user = User::find($requestUser->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            $data = $request->getParsedBody() ?? [];

            // Validate required fields
            $requiredFields = ['current_password', 'new_password', 'new_password_confirmation'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ResponseHelper::error($response, "Field '$field' is required", 400);
                }
            }

            // Verify current password
            if (!password_verify($data['current_password'], $user->password)) {
                return ResponseHelper::error($response, 'Current password is incorrect', 400);
            }

            // Check password confirmation
            if ($data['new_password'] !== $data['new_password_confirmation']) {
                return ResponseHelper::error($response, 'New password and confirmation do not match', 400);
            }

            // Validate password strength (min 8 characters)
            if (strlen($data['new_password']) < 8) {
                return ResponseHelper::error($response, 'New password must be at least 8 characters', 400);
            }

            // Update password (model will auto-hash)
            $user->update(['password' => $data['new_password']]);

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'password_change',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'details' => json_encode(['changed_at' => Carbon::now()->toISOString()]),
            ]);

            return ResponseHelper::success($response, 'Password changed successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to change password', 500, $e->getMessage());
        }
    }

    /**
     * Get activity history
     * GET /v1/profile/activity
     */
    public function activity(Request $request, Response $response): Response
    {
        try {
            $requestUser = $request->getAttribute('user');
            $queryParams = $request->getQueryParams();

            $page = (int)($queryParams['page'] ?? 1);
            $limit = min((int)($queryParams['limit'] ?? 20), 100);

            // Get user's audit logs
            $query = AuditLog::where('user_id', $requestUser->id)
                ->orderBy('created_at', 'desc');

            $total = $query->count();
            $activities = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format activities
            $formattedActivities = $activities->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'details' => json_decode($log->details, true),
                    'ip_address' => $log->ip_address ?? null,
                    'user_agent' => $log->user_agent ?? null,
                    'created_at' => $log->created_at->toISOString(),
                ];
            });

            return ResponseHelper::success($response, 'Activity history retrieved successfully', [
                'activities' => $formattedActivities,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve activity history', 500, $e->getMessage());
        }
    }
}
