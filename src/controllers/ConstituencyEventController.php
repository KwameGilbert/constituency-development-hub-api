<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ConstituencyEvent;
use App\Models\WebAdmin;
use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

/**
 * ConstituencyEventController
 * 
 * Handles CRUD operations for community events.
 * Web Admins only for create/update/delete.
 */
class ConstituencyEventController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * Get upcoming events (Public)
     * GET /api/events
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $status = $params['status'] ?? null;

            $query = ConstituencyEvent::orderBy('event_date', 'asc');

            if ($status) {
                $query->where('status', $status);
            } else {
                // Default to upcoming events
                $query->where('event_date', '>=', date('Y-m-d'));
            }

            $total = $query->count();
            $events = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Events fetched successfully', [
                'events' => $events->map(fn($event) => $event->toPublicArray())->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Get upcoming events (Public)
     * GET /api/events/upcoming
     */
    public function upcoming(Request $request, Response $response): Response
    {
        try {
            $limit = min((int)($request->getQueryParams()['limit'] ?? 5), 20);
            $events = ConstituencyEvent::upcoming()->take($limit)->get();

            return ResponseHelper::success($response, 'Upcoming events fetched successfully', [
                'events' => $events->map(fn($event) => $event->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch upcoming events', 500, $e->getMessage());
        }
    }

    /**
     * Get single event by slug (Public)
     * GET /api/events/{slug}
     */
    public function showBySlug(Request $request, Response $response, array $args): Response
    {
        try {
            $event = ConstituencyEvent::where('slug', $args['slug'])->first();

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            return ResponseHelper::success($response, 'Event fetched successfully', [
                'event' => $event->toPublicArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event', 500, $e->getMessage());
        }
    }

    /**
     * Get all events (Admin)
     * GET /api/admin/events
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $status = $params['status'] ?? null;

            $query = ConstituencyEvent::orderBy('event_date', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $events = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Events fetched successfully', [
                'events' => $events->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Get single event by ID (Admin)
     * GET /api/admin/events/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $event = ConstituencyEvent::find($args['id']);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            return ResponseHelper::success($response, 'Event fetched successfully', [
                'event' => $event->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event', 500, $e->getMessage());
        }
    }

    /**
     * Create new event
     * POST /api/admin/events
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['name'])) {
                return ResponseHelper::error($response, 'Event name is required', 400);
            }
            if (empty($data['event_date'])) {
                return ResponseHelper::error($response, 'Event date is required', 400);
            }
            if (empty($data['location'])) {
                return ResponseHelper::error($response, 'Location is required', 400);
            }

            // Handle image upload
            $imageUrl = $data['image'] ?? null;
            $imageFile = $uploadedFiles['image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = $this->uploadService->uploadFile($imageFile, 'image', 'events');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Generate slug
            $slug = $data['slug'] ?? $this->generateSlug($data['name']);
            if (ConstituencyEvent::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . time();
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $event = ConstituencyEvent::create([
                'created_by' => $webAdmin->id ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'event_date' => $data['event_date'],
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'location' => $data['location'],
                'venue_address' => $data['venue_address'] ?? null,
                'map_url' => $data['map_url'] ?? null,
                'image' => $imageUrl,
                'organizer' => $data['organizer'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'status' => $data['status'] ?? ConstituencyEvent::STATUS_UPCOMING,
                'is_featured' => $data['is_featured'] ?? false,
                'max_attendees' => $data['max_attendees'] ?? null,
                'registration_required' => $data['registration_required'] ?? false,
            ]);

            return ResponseHelper::success($response, 'Event created successfully', [
                'event' => $event->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create event', 500, $e->getMessage());
        }
    }

    /**
     * Update event
     * PUT /api/admin/events/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $event = ConstituencyEvent::find($args['id']);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();
            $user = $request->getAttribute('user');

            // Handle image upload
            $imageUrl = $data['image'] ?? $event->image;
            $imageFile = $uploadedFiles['image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = $this->uploadService->replaceFile($imageFile, $event->image, 'image', 'events');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $event->update([
                'updated_by' => $webAdmin->id ?? null,
                'name' => $data['name'] ?? $event->name,
                'slug' => $data['slug'] ?? $event->slug,
                'description' => $data['description'] ?? $event->description,
                'event_date' => $data['event_date'] ?? $event->event_date,
                'start_time' => $data['start_time'] ?? $event->start_time,
                'end_time' => $data['end_time'] ?? $event->end_time,
                'location' => $data['location'] ?? $event->location,
                'venue_address' => $data['venue_address'] ?? $event->venue_address,
                'map_url' => $data['map_url'] ?? $event->map_url,
                'image' => $imageUrl,
                'organizer' => $data['organizer'] ?? $event->organizer,
                'contact_phone' => $data['contact_phone'] ?? $event->contact_phone,
                'contact_email' => $data['contact_email'] ?? $event->contact_email,
                'status' => $data['status'] ?? $event->status,
                'is_featured' => $data['is_featured'] ?? $event->is_featured,
                'max_attendees' => $data['max_attendees'] ?? $event->max_attendees,
                'registration_required' => $data['registration_required'] ?? $event->registration_required,
            ]);

            return ResponseHelper::success($response, 'Event updated successfully', [
                'event' => $event->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event', 500, $e->getMessage());
        }
    }

    /**
     * Delete event
     * DELETE /api/admin/events/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $event = ConstituencyEvent::find($args['id']);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            $event->delete();

            return ResponseHelper::success($response, 'Event deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event', 500, $e->getMessage());
        }
    }

    /**
     * Generate URL-friendly slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
