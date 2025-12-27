<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NewsletterSubscriber;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * NewsletterController
 * 
 * Handles newsletter subscriptions.
 */
class NewsletterController
{
    /**
     * Subscribe to newsletter (Public)
     * POST /api/newsletter/subscribe
     */
    public function subscribe(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            // Validation
            if (empty($data['email'])) {
                return ResponseHelper::error($response, 'Email is required', 400);
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ResponseHelper::error($response, 'Invalid email address', 400);
            }

            $subscriber = NewsletterSubscriber::subscribe(
                $data['email'],
                $data['name'] ?? null,
                $data['phone'] ?? null
            );

            return ResponseHelper::success($response, 'Successfully subscribed to newsletter', [
                'subscriber' => [
                    'email' => $subscriber->email,
                    'status' => $subscriber->status,
                ]
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to subscribe', 500, $e->getMessage());
        }
    }

    /**
     * Unsubscribe from newsletter (Public)
     * POST /api/newsletter/unsubscribe
     */
    public function unsubscribe(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['email'])) {
                return ResponseHelper::error($response, 'Email is required', 400);
            }

            $subscriber = NewsletterSubscriber::findByEmail($data['email']);

            if (!$subscriber) {
                return ResponseHelper::error($response, 'Subscriber not found', 404);
            }

            $subscriber->unsubscribe();

            return ResponseHelper::success($response, 'Successfully unsubscribed from newsletter');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to unsubscribe', 500, $e->getMessage());
        }
    }

    /**
     * Get all subscribers (Admin)
     * GET /api/admin/newsletter
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $status = $params['status'] ?? null;

            $query = NewsletterSubscriber::orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $subscribers = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Subscribers fetched successfully', [
                'subscribers' => $subscribers->map(fn($s) => $s->toPublicArray())->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ],
                'stats' => [
                    'total' => NewsletterSubscriber::count(),
                    'active' => NewsletterSubscriber::active()->count(),
                    'unsubscribed' => NewsletterSubscriber::unsubscribed()->count(),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch subscribers', 500, $e->getMessage());
        }
    }

    /**
     * Export subscribers (Admin)
     * GET /api/admin/newsletter/export
     */
    public function export(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $status = $params['status'] ?? 'active';

            $query = NewsletterSubscriber::orderBy('created_at', 'desc');

            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'unsubscribed') {
                $query->unsubscribed();
            }

            $subscribers = $query->get(['email', 'name', 'phone', 'status', 'subscribed_at']);

            return ResponseHelper::success($response, 'Subscribers exported successfully', [
                'count' => $subscribers->count(),
                'subscribers' => $subscribers->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to export subscribers', 500, $e->getMessage());
        }
    }

    /**
     * Delete subscriber (Admin)
     * DELETE /api/admin/newsletter/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $subscriber = NewsletterSubscriber::find($args['id']);

            if (!$subscriber) {
                return ResponseHelper::error($response, 'Subscriber not found', 404);
            }

            $subscriber->delete();

            return ResponseHelper::success($response, 'Subscriber deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete subscriber', 500, $e->getMessage());
        }
    }
}
