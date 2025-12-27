<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Project;
use App\Models\Sector;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * ProjectController
 * 
 * Handles CRUD operations for development projects.
 * Web Admins and Officers can manage projects.
 */
class ProjectController
{
    /**
     * Get all projects (Public)
     * GET /api/projects
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $status = $params['status'] ?? null;
            $sector = $params['sector'] ?? null;
            $location = $params['location'] ?? null;

            $query = Project::with('sector')->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }
            if ($sector) {
                $query->where('sector_id', $sector);
            }
            if ($location) {
                $query->where('location', 'LIKE', "%{$location}%");
            }

            $total = $query->count();
            $projects = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Projects fetched successfully', [
                'projects' => $projects->map(fn($p) => $p->toPublicArray())->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch projects', 500, $e->getMessage());
        }
    }

    /**
     * Get featured projects
     * GET /api/projects/featured
     */
    public function featured(Request $request, Response $response): Response
    {
        try {
            $limit = min((int)($request->getQueryParams()['limit'] ?? 6), 20);
            $projects = Project::with('sector')->featured()->take($limit)->get();

            return ResponseHelper::success($response, 'Featured projects fetched successfully', [
                'projects' => $projects->map(fn($p) => $p->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch featured projects', 500, $e->getMessage());
        }
    }

    /**
     * Get project statistics
     * GET /api/projects/stats
     */
    public function stats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'total' => Project::count(),
                'ongoing' => Project::where('status', Project::STATUS_ONGOING)->count(),
                'completed' => Project::where('status', Project::STATUS_COMPLETED)->count(),
                'planning' => Project::where('status', Project::STATUS_PLANNING)->count(),
                'by_sector' => Sector::withCount('projects')->get()->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'count' => $s->projects_count,
                ])->toArray(),
            ];

            return ResponseHelper::success($response, 'Project statistics fetched successfully', $stats);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch project statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get single project by slug (Public)
     * GET /api/projects/{slug}
     */
    public function showBySlug(Request $request, Response $response, array $args): Response
    {
        try {
            $project = Project::with(['sector', 'managingOfficer.user'])->where('slug', $args['slug'])->first();

            if (!$project) {
                return ResponseHelper::error($response, 'Project not found', 404);
            }

            $project->incrementViews();

            return ResponseHelper::success($response, 'Project fetched successfully', [
                'project' => $project->toPublicArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch project', 500, $e->getMessage());
        }
    }

    /**
     * Get all projects (Admin)
     * GET /api/admin/projects
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);

            $query = Project::with('sector')->orderBy('created_at', 'desc');

            $total = $query->count();
            $projects = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Projects fetched successfully', [
                'projects' => $projects->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch projects', 500, $e->getMessage());
        }
    }

    /**
     * Get single project by ID (Admin)
     * GET /api/admin/projects/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $project = Project::with(['sector', 'managingOfficer.user', 'createdBy', 'updatedBy'])->find($args['id']);

            if (!$project) {
                return ResponseHelper::error($response, 'Project not found', 404);
            }

            return ResponseHelper::success($response, 'Project fetched successfully', [
                'project' => $project->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch project', 500, $e->getMessage());
        }
    }

    /**
     * Create new project
     * POST /api/admin/projects
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
            if (empty($data['sector_id'])) {
                return ResponseHelper::error($response, 'Sector is required', 400);
            }
            if (empty($data['location'])) {
                return ResponseHelper::error($response, 'Location is required', 400);
            }

            // Generate slug
            $slug = $data['slug'] ?? $this->generateSlug($data['title']);
            if (Project::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . time();
            }

            $project = Project::create([
                'created_by' => $user->id ?? null,
                'title' => $data['title'],
                'slug' => $slug,
                'sector_id' => $data['sector_id'],
                'location' => $data['location'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? Project::STATUS_PLANNING,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'budget' => $data['budget'] ?? null,
                'spent' => $data['spent'] ?? 0,
                'progress_percent' => $data['progress_percent'] ?? 0,
                'beneficiaries' => $data['beneficiaries'] ?? null,
                'image' => $data['image'] ?? null,
                'gallery' => $data['gallery'] ?? null,
                'contractor' => $data['contractor'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'managing_officer_id' => $data['managing_officer_id'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
            ]);

            return ResponseHelper::success($response, 'Project created successfully', [
                'project' => $project->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create project', 500, $e->getMessage());
        }
    }

    /**
     * Update project
     * PUT /api/admin/projects/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $project = Project::find($args['id']);

            if (!$project) {
                return ResponseHelper::error($response, 'Project not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            $project->update([
                'updated_by' => $user->id ?? null,
                'title' => $data['title'] ?? $project->title,
                'slug' => $data['slug'] ?? $project->slug,
                'sector_id' => $data['sector_id'] ?? $project->sector_id,
                'location' => $data['location'] ?? $project->location,
                'description' => $data['description'] ?? $project->description,
                'status' => $data['status'] ?? $project->status,
                'start_date' => $data['start_date'] ?? $project->start_date,
                'end_date' => $data['end_date'] ?? $project->end_date,
                'budget' => $data['budget'] ?? $project->budget,
                'spent' => $data['spent'] ?? $project->spent,
                'progress_percent' => $data['progress_percent'] ?? $project->progress_percent,
                'beneficiaries' => $data['beneficiaries'] ?? $project->beneficiaries,
                'image' => $data['image'] ?? $project->image,
                'gallery' => $data['gallery'] ?? $project->gallery,
                'contractor' => $data['contractor'] ?? $project->contractor,
                'contact_person' => $data['contact_person'] ?? $project->contact_person,
                'contact_phone' => $data['contact_phone'] ?? $project->contact_phone,
                'managing_officer_id' => $data['managing_officer_id'] ?? $project->managing_officer_id,
                'is_featured' => $data['is_featured'] ?? $project->is_featured,
            ]);

            return ResponseHelper::success($response, 'Project updated successfully', [
                'project' => $project->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update project', 500, $e->getMessage());
        }
    }

    /**
     * Delete project
     * DELETE /api/admin/projects/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $project = Project::find($args['id']);

            if (!$project) {
                return ResponseHelper::error($response, 'Project not found', 404);
            }

            $project->delete();

            return ResponseHelper::success($response, 'Project deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete project', 500, $e->getMessage());
        }
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
