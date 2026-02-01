<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Sector;
use App\Models\SubSector;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * SubSectorController
 * 
 * Handles CRUD operations for sub-sectors.
 * Web Admins only.
 */
class SubSectorController
{
    /**
     * Get sub-sectors for a sector
     * GET /v1/admin/sectors/{sectorId}/sub-sectors
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $sectorId = (int)$args['sectorId'];
            
            // Check if sector exists
            $sector = Sector::find($sectorId);
            if (!$sector) {
                return ResponseHelper::error($response, 'Sector not found', 404);
            }

            $subSectors = SubSector::where('sector_id', $sectorId)
                ->orderBy('display_order', 'asc')
                ->get()
                ->map(fn($item) => $item->toFullArray());

            return ResponseHelper::success($response, 'Sub-sectors fetched successfully', [
                'sub_sectors' => $subSectors,
                'sector_name' => $sector->name
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch sub-sectors: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new sub-sector
     * POST /v1/admin/sectors/{sectorId}/sub-sectors
     */
    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $sectorId = (int)$args['sectorId'];
            $data = $request->getParsedBody();

            // Validate
            if (empty($data['name'])) {
                return ResponseHelper::error($response, 'Sub-sector name is required');
            }

            // Check if sector exists
            $sector = Sector::find($sectorId);
            if (!$sector) {
                return ResponseHelper::error($response, 'Sector not found', 404);
            }

            // Determine display order (last + 1)
            $maxOrder = SubSector::where('sector_id', $sectorId)->max('display_order') ?? 0;

            $subSector = new SubSector();
            $subSector->sector_id = $sectorId;
            $subSector->name = trim($data['name']);
            $subSector->code = $data['code'] ?? null;
            $subSector->description = $data['description'] ?? null;
            $subSector->icon = $data['icon'] ?? null;
            $subSector->status = $data['status'] ?? 'active';
            $subSector->display_order = $maxOrder + 1;
            
            if (!$subSector->save()) {
                throw new Exception("Database error while saving sub-sector");
            }

            return ResponseHelper::success($response, 'Sub-sector created successfully', [
                'sub_sector' => $subSector->toFullArray()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create sub-sector: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update sub-sector
     * PUT /v1/admin/sub-sectors/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $data = $request->getParsedBody();

            $subSector = SubSector::find($id);

            if (!$subSector) {
                return ResponseHelper::error($response, 'Sub-sector not found', 404);
            }

            if (isset($data['name']) && !empty($data['name'])) {
                $subSector->name = trim($data['name']);
            }
            if (isset($data['code'])) $subSector->code = $data['code'];
            if (isset($data['description'])) $subSector->description = $data['description'];
            if (isset($data['icon'])) $subSector->icon = $data['icon'];
            if (isset($data['status'])) $subSector->status = $data['status'];
            if (isset($data['display_order'])) $subSector->display_order = (int)$data['display_order'];

            $subSector->save();

            return ResponseHelper::success($response, 'Sub-sector updated successfully', [
                'sub_sector' => $subSector->toFullArray()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update sub-sector: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete sub-sector
     * DELETE /v1/admin/sub-sectors/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $subSector = SubSector::find($id);

            if (!$subSector) {
                return ResponseHelper::error($response, 'Sub-sector not found', 404);
            }

            // Check for associated issues
            if ($subSector->getIssuesCount() > 0) {
                return ResponseHelper::error($response, 'Cannot delete sub-sector with associated issues', 400);
            }

            $subSector->delete();

            return ResponseHelper::success($response, 'Sub-sector deleted successfully', [
                'success' => true
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete sub-sector: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reorder sub-sectors
     * PUT /v1/admin/sub-sectors/reorder
     */
    public function reorder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['order']) || !is_array($data['order'])) {
                return ResponseHelper::error($response, 'Invalid order data provided');
            }

            foreach ($data['order'] as $index => $id) {
                SubSector::where('id', $id)->update(['display_order' => $index + 1]);
            }

            return ResponseHelper::success($response, 'Sub-sectors reordered successfully', [
                'success' => true
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder sub-sectors: ' . $e->getMessage(), 500);
        }
    }
}
