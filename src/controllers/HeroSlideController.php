<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\HeroSlide;
use App\Models\WebAdmin;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * HeroSlideController
 * 
 * Handles CRUD operations for homepage carousel slides.
 * Web Admins only.
 */
class HeroSlideController
{
    /**
     * Get all active slides (Public)
     * GET /api/hero-slides
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $slides = HeroSlide::active()->get();

            return ResponseHelper::success($response, 'Hero slides fetched successfully', [
                'slides' => $slides->map(fn($slide) => $slide->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch hero slides', 500, $e->getMessage());
        }
    }

    /**
     * Get all slides including inactive (Admin)
     * GET /api/admin/hero-slides
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $slides = HeroSlide::orderBy('display_order')->get();

            return ResponseHelper::success($response, 'Hero slides fetched successfully', [
                'slides' => $slides->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch hero slides', 500, $e->getMessage());
        }
    }

    /**
     * Get single slide
     * GET /api/admin/hero-slides/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $slide = HeroSlide::find($args['id']);

            if (!$slide) {
                return ResponseHelper::error($response, 'Hero slide not found', 404);
            }

            return ResponseHelper::success($response, 'Hero slide fetched successfully', [
                'slide' => $slide->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch hero slide', 500, $e->getMessage());
        }
    }

    /**
     * Create new slide
     * POST /api/admin/hero-slides
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['title'])) {
                return ResponseHelper::error($response, 'Title is required', 400);
            }
            if (empty($data['image'])) {
                return ResponseHelper::error($response, 'Image is required', 400);
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $slide = HeroSlide::create([
                'created_by' => $webAdmin->id ?? null,
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'description' => $data['description'] ?? null,
                'image' => $data['image'],
                'cta_label' => $data['cta_label'] ?? null,
                'cta_link' => $data['cta_link'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'status' => $data['status'] ?? HeroSlide::STATUS_ACTIVE,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Hero slide created successfully', [
                'slide' => $slide->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create hero slide', 500, $e->getMessage());
        }
    }

    /**
     * Update slide
     * PUT /api/admin/hero-slides/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $slide = HeroSlide::find($args['id']);

            if (!$slide) {
                return ResponseHelper::error($response, 'Hero slide not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $slide->update([
                'updated_by' => $webAdmin->id ?? null,
                'title' => $data['title'] ?? $slide->title,
                'subtitle' => $data['subtitle'] ?? $slide->subtitle,
                'description' => $data['description'] ?? $slide->description,
                'image' => $data['image'] ?? $slide->image,
                'cta_label' => $data['cta_label'] ?? $slide->cta_label,
                'cta_link' => $data['cta_link'] ?? $slide->cta_link,
                'display_order' => $data['display_order'] ?? $slide->display_order,
                'status' => $data['status'] ?? $slide->status,
                'starts_at' => $data['starts_at'] ?? $slide->starts_at,
                'ends_at' => $data['ends_at'] ?? $slide->ends_at,
            ]);

            return ResponseHelper::success($response, 'Hero slide updated successfully', [
                'slide' => $slide->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update hero slide', 500, $e->getMessage());
        }
    }

    /**
     * Delete slide
     * DELETE /api/admin/hero-slides/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $slide = HeroSlide::find($args['id']);

            if (!$slide) {
                return ResponseHelper::error($response, 'Hero slide not found', 404);
            }

            $slide->delete();

            return ResponseHelper::success($response, 'Hero slide deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete hero slide', 500, $e->getMessage());
        }
    }

    /**
     * Reorder slides
     * PUT /api/admin/hero-slides/reorder
     */
    public function reorder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['order']) || !is_array($data['order'])) {
                return ResponseHelper::error($response, 'Order array is required', 400);
            }

            foreach ($data['order'] as $index => $slideId) {
                HeroSlide::where('id', $slideId)->update(['display_order' => $index]);
            }

            return ResponseHelper::success($response, 'Slides reordered successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder slides', 500, $e->getMessage());
        }
    }
}
