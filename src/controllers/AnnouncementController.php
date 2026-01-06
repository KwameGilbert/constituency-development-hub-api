<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Announcement;
use App\Models\WebAdmin;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * AnnouncementController
 * 
 * Handles CRUD operations for announcements.
 */
class AnnouncementController
{
    /**
     * List all announcements
     * GET /v1/announcements
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 10);
            $status = $params['status'] ?? null;
            $category = $params['category'] ?? null;
            $priority = $params['priority'] ?? null;

            $query = Announcement::query();

            if ($status) {
                $query->where('status', $status);
            }
            if ($category) {
                $query->where('category', $category);
            }
            if ($priority) {
                $query->where('priority', $priority);
            }

            $total = $query->count();
            $announcements = $query
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Announcements retrieved', [
                'announcements' => $announcements->map(fn($a) => $a->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve announcements', 500, $e->getMessage());
        }
    }

    /**
     * Get public/active announcements (for public website)
     * GET /v1/announcements/public
     */
    public function publicList(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = (int) ($params['limit'] ?? 10);
            $category = $params['category'] ?? null;

            $query = Announcement::active();

            if ($category) {
                $query->where('category', $category);
            }

            $announcements = $query
                ->orderBy('is_pinned', 'desc')
                ->orderBy('priority', 'desc')
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Public announcements retrieved', [
                'announcements' => $announcements->map(fn($a) => $a->toPublicArray()),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve announcements', 500, $e->getMessage());
        }
    }

    /**
     * Get a single announcement by ID or slug
     * GET /v1/announcements/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];

            // Check if it's a numeric ID or a slug
            if (is_numeric($identifier)) {
                $announcement = Announcement::find((int) $identifier);
            } else {
                $announcement = Announcement::findBySlug($identifier);
            }

            if (!$announcement) {
                return ResponseHelper::error($response, 'Announcement not found', 404);
            }

            // Increment views
            $announcement->incrementViews();

            return ResponseHelper::success($response, 'Announcement retrieved', [
                'announcement' => $announcement->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve announcement', 500, $e->getMessage());
        }
    }

    /**
     * Create a new announcement
     * POST /v1/announcements
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                return ResponseHelper::error($response, 'Title and content are required', 400);
            }

            // Get web admin ID
            $webAdmin = WebAdmin::findByUserId($user->id);
            if (!$webAdmin) {
                return ResponseHelper::error($response, 'Unauthorized', 403);
            }

            // Generate slug
            $slug = Announcement::generateSlug($data['title']);

            $announcement = Announcement::create([
                'created_by' => $webAdmin->id,
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'],
                'category' => $data['category'] ?? 'general',
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'draft',
                'publish_date' => $data['publish_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'image' => $data['image'] ?? null,
                'attachment' => $data['attachment'] ?? null,
                'is_pinned' => $data['is_pinned'] ?? false,
            ]);

            // If status is published, set published_at
            if ($announcement->status === 'published') {
                $announcement->update(['published_at' => date('Y-m-d H:i:s')]);
            }

            return ResponseHelper::success($response, 'Announcement created', [
                'announcement' => $announcement->toPublicArray(),
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create announcement', 500, $e->getMessage());
        }
    }

    /**
     * Update an announcement
     * PUT /v1/announcements/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            $announcement = Announcement::find($id);
            if (!$announcement) {
                return ResponseHelper::error($response, 'Announcement not found', 404);
            }

            // Get web admin ID
            $webAdmin = WebAdmin::findByUserId($user->id);
            if (!$webAdmin) {
                return ResponseHelper::error($response, 'Unauthorized', 403);
            }

            $updateData = ['updated_by' => $webAdmin->id];

            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                // Regenerate slug if title changes
                if ($data['title'] !== $announcement->title) {
                    $updateData['slug'] = Announcement::generateSlug($data['title']);
                }
            }
            if (isset($data['content'])) $updateData['content'] = $data['content'];
            if (isset($data['category'])) $updateData['category'] = $data['category'];
            if (isset($data['priority'])) $updateData['priority'] = $data['priority'];
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
                // Set published_at if status changes to published
                if ($data['status'] === 'published' && $announcement->status !== 'published') {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }
            if (isset($data['publish_date'])) $updateData['publish_date'] = $data['publish_date'];
            if (isset($data['expiry_date'])) $updateData['expiry_date'] = $data['expiry_date'];
            if (isset($data['image'])) $updateData['image'] = $data['image'];
            if (isset($data['attachment'])) $updateData['attachment'] = $data['attachment'];
            if (isset($data['is_pinned'])) $updateData['is_pinned'] = $data['is_pinned'];

            $announcement->update($updateData);

            return ResponseHelper::success($response, 'Announcement updated', [
                'announcement' => $announcement->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update announcement', 500, $e->getMessage());
        }
    }

    /**
     * Delete an announcement
     * DELETE /v1/announcements/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $announcement = Announcement::find($id);
            if (!$announcement) {
                return ResponseHelper::error($response, 'Announcement not found', 404);
            }

            $announcement->delete();

            return ResponseHelper::success($response, 'Announcement deleted');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete announcement', 500, $e->getMessage());
        }
    }

    /**
     * Publish an announcement
     * POST /v1/announcements/{id}/publish
     */
    public function publish(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $announcement = Announcement::find($id);
            if (!$announcement) {
                return ResponseHelper::error($response, 'Announcement not found', 404);
            }

            $announcement->publish();

            return ResponseHelper::success($response, 'Announcement published', [
                'announcement' => $announcement->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to publish announcement', 500, $e->getMessage());
        }
    }

    /**
     * Archive an announcement
     * POST /v1/announcements/{id}/archive
     */
    public function archive(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $announcement = Announcement::find($id);
            if (!$announcement) {
                return ResponseHelper::error($response, 'Announcement not found', 404);
            }

            $announcement->archive();

            return ResponseHelper::success($response, 'Announcement archived', [
                'announcement' => $announcement->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to archive announcement', 500, $e->getMessage());
        }
    }
}
