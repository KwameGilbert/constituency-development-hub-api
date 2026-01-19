<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Officer;
use App\Models\Location;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * OfficerCommunityController
 * 
 * Handles officer community assignment operations.
 */
class OfficerCommunityController
{
    /**
     * Get officers assigned to a specific community (Admin)
     * GET /api/admin/officers/by-community/{communityId}
     */
    public function getOfficersByCommunity(Request $request, Response $response, array $args): Response
    {
        try {
            $communityId = (int) $args['communityId'];
            
            // Get officers assigned to this community (either main or smaller)
            $officers = Officer::whereRaw(
                'JSON_CONTAINS(assigned_main_communities, ?)', 
                [json_encode($communityId)]
            )->orWhereRaw(
                'JSON_CONTAINS(assigned_smaller_communities, ?)', 
                [json_encode($communityId)]
            )->with('user')->get();

            return ResponseHelper::success($response, 'Officers fetched successfully', [
                'officers' => $officers->map(fn($o) => $o->getFullProfile())->toArray(),
                'community_id' => $communityId,
                'count' => $officers->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch officers', 500, $e->getMessage());
        }
    }

    /**
     * Assign officer to communities (Admin)
     * POST /api/admin/officers/{id}/assign-communities
     */
    public function assignCommunities(Request $request, Response $response, array $args): Response
    {
        try {
            $officer = Officer::find($args['id']);

            if (!$officer) {
                return ResponseHelper::error($response, 'Officer not found', 404);
            }

            $data = $request->getParsedBody() ?? [];

            // Validate communities exist
            $mainCommunityIds = $data['main_community_ids'] ?? [];
            $smallerCommunityIds = $data['smaller_community_ids'] ?? [];

            if (!is_array($mainCommunityIds)) {
                $mainCommunityIds = [$mainCommunityIds];
            }
            if (!is_array($smallerCommunityIds)) {
                $smallerCommunityIds = [$smallerCommunityIds];
            }

            // Verify all communities exist
            if (!empty($mainCommunityIds)) {
                $validMainCount = Location::whereIn('id', $mainCommunityIds)
                    ->where('type', 'community')
                    ->count();
                if ($validMainCount !== count($mainCommunityIds)) {
                    return ResponseHelper::error($response, 'One or more main communities not found', 400);
                }
            }

            if (!empty($smallerCommunityIds)) {
                $validSmallerCount = Location::whereIn('id', $smallerCommunityIds)
                    ->where('type', 'smaller_community')
                    ->count();
                if ($validSmallerCount !== count($smallerCommunityIds)) {
                    return ResponseHelper::error($response, 'One or more smaller communities not found', 400);
                }
            }

            // Update officer assignments
            $officer->update([
                'assigned_main_communities' => !empty($mainCommunityIds) ? json_encode($mainCommunityIds) : null,
                'assigned_smaller_communities' => !empty($smallerCommunityIds) ? json_encode($smallerCommunityIds) : null,
            ]);

            return ResponseHelper::success($response, 'Communities assigned successfully', [
                'officer' => $officer->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to assign communities', 500, $e->getMessage());
        }
    }

    /**
     * Add communities to officer's assignment (Admin)
     * POST /api/admin/officers/{id}/add-communities
     */
    public function addCommunities(Request $request, Response $response, array $args): Response
    {
        try {
            $officer = Officer::find($args['id']);

            if (!$officer) {
                return ResponseHelper::error($response, 'Officer not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            
            $mainIds = $data['main_community_ids'] ?? [];
            $smallerIds = $data['smaller_community_ids'] ?? [];

            if (!is_array($mainIds)) $mainIds = [$mainIds];
            if (!is_array($smallerIds)) $smallerIds = [$smallerIds];

            // Get current assignments
            $currentMain = $officer->assigned_main_communities 
                ? json_decode($officer->assigned_main_communities, true) 
                : [];
            $currentSmaller = $officer->assigned_smaller_communities 
                ? json_decode($officer->assigned_smaller_communities, true) 
                : [];

            // Merge with new assignments (unique)
            $newMain = array_unique(array_merge($currentMain, $mainIds));
            $newSmaller = array_unique(array_merge($currentSmaller, $smallerIds));

            // Update
            $officer->update([
                'assigned_main_communities' => !empty($newMain) ? json_encode(array_values($newMain)) : null,
                'assigned_smaller_communities' => !empty($newSmaller) ? json_encode(array_values($newSmaller)) : null,
            ]);

            return ResponseHelper::success($response, 'Communities added successfully', [
                'officer' => $officer->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to add communities', 500, $e->getMessage());
        }
    }

    /**
     * Remove communities from officer's assignment (Admin)
     * DELETE /api/admin/officers/{id}/remove-communities
     */
    public function removeCommunities(Request $request, Response $response, array $args): Response
    {
        try {
            $officer = Officer::find($args['id']);

            if (!$officer) {
                return ResponseHelper::error($response, 'Officer not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            
            $removeMainIds = $data['main_community_ids'] ?? [];
            $removeSmallerIds = $data['smaller_community_ids'] ?? [];

            if (!is_array($removeMainIds)) $removeMainIds = [$removeMainIds];
            if (!is_array($removeSmallerIds)) $removeSmallerIds = [$removeSmallerIds];

            // Get current assignments
            $currentMain = $officer->assigned_main_communities 
                ? json_decode($officer->assigned_main_communities, true) 
                : [];
            $currentSmaller = $officer->assigned_smaller_communities 
                ? json_decode($officer->assigned_smaller_communities, true) 
                : [];

            // Remove specified IDs
            $newMain = array_diff($currentMain, $removeMainIds);
            $newSmaller = array_diff($currentSmaller, $removeSmallerIds);

            // Update
            $officer->update([
                'assigned_main_communities' => !empty($newMain) ? json_encode(array_values($newMain)) : null,
                'assigned_smaller_communities' => !empty($newSmaller) ? json_encode(array_values($newSmaller)) : null,
            ]);

            return ResponseHelper::success($response, 'Communities removed successfully', [
                'officer' => $officer->fresh()->getFullProfile()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to remove communities', 500, $e->getMessage());
        }
    }

    /**
     * Get officer's assigned communities (Officer)
     * GET /api/officer/my-communities
     */
    public function getMyCommunities(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $officer = Officer::findByUserId($user->id);

            if (!$officer) {
                return ResponseHelper::error($response, 'Officer profile not found', 404);
            }

            // Decode community IDs
            $mainCommunityIds = $officer->assigned_main_communities 
                ? json_decode($officer->assigned_main_communities, true) 
                : [];
            $smallerCommunityIds = $officer->assigned_smaller_communities 
                ? json_decode($officer->assigned_smaller_communities, true) 
                : [];

            // Fetch full community data
            $mainCommunities = [];
            $smallerCommunities = [];

            if (!empty($mainCommunityIds)) {
                $mainCommunities = Location::whereIn('id', $mainCommunityIds)
                    ->where('type', 'community')
                    ->get()
                    ->map(fn($l) => [
                        'id' => $l->id,
                        'name' => $l->name,
                        'type' => $l->type,
                        'status' => $l->status
                    ])->toArray();
            }

            if (!empty($smallerCommunityIds)) {
                $smallerCommunities = Location::whereIn('id', $smallerCommunityIds)
                    ->where('type', 'smaller_community')
                    ->get()
                    ->map(fn($l) => [
                        'id' => $l->id,
                        'name' => $l->name,
                        'type' => $l->type,
                        'parent_id' => $l->parent_id,
                        'status' => $l->status
                    ])->toArray();
            }

            return ResponseHelper::success($response, 'Communities fetched successfully', [
                'main_communities' => $mainCommunities,
                'smaller_communities' => $smallerCommunities,
                'all_communities' => array_merge($mainCommunities, $smallerCommunities)
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch communities', 500, $e->getMessage());
        }
    }

    /**
     * Get issues in officer's assigned communities (Officer)
     * GET /api/officer/community-issues
     */
    public function getMyCommunityIssues(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $officer = Officer::findByUserId($user->id);

            if (!$officer) {
                return ResponseHelper::error($response, 'Officer profile not found', 404);
            }

            // Get community IDs
            $mainCommunityIds = $officer->assigned_main_communities 
                ? json_decode($officer->assigned_main_communities, true) 
                : [];
            $smallerCommunityIds = $officer->assigned_smaller_communities 
                ? json_decode($officer->assigned_smaller_communities, true) 
                : [];

            // Build query
            $query = \App\Models\IssueReport::query();

            if (!empty($mainCommunityIds) || !empty($smallerCommunityIds)) {
                $query->where(function($q) use ($mainCommunityIds, $smallerCommunityIds) {
                    if (!empty($mainCommunityIds)) {
                        $q->orWhereIn('main_community_id', $mainCommunityIds);
                    }
                    if (!empty($smallerCommunityIds)) {
                        $q->orWhereIn('smaller_community_id', $smallerCommunityIds);
                    }
                });
            } else {
                // No communities assigned, return empty
                return ResponseHelper::success($response, 'No communities assigned', [
                    'issues' => [],
                    'total' => 0
                ]);
            }

            $params = $request->getQueryParams();
            $status = $params['status'] ?? null;
            
            if ($status) {
                $query->where('status', $status);
            }

            $issues = $query->with([
                'mainCommunity',
                'smallerCommunity',
                'sector',
                'subSector'
            ])->orderBy('created_at', 'desc')->get();

            return ResponseHelper::success($response, 'Issues fetched successfully', [
                'issues' => $issues->toArray(),
                'total' => $issues->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issues', 500, $e->getMessage());
        }
    }
}
