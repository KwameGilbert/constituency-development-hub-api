<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Location;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * LocationController
 * 
 * Handles location/area management operations for admins:
 * - GET /admin/locations - List all locations with filtering and pagination
 * - GET /admin/locations/:id - Get single location details
 * - POST /admin/locations - Create new location
 * - PUT /admin/locations/:id - Update location details
 * - DELETE /admin/locations/:id - Delete location
 * - GET /admin/locations/:id/stats - Get location statistics
 */
class LocationController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * List all locations with filtering and pagination
     * GET /v1/admin/locations
     */
    public function index(Request $request, Response $response): Response
    {
        file_put_contents(__DIR__ . '/../../debug_log.txt', "Inside LocationController::index\n", FILE_APPEND);
        try {
            $params = $request->getQueryParams();
            
            $result = Location::getAllWithFilters([
                'type' => $params['type'] ?? null,
                'status' => $params['status'] ?? null,
                'parent_id' => $params['parent_id'] ?? null,
                'search' => $params['search'] ?? null,
                'sort_by' => $params['sort_by'] ?? 'name',
                'sort_order' => $params['sort_order'] ?? 'asc',
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 20
            ]);

            $locations = array_map(function ($location) {
                return $location->toApiResponse();
            }, $result['locations']->all());

            return ResponseHelper::success($response, 'Locations retrieved successfully', [
                'locations' => $locations,
                'pagination' => $result['pagination']
            ]);

        } catch (\Throwable $e) {
            $logMessage = date('Y-m-d H:i:s') . " - Location Index Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            file_put_contents(__DIR__ . '/../../debug_log.txt', $logMessage, FILE_APPEND);
            return ResponseHelper::error($response, 'Failed to retrieve locations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single location details
     * GET /v1/admin/locations/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $location = Location::find($id);

            if (!$location) {
                return ResponseHelper::error($response, 'Location not found', 404);
            }

            return ResponseHelper::success($response, 'Location retrieved successfully', [
                'location' => $location->toApiResponse(true)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new location
     * POST /v1/admin/locations
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $user = $request->getAttribute('user');

            // Validation
            $errors = $this->validateLocationData($data);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Validate parent exists if provided
            if (!empty($data['parent_id'])) {
                $parent = Location::find($data['parent_id']);
                if (!$parent) {
                    return ResponseHelper::error($response, 'Parent location not found', 400);
                }
            }

            // Create location
            $location = new Location();
            $location->name = trim($data['name']);
            $location->type = $data['type'];
            $location->parent_id = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
            $location->population = !empty($data['population']) ? (int) $data['population'] : null;
            $location->area_size = !empty($data['area_size']) ? (float) $data['area_size'] : null;
            $location->latitude = !empty($data['latitude']) ? (float) $data['latitude'] : null;
            $location->longitude = !empty($data['longitude']) ? (float) $data['longitude'] : null;
            $location->description = !empty($data['description']) ? trim($data['description']) : null;
            $location->status = $data['status'] ?? Location::STATUS_ACTIVE;
            $location->save();

            // Log the action (non-blocking)
            try {
                AuditLog::logAction(
                    $user->id ?? 0,
                    'create_location',
                    'locations',
                    $location->id,
                    null,
                    $location->toArray()
                );
            } catch (Exception $e) {
                // Log audit failure but don't block the operation
                error_log('Audit log failed: ' . $e->getMessage());
            }

            return ResponseHelper::success($response, 'Location created successfully', [
                'location' => $location->toApiResponse()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update location details
     * PUT /v1/admin/locations/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody() ?? [];
            $user = $request->getAttribute('user');

            $location = Location::find($id);
            if (!$location) {
                return ResponseHelper::error($response, 'Location not found', 404);
            }

            $oldData = $location->toArray();

            // Validate parent if being updated
            if (isset($data['parent_id'])) {
                if ($data['parent_id'] !== null && $data['parent_id'] !== '') {
                    // Cannot set self as parent
                    if ((int) $data['parent_id'] === $id) {
                        return ResponseHelper::error($response, 'A location cannot be its own parent', 400);
                    }
                    $parent = Location::find($data['parent_id']);
                    if (!$parent) {
                        return ResponseHelper::error($response, 'Parent location not found', 400);
                    }
                }
            }

            // Validate type if being updated
            if (isset($data['type']) && !in_array($data['type'], Location::VALID_TYPES)) {
                return ResponseHelper::validationError($response, [
                    'type' => ['Invalid location type. Valid types: ' . implode(', ', Location::VALID_TYPES)]
                ]);
            }

            // Update fields
            if (isset($data['name'])) $location->name = trim($data['name']);
            if (isset($data['type'])) $location->type = $data['type'];
            if (array_key_exists('parent_id', $data)) {
                $location->parent_id = $data['parent_id'] ? (int) $data['parent_id'] : null;
            }
            if (isset($data['population'])) $location->population = (int) $data['population'];
            if (isset($data['area_size'])) $location->area_size = (float) $data['area_size'];
            if (isset($data['latitude'])) $location->latitude = (float) $data['latitude'];
            if (isset($data['longitude'])) $location->longitude = (float) $data['longitude'];
            if (isset($data['description'])) $location->description = trim($data['description']);
            if (isset($data['status'])) $location->status = $data['status'];
            
            $location->save();

            // Log the action (non-blocking)
            try {
                AuditLog::logAction(
                    $user->id ?? 0,
                    'update_location',
                    'locations',
                    $location->id,
                    $oldData,
                    $location->toArray()
                );
            } catch (Exception $e) {
                // Log audit failure but don't block the operation
                error_log('Audit log failed: ' . $e->getMessage());
            }

            return ResponseHelper::success($response, 'Location updated successfully', [
                'location' => $location->toApiResponse()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete location
     * DELETE /v1/admin/locations/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $user = $request->getAttribute('user');

            $location = Location::find($id);
            if (!$location) {
                return ResponseHelper::error($response, 'Location not found', 404);
            }

            // Check if location has children
            if ($location->children()->count() > 0) {
                return ResponseHelper::error($response, 'Cannot delete location with child locations. Delete or reassign children first.', 400);
            }

            $oldData = $location->toArray();
            $location->delete();

            // Log the action (non-blocking)
            try {
                AuditLog::logAction(
                    $user->id ?? 0,
                    'delete_location',
                    'locations',
                    $id,
                    $oldData,
                    null
                );
            } catch (Exception $e) {
                // Log audit failure but don't block the operation
                error_log('Audit log failed: ' . $e->getMessage());
            }

            return ResponseHelper::success($response, 'Location deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get location statistics
     * GET /v1/admin/locations/{id}/stats
     */
    public function stats(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $location = Location::find($id);

            if (!$location) {
                return ResponseHelper::error($response, 'Location not found', 404);
            }

            return ResponseHelper::success($response, 'Location statistics retrieved successfully', [
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'type' => $location->type
                ],
                'statistics' => $location->getStats()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve location statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get location dashboard statistics
     * GET /v1/admin/locations/dashboard-stats
     */
    public function dashboardStats(Request $request, Response $response): Response
    {
        try {
            // Count by type
            $typeCounts = [];
            foreach (Location::VALID_TYPES as $type) {
                $typeCounts[$type] = Location::where('type', $type)->count();
            }

            // Recent locations (last 5)
            $recentLocations = Location::orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'name' => $location->name,
                        'type' => $location->type,
                        'created_at' => $location->created_at->toIso8601String(),
                        'formatted_date' => $location->created_at->format('M d, Y')
                    ];
                });

            return ResponseHelper::success($response, 'Location dashboard stats retrieved successfully', [
                'counts' => $typeCounts,
                'total' => Location::count(),
                'recent_locations' => $recentLocations
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve dashboard stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get location types summary
     * GET /v1/admin/locations/types
     */
    public function types(Request $request, Response $response): Response
    {
        try {
            $typeCounts = [];
            foreach (Location::VALID_TYPES as $type) {
                $typeCounts[$type] = Location::where('type', $type)->count();
            }

            return ResponseHelper::success($response, 'Location types retrieved successfully', [
                'types' => Location::VALID_TYPES,
                'counts' => $typeCounts,
                'total' => Location::count()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve location types: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate location data
     */
    private function validateLocationData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate) {
            // Required fields for creation
            if (empty($data['name'])) {
                $errors['name'] = ['Name is required'];
            } elseif (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                $errors['name'] = ['Name must be between 2 and 100 characters'];
            }

            if (empty($data['type'])) {
                $errors['type'] = ['Type is required'];
            } elseif (!in_array($data['type'], Location::VALID_TYPES)) {
                $errors['type'] = ['Invalid location type. Valid types: ' . implode(', ', Location::VALID_TYPES)];
            }
        }

        // Optional field validations
        if (isset($data['population']) && $data['population'] < 0) {
            $errors['population'] = ['Population cannot be negative'];
        }

        if (isset($data['area_size']) && $data['area_size'] < 0) {
            $errors['area_size'] = ['Area size cannot be negative'];
        }

        if (isset($data['latitude']) && ($data['latitude'] < -90 || $data['latitude'] > 90)) {
            $errors['latitude'] = ['Latitude must be between -90 and 90'];
        }

        if (isset($data['longitude']) && ($data['longitude'] < -180 || $data['longitude'] > 180)) {
            $errors['longitude'] = ['Longitude must be between -180 and 180'];
        }

        if (isset($data['status']) && !in_array($data['status'], [Location::STATUS_ACTIVE, Location::STATUS_INACTIVE])) {
            $errors['status'] = ['Invalid status. Valid values: active, inactive'];
        }

        return $errors;
    }
}
