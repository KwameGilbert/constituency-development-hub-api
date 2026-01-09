<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Notification;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * NotificationController
 * 
 * Handles user notification operations:
 * - GET /notifications - Get user's notifications
 * - GET /notifications/unread-count - Get unread count
 * - PUT /notifications/:id/read - Mark notification as read
 * - PUT /notifications/read-all - Mark all as read
 * - DELETE /notifications/:id - Delete notification
 */
class NotificationController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get user's notifications
     * GET /v1/notifications
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $params = $request->getQueryParams();

            // Parse is_read filter
            $isRead = null;
            if (isset($params['is_read'])) {
                $isRead = filter_var($params['is_read'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $result = Notification::getForUserPaginated($user->id, [
                'is_read' => $isRead,
                'type' => $params['type'] ?? null,
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 20
            ]);

            $notifications = array_map(function ($notification) {
                return $notification->toApiResponse();
            }, $result['notifications']->all());

            return ResponseHelper::success($response, 'Notifications retrieved successfully', [
                'notifications' => $notifications,
                'pagination' => $result['pagination'],
                'unread_count' => Notification::getUnreadCount($user->id)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get unread count
     * GET /v1/notifications/unread-count
     */
    public function unreadCount(Request $request, Response $response): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $count = Notification::getUnreadCount($user->id);

            return ResponseHelper::success($response, 'Unread count retrieved successfully', [
                'unread_count' => $count
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to get unread count: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark notification as read
     * PUT /v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $id = (int) $args['id'];

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return ResponseHelper::error($response, 'Notification not found', 404);
            }

            $notification->markAsRead();

            return ResponseHelper::success($response, 'Notification marked as read', [
                'notification' => $notification->toApiResponse()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to mark as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark all notifications as read
     * PUT /v1/notifications/read-all
     */
    public function markAllAsRead(Request $request, Response $response): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $count = Notification::markAllAsReadForUser($user->id);

            return ResponseHelper::success($response, 'All notifications marked as read', [
                'marked_count' => $count,
                'unread_count' => 0
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to mark all as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete notification
     * DELETE /v1/notifications/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $id = (int) $args['id'];

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return ResponseHelper::error($response, 'Notification not found', 404);
            }

            $notification->delete();

            return ResponseHelper::success($response, 'Notification deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get notification by ID
     * GET /v1/notifications/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $id = (int) $args['id'];

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return ResponseHelper::error($response, 'Notification not found', 404);
            }

            // Mark as read when viewing
            $notification->markAsRead();

            return ResponseHelper::success($response, 'Notification retrieved successfully', [
                'notification' => $notification->toApiResponse()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a test notification (admin only, for testing)
     * POST /v1/admin/notifications/test
     */
    public function createTest(Request $request, Response $response): Response
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request);
            $data = $request->getParsedBody() ?? [];

            $targetUserId = $data['user_id'] ?? $user->id;

            $notification = Notification::createForUser(
                (int) $targetUserId,
                $data['type'] ?? Notification::TYPE_INFO,
                $data['title'] ?? 'Test Notification',
                $data['message'] ?? 'This is a test notification message.',
                $data['action_url'] ?? null,
                $data['action_text'] ?? null
            );

            return ResponseHelper::success($response, 'Test notification created', [
                'notification' => $notification->toApiResponse()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create notification: ' . $e->getMessage(), 500);
        }
    }
}
