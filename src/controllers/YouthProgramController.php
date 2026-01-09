<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\YouthProgram;
use App\Models\YouthProgramParticipant;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Carbon\Carbon;
use Exception;

/**
 * YouthProgramController
 * 
 * Handles youth program management:
 * Public endpoints:
 * - GET /youth-programs - List active programs (public)
 * - GET /youth-programs/:slug - Get program by slug (public)
 * - POST /youth-programs/:id/enroll - Enroll in a program (public)
 * 
 * Admin endpoints:
 * - GET /admin/youth-programs - List all programs
 * - GET /admin/youth-programs/:id - Get program details
 * - POST /admin/youth-programs - Create program
 * - PUT /admin/youth-programs/:id - Update program
 * - DELETE /admin/youth-programs/:id - Delete program
 * - GET /admin/youth-programs/:id/participants - Get participants
 * - PUT /admin/youth-programs/:id/participants/:participantId - Update participant status
 */
class YouthProgramController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * List active programs (public)
     * GET /v1/youth-programs
     */
    public function publicIndex(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            
            $result = YouthProgram::getAllWithFilters([
                'category' => $params['category'] ?? null,
                'location_id' => $params['location_id'] ?? null,
                'search' => $params['search'] ?? null,
                'public_only' => true,
                'sort_by' => $params['sort_by'] ?? 'start_date',
                'sort_order' => $params['sort_order'] ?? 'asc',
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 20
            ]);

            $programs = array_map(function ($program) {
                return $program->toApiResponse();
            }, $result['programs']->all());

            return ResponseHelper::success($response, 'Programs retrieved successfully', [
                'programs' => $programs,
                'pagination' => $result['pagination']
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve programs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get program by slug (public)
     * GET /v1/youth-programs/{slug}
     */
    public function publicShow(Request $request, Response $response, array $args): Response
    {
        try {
            $slug = $args['slug'];
            $program = YouthProgram::where('slug', $slug)
                ->public()
                ->first();

            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            return ResponseHelper::success($response, 'Program retrieved successfully', [
                'program' => $program->toApiResponse(true)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enroll in a program (public)
     * POST /v1/youth-programs/{id}/enroll
     */
    public function enroll(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody() ?? [];

            $program = YouthProgram::find($id);
            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            if (!$program->isRegistrationOpen()) {
                return ResponseHelper::error($response, 'Registration is closed for this program', 400);
            }

            // Validate required fields
            $errors = $this->validateEnrollmentData($data);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Check for duplicate enrollment
            $existingEnrollment = YouthProgramParticipant::where('program_id', $id)
                ->where('email', $data['email'])
                ->first();

            if ($existingEnrollment) {
                return ResponseHelper::error($response, 'This email is already enrolled in this program', 400);
            }

            // Get authenticated user if available
            $userId = null;
            try {
                $user = $this->authService->getAuthenticatedUser($request);
                $userId = $user?->id;
            } catch (Exception $e) {
                // User not authenticated, that's okay for public enrollment
            }

            // Create participant
            $participant = new YouthProgramParticipant();
            $participant->program_id = $id;
            $participant->user_id = $userId;
            $participant->full_name = trim($data['full_name']);
            $participant->email = trim($data['email']);
            $participant->phone = trim($data['phone'] ?? '');
            $participant->date_of_birth = !empty($data['date_of_birth']) ? Carbon::parse($data['date_of_birth']) : null;
            $participant->gender = $data['gender'] ?? null;
            $participant->address = $data['address'] ?? null;
            $participant->emergency_contact_name = $data['emergency_contact_name'] ?? null;
            $participant->emergency_contact_phone = $data['emergency_contact_phone'] ?? null;
            $participant->status = YouthProgramParticipant::STATUS_PENDING;
            $participant->registered_at = Carbon::now();
            $participant->save();

            // Update enrollment count
            $program->increment('current_enrollment');

            return ResponseHelper::success($response, 'Successfully enrolled in the program', [
                'enrollment' => $participant->toApiResponse()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to enroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all programs (admin)
     * GET /v1/admin/youth-programs
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            
            $result = YouthProgram::getAllWithFilters([
                'category' => $params['category'] ?? null,
                'status' => $params['status'] ?? null,
                'location_id' => $params['location_id'] ?? null,
                'search' => $params['search'] ?? null,
                'sort_by' => $params['sort_by'] ?? 'created_at',
                'sort_order' => $params['sort_order'] ?? 'desc',
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 20
            ]);

            $programs = array_map(function ($program) {
                return $program->toApiResponse(true);
            }, $result['programs']->all());

            return ResponseHelper::success($response, 'Programs retrieved successfully', [
                'programs' => $programs,
                'pagination' => $result['pagination']
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve programs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get program details (admin)
     * GET /v1/admin/youth-programs/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $program = YouthProgram::find($id);

            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            $data = $program->toApiResponse(true);
            $data['participants_count'] = $program->participants()->count();
            $data['approved_count'] = $program->participants()
                ->where('status', YouthProgramParticipant::STATUS_APPROVED)
                ->count();

            return ResponseHelper::success($response, 'Program retrieved successfully', [
                'program' => $data
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create program (admin)
     * POST /v1/admin/youth-programs
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $user = $this->authService->getAuthenticatedUser($request);

            // Validation
            $errors = $this->validateProgramData($data);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Create program
            $program = new YouthProgram();
            $program->title = trim($data['title']);
            $program->slug = YouthProgram::generateSlug($data['title']);
            $program->description = $data['description'] ?? null;
            $program->category = $data['category'];
            $program->start_date = !empty($data['start_date']) ? Carbon::parse($data['start_date']) : null;
            $program->end_date = !empty($data['end_date']) ? Carbon::parse($data['end_date']) : null;
            $program->registration_deadline = !empty($data['registration_deadline']) ? Carbon::parse($data['registration_deadline']) : null;
            $program->status = $data['status'] ?? YouthProgram::STATUS_DRAFT;
            $program->max_participants = !empty($data['max_participants']) ? (int) $data['max_participants'] : null;
            $program->location_id = !empty($data['location_id']) ? (int) $data['location_id'] : null;
            $program->venue = $data['venue'] ?? null;
            $program->image_url = $data['image_url'] ?? null;
            $program->requirements = $data['requirements'] ?? null;
            $program->benefits = $data['benefits'] ?? null;
            $program->contact_email = $data['contact_email'] ?? null;
            $program->contact_phone = $data['contact_phone'] ?? null;
            $program->created_by = $user->id ?? null;
            $program->save();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'create_youth_program',
                'youth_programs',
                $program->id,
                null,
                $program->toArray()
            );

            return ResponseHelper::success($response, 'Program created successfully', [
                'program' => $program->toApiResponse(true)
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update program (admin)
     * PUT /v1/admin/youth-programs/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody() ?? [];
            $user = $this->authService->getAuthenticatedUser($request);

            $program = YouthProgram::find($id);
            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            $oldData = $program->toArray();

            // Validate category if provided
            if (isset($data['category']) && !in_array($data['category'], YouthProgram::VALID_CATEGORIES)) {
                return ResponseHelper::validationError($response, [
                    'category' => ['Invalid category']
                ]);
            }

            // Validate status if provided
            if (isset($data['status']) && !in_array($data['status'], YouthProgram::VALID_STATUSES)) {
                return ResponseHelper::validationError($response, [
                    'status' => ['Invalid status']
                ]);
            }

            // Update fields
            if (isset($data['title'])) {
                $program->title = trim($data['title']);
                // Regenerate slug if title changed
                $program->slug = YouthProgram::generateSlug($data['title']);
            }
            if (isset($data['description'])) $program->description = $data['description'];
            if (isset($data['category'])) $program->category = $data['category'];
            if (isset($data['start_date'])) $program->start_date = Carbon::parse($data['start_date']);
            if (isset($data['end_date'])) $program->end_date = Carbon::parse($data['end_date']);
            if (isset($data['registration_deadline'])) $program->registration_deadline = Carbon::parse($data['registration_deadline']);
            if (isset($data['status'])) $program->status = $data['status'];
            if (isset($data['max_participants'])) $program->max_participants = (int) $data['max_participants'];
            if (isset($data['location_id'])) $program->location_id = (int) $data['location_id'];
            if (isset($data['venue'])) $program->venue = $data['venue'];
            if (isset($data['image_url'])) $program->image_url = $data['image_url'];
            if (isset($data['requirements'])) $program->requirements = $data['requirements'];
            if (isset($data['benefits'])) $program->benefits = $data['benefits'];
            if (isset($data['contact_email'])) $program->contact_email = $data['contact_email'];
            if (isset($data['contact_phone'])) $program->contact_phone = $data['contact_phone'];
            
            $program->save();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'update_youth_program',
                'youth_programs',
                $program->id,
                $oldData,
                $program->toArray()
            );

            return ResponseHelper::success($response, 'Program updated successfully', [
                'program' => $program->toApiResponse(true)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete program (admin)
     * DELETE /v1/admin/youth-programs/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $user = $this->authService->getAuthenticatedUser($request);

            $program = YouthProgram::find($id);
            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            // Check for active participants
            $activeParticipants = $program->participants()
                ->whereIn('status', [
                    YouthProgramParticipant::STATUS_PENDING,
                    YouthProgramParticipant::STATUS_APPROVED
                ])
                ->count();

            if ($activeParticipants > 0) {
                return ResponseHelper::error($response, 'Cannot delete program with active participants. Cancel enrollments first.', 400);
            }

            $oldData = $program->toArray();
            
            // Delete all participants
            $program->participants()->delete();
            $program->delete();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'delete_youth_program',
                'youth_programs',
                $id,
                $oldData,
                null
            );

            return ResponseHelper::success($response, 'Program deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete program: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get program participants (admin)
     * GET /v1/admin/youth-programs/{id}/participants
     */
    public function participants(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $params = $request->getQueryParams();

            $program = YouthProgram::find($id);
            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            $query = $program->participants();

            // Filter by status
            if (!empty($params['status'])) {
                $query->where('status', $params['status']);
            }

            // Search
            if (!empty($params['search'])) {
                $query->where(function ($q) use ($params) {
                    $q->where('full_name', 'LIKE', '%' . $params['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $params['search'] . '%');
                });
            }

            // Pagination
            $page = (int) ($params['page'] ?? 1);
            $limit = min((int) ($params['limit'] ?? 20), 100);
            $total = $query->count();

            $participants = $query->orderBy('registered_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Participants retrieved successfully', [
                'program' => [
                    'id' => $program->id,
                    'title' => $program->title
                ],
                'participants' => array_map(function ($p) {
                    return $p->toApiResponse();
                }, $participants->all()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit)
                ]
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve participants: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update participant status (admin)
     * PUT /v1/admin/youth-programs/{id}/participants/{participantId}
     */
    public function updateParticipant(Request $request, Response $response, array $args): Response
    {
        try {
            $programId = (int) $args['id'];
            $participantId = (int) $args['participantId'];
            $data = $request->getParsedBody() ?? [];
            $user = $this->authService->getAuthenticatedUser($request);

            $program = YouthProgram::find($programId);
            if (!$program) {
                return ResponseHelper::error($response, 'Program not found', 404);
            }

            $participant = YouthProgramParticipant::where('id', $participantId)
                ->where('program_id', $programId)
                ->first();

            if (!$participant) {
                return ResponseHelper::error($response, 'Participant not found', 404);
            }

            $oldStatus = $participant->status;

            if (isset($data['status'])) {
                if (!in_array($data['status'], YouthProgramParticipant::VALID_STATUSES)) {
                    return ResponseHelper::validationError($response, [
                        'status' => ['Invalid status']
                    ]);
                }
                $participant->status = $data['status'];

                // Set completed_at if status is completed
                if ($data['status'] === YouthProgramParticipant::STATUS_COMPLETED) {
                    $participant->completed_at = Carbon::now();
                }
            }

            if (isset($data['notes'])) {
                $participant->notes = $data['notes'];
            }

            $participant->save();

            return ResponseHelper::success($response, 'Participant updated successfully', [
                'participant' => $participant->toApiResponse()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update participant: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate program data
     */
    private function validateProgramData(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = ['Title is required'];
        } elseif (strlen($data['title']) < 3 || strlen($data['title']) > 200) {
            $errors['title'] = ['Title must be between 3 and 200 characters'];
        }

        if (empty($data['category'])) {
            $errors['category'] = ['Category is required'];
        } elseif (!in_array($data['category'], YouthProgram::VALID_CATEGORIES)) {
            $errors['category'] = ['Invalid category. Valid: ' . implode(', ', YouthProgram::VALID_CATEGORIES)];
        }

        if (isset($data['status']) && !in_array($data['status'], YouthProgram::VALID_STATUSES)) {
            $errors['status'] = ['Invalid status. Valid: ' . implode(', ', YouthProgram::VALID_STATUSES)];
        }

        if (!empty($data['max_participants']) && $data['max_participants'] < 1) {
            $errors['max_participants'] = ['Max participants must be at least 1'];
        }

        return $errors;
    }

    /**
     * Validate enrollment data
     */
    private function validateEnrollmentData(array $data): array
    {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors['full_name'] = ['Full name is required'];
        }

        if (empty($data['email'])) {
            $errors['email'] = ['Email is required'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Invalid email format'];
        }

        return $errors;
    }
}
