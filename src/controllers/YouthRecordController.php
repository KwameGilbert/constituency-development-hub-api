<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\YouthRecord;
use App\Models\AuditLog;
use App\Models\User;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Carbon\Carbon;
use Exception;

/**
 * YouthRecordController
 * 
 * Handles youth record management:
 * - GET /admin/youth-records - List all records
 * - GET /admin/youth-records/stats - Get statistics
 * - GET /admin/youth-records/{id} - Get record details
 * - POST /admin/youth-records - Create record
 * - PUT /admin/youth-records/{id} - Update record
 * - PUT /admin/youth-records/{id}/status - Update record status
 * - DELETE /admin/youth-records/{id} - Delete record
 */
class YouthRecordController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * List all youth records with pagination and filtering
     * GET /v1/admin/youth-records
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            
            $result = YouthRecord::getAllWithFilters([
                'status' => $params['status'] ?? null,
                'employment_status' => $params['employment_status'] ?? null,
                'education_level' => $params['education_level'] ?? null,
                'location_id' => $params['location_id'] ?? null,
                'community' => $params['community'] ?? null,
                'search' => $params['search'] ?? null,
                'sort_by' => $params['sort_by'] ?? 'created_at',
                'sort_order' => $params['sort_order'] ?? 'desc',
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 20
            ]);

            $records = array_map(function ($record) {
                return $record->toApiResponse();
            }, $result['records']->all());

            return ResponseHelper::success($response, 'Youth records retrieved successfully', [
                'records' => $records,
                'pagination' => $result['pagination']
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve youth records: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get youth records statistics
     * GET /v1/admin/youth-records/stats
     */
    public function stats(Request $request, Response $response): Response
    {
        try {
            $statistics = YouthRecord::getStatistics();

            return ResponseHelper::success($response, 'Statistics retrieved successfully', [
                'statistics' => $statistics
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get youth record details
     * GET /v1/admin/youth-records/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $record = YouthRecord::find($id);

            if (!$record) {
                return ResponseHelper::error($response, 'Youth record not found', 404);
            }

            return ResponseHelper::success($response, 'Youth record retrieved successfully', [
                'record' => $record->toApiResponse(true)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve youth record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create youth record
     * POST /v1/admin/youth-records
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $user = $request->getAttribute('user');

            // Validation
            $errors = $this->validateRecordData($data);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Create record
            $record = new YouthRecord();
            $record->full_name = trim($data['full_name']);
            $record->date_of_birth = !empty($data['date_of_birth']) ? Carbon::parse($data['date_of_birth']) : null;
            $record->gender = $data['gender'] ?? null;
            $record->national_id = trim($data['national_id'] ?? '');
            $record->phone = trim($data['phone'] ?? '');
            $record->email = trim($data['email'] ?? '');
            $record->hometown = trim($data['hometown'] ?? '');
            $record->community = trim($data['community'] ?? '');
            $record->location_id = !empty($data['location_id']) ? (int) $data['location_id'] : null;
            
            // Education
            $record->education_level = $data['education_level'] ?? null;
            $record->jhs_completed = !empty($data['jhs_completed']);
            $record->shs_qualification = trim($data['shs_qualification'] ?? '');
            $record->certificate_qualification = trim($data['certificate_qualification'] ?? '');
            $record->diploma_qualification = trim($data['diploma_qualification'] ?? '');
            $record->degree_qualification = trim($data['degree_qualification'] ?? '');
            $record->postgraduate_qualification = trim($data['postgraduate_qualification'] ?? '');
            $record->professional_qualification = trim($data['professional_qualification'] ?? '');
            
            // Employment
            $record->employment_status = $data['employment_status'] ?? YouthRecord::EMP_STATUS_UNEMPLOYED;
            $record->availability_status = $data['availability_status'] ?? YouthRecord::AVAIL_STATUS_AVAILABLE;
            $record->current_employment = trim($data['current_employment'] ?? '');
            $record->preferred_location = trim($data['preferred_location'] ?? '');
            $record->salary_expectation = !empty($data['salary_expectation']) ? (float) $data['salary_expectation'] : null;
            $record->employment_notes = trim($data['employment_notes'] ?? '');
            
            // Work experiences (JSON)
            $record->work_experiences = $data['work_experiences'] ?? null;
            
            // Skills and interests
            $record->skills = trim($data['skills'] ?? '');
            $record->interests = trim($data['interests'] ?? '');
            
            // Administrative
            $record->status = $data['status'] ?? YouthRecord::STATUS_PENDING;
            $record->admin_notes = trim($data['admin_notes'] ?? '');
            
            // Only set created_by if user exists in database (handles stale JWT tokens)
            $userId = $user->id ?? null;
            if ($userId) {
                $existingUser = User::find($userId);
                $record->created_by = $existingUser ? $userId : null;
            } else {
                $record->created_by = null;
            }
            
            $record->save();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'create_youth_record',
                'youth_records',
                $record->id,
                null,
                $record->toArray()
            );

            return ResponseHelper::success($response, 'Youth record created successfully', [
                'record' => $record->toApiResponse(true)
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create youth record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update youth record
     * PUT /v1/admin/youth-records/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody() ?? [];
            $user = $request->getAttribute('user');

            $record = YouthRecord::find($id);
            if (!$record) {
                return ResponseHelper::error($response, 'Youth record not found', 404);
            }

            $oldData = $record->toArray();

            // Update fields if provided
            if (isset($data['full_name'])) $record->full_name = trim($data['full_name']);
            if (isset($data['date_of_birth'])) $record->date_of_birth = Carbon::parse($data['date_of_birth']);
            if (isset($data['gender'])) $record->gender = $data['gender'];
            if (isset($data['national_id'])) $record->national_id = trim($data['national_id']);
            if (isset($data['phone'])) $record->phone = trim($data['phone']);
            if (isset($data['email'])) $record->email = trim($data['email']);
            if (isset($data['hometown'])) $record->hometown = trim($data['hometown']);
            if (isset($data['community'])) $record->community = trim($data['community']);
            if (isset($data['location_id'])) $record->location_id = (int) $data['location_id'];
            
            // Education
            if (isset($data['education_level'])) $record->education_level = $data['education_level'];
            if (isset($data['jhs_completed'])) $record->jhs_completed = !empty($data['jhs_completed']);
            if (isset($data['shs_qualification'])) $record->shs_qualification = trim($data['shs_qualification']);
            if (isset($data['certificate_qualification'])) $record->certificate_qualification = trim($data['certificate_qualification']);
            if (isset($data['diploma_qualification'])) $record->diploma_qualification = trim($data['diploma_qualification']);
            if (isset($data['degree_qualification'])) $record->degree_qualification = trim($data['degree_qualification']);
            if (isset($data['postgraduate_qualification'])) $record->postgraduate_qualification = trim($data['postgraduate_qualification']);
            if (isset($data['professional_qualification'])) $record->professional_qualification = trim($data['professional_qualification']);
            
            // Employment
            if (isset($data['employment_status'])) {
                if (!in_array($data['employment_status'], YouthRecord::VALID_EMPLOYMENT_STATUSES)) {
                    return ResponseHelper::validationError($response, ['employment_status' => ['Invalid employment status']]);
                }
                $record->employment_status = $data['employment_status'];
            }
            if (isset($data['availability_status'])) {
                if (!in_array($data['availability_status'], YouthRecord::VALID_AVAILABILITY_STATUSES)) {
                    return ResponseHelper::validationError($response, ['availability_status' => ['Invalid availability status']]);
                }
                $record->availability_status = $data['availability_status'];
            }
            if (isset($data['current_employment'])) $record->current_employment = trim($data['current_employment']);
            if (isset($data['preferred_location'])) $record->preferred_location = trim($data['preferred_location']);
            if (isset($data['salary_expectation'])) $record->salary_expectation = (float) $data['salary_expectation'];
            if (isset($data['employment_notes'])) $record->employment_notes = trim($data['employment_notes']);
            
            // Work experiences
            if (isset($data['work_experiences'])) $record->work_experiences = $data['work_experiences'];
            
            // Skills and interests
            if (isset($data['skills'])) $record->skills = trim($data['skills']);
            if (isset($data['interests'])) $record->interests = trim($data['interests']);
            
            // Administrative
            if (isset($data['admin_notes'])) $record->admin_notes = trim($data['admin_notes']);
            
            $record->save();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'update_youth_record',
                'youth_records',
                $record->id,
                $oldData,
                $record->toArray()
            );

            return ResponseHelper::success($response, 'Youth record updated successfully', [
                'record' => $record->toApiResponse(true)
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update youth record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update youth record status (approve/reject)
     * PUT /v1/admin/youth-records/{id}/status
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody() ?? [];
            $user = $request->getAttribute('user');

            $record = YouthRecord::find($id);
            if (!$record) {
                return ResponseHelper::error($response, 'Youth record not found', 404);
            }

            if (empty($data['status']) || !in_array($data['status'], YouthRecord::VALID_STATUSES)) {
                return ResponseHelper::validationError($response, [
                    'status' => ['Invalid status. Valid values: ' . implode(', ', YouthRecord::VALID_STATUSES)]
                ]);
            }

            $oldStatus = $record->status;
            $record->status = $data['status'];
            
            if (isset($data['admin_notes'])) {
                $record->admin_notes = trim($data['admin_notes']);
            }
            
            $record->save();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'update_youth_record_status',
                'youth_records',
                $record->id,
                ['status' => $oldStatus],
                ['status' => $record->status]
            );

            return ResponseHelper::success($response, 'Youth record status updated successfully', [
                'record' => $record->toApiResponse()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update youth record status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete youth record
     * DELETE /v1/admin/youth-records/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $user = $request->getAttribute('user');

            $record = YouthRecord::find($id);
            if (!$record) {
                return ResponseHelper::error($response, 'Youth record not found', 404);
            }

            $oldData = $record->toArray();
            $record->delete();

            // Log the action
            AuditLog::logAction(
                $user->id ?? 0,
                'delete_youth_record',
                'youth_records',
                $id,
                $oldData,
                null
            );

            return ResponseHelper::success($response, 'Youth record deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete youth record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate record data
     */
    private function validateRecordData(array $data): array
    {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors['full_name'] = ['Full name is required'];
        } elseif (strlen($data['full_name']) < 2 || strlen($data['full_name']) > 200) {
            $errors['full_name'] = ['Full name must be between 2 and 200 characters'];
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Invalid email format'];
        }

        if (!empty($data['employment_status']) && !in_array($data['employment_status'], YouthRecord::VALID_EMPLOYMENT_STATUSES)) {
            $errors['employment_status'] = ['Invalid employment status. Valid: ' . implode(', ', YouthRecord::VALID_EMPLOYMENT_STATUSES)];
        }

        if (!empty($data['status']) && !in_array($data['status'], YouthRecord::VALID_STATUSES)) {
            $errors['status'] = ['Invalid status. Valid: ' . implode(', ', YouthRecord::VALID_STATUSES)];
        }

        if (!empty($data['gender']) && !in_array($data['gender'], ['male', 'female'])) {
            $errors['gender'] = ['Invalid gender. Valid: male, female'];
        }

        return $errors;
    }
}
