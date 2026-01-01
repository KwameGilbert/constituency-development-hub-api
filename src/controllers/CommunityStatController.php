<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CommunityStat;
use App\Models\WebAdmin;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * CommunityStatController
 * 
 * Handles CRUD operations for homepage statistics.
 * Web Admins only.
 */
class CommunityStatController
{
    /**
     * Get all active stats (Public)
     * GET /api/stats
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $stats = CommunityStat::active()->get();

            return ResponseHelper::success($response, 'Community stats fetched successfully', [
                'stats' => $stats->map(fn($s) => $s->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch community stats', 500, $e->getMessage());
        }
    }

    /**
     * Get all stats including inactive (Admin)
     * GET /api/admin/stats
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $stats = CommunityStat::orderBy('display_order')->get();

            return ResponseHelper::success($response, 'Community stats fetched successfully', [
                'stats' => $stats->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch community stats', 500, $e->getMessage());
        }
    }

    /**
     * Get single stat
     * GET /api/admin/stats/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $stat = CommunityStat::find($args['id']);

            if (!$stat) {
                return ResponseHelper::error($response, 'Community stat not found', 404);
            }

            return ResponseHelper::success($response, 'Community stat fetched successfully', [
                'stat' => $stat->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch community stat', 500, $e->getMessage());
        }
    }

    /**
     * Create new stat
     * POST /api/admin/stats
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['label'])) {
                return ResponseHelper::error($response, 'Label is required', 400);
            }
            if (empty($data['value'])) {
                return ResponseHelper::error($response, 'Value is required', 400);
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $stat = CommunityStat::create([
                'created_by' => $webAdmin->id ?? null,
                'label' => $data['label'],
                'value' => $data['value'],
                'icon' => $data['icon'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'status' => $data['status'] ?? CommunityStat::STATUS_ACTIVE,
            ]);

            return ResponseHelper::success($response, 'Community stat created successfully', [
                'stat' => $stat->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create community stat', 500, $e->getMessage());
        }
    }

    /**
     * Update stat
     * PUT /api/admin/stats/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $stat = CommunityStat::find($args['id']);

            if (!$stat) {
                return ResponseHelper::error($response, 'Community stat not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $stat->update([
                'updated_by' => $webAdmin->id ?? null,
                'label' => $data['label'] ?? $stat->label,
                'value' => $data['value'] ?? $stat->value,
                'icon' => $data['icon'] ?? $stat->icon,
                'display_order' => $data['display_order'] ?? $stat->display_order,
                'status' => $data['status'] ?? $stat->status,
            ]);

            return ResponseHelper::success($response, 'Community stat updated successfully', [
                'stat' => $stat->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update community stat', 500, $e->getMessage());
        }
    }

    /**
     * Delete stat
     * DELETE /api/admin/stats/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $stat = CommunityStat::find($args['id']);

            if (!$stat) {
                return ResponseHelper::error($response, 'Community stat not found', 404);
            }

            $stat->delete();

            return ResponseHelper::success($response, 'Community stat deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete community stat', 500, $e->getMessage());
        }
    }

    /**
     * Reorder stats
     * PUT /api/admin/stats/reorder
     */
    public function reorder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['order']) || !is_array($data['order'])) {
                return ResponseHelper::error($response, 'Order array is required', 400);
            }

            foreach ($data['order'] as $index => $statId) {
                CommunityStat::where('id', $statId)->update(['display_order' => $index]);
            }

            return ResponseHelper::success($response, 'Stats reordered successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder stats', 500, $e->getMessage());
        }
    }
}
