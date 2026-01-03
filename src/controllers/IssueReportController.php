<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\IssueReport;
use App\Models\IssueReportComment;
use App\Models\IssueReportStatusHistory;
use App\Models\Agent;
use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

/**
 * IssueReportController
 * 
 * Handles issue report operations.
 * - Public can submit reports
 * - Officers and Agents can manage reports
 */
class IssueReportController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * Submit a new issue report (Public)
     * POST /api/issues
     */
    public function submit(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();

            // Validation
            if (empty($data['title'])) {
                return ResponseHelper::error($response, 'Title is required', 400);
            }
            if (empty($data['description'])) {
                return ResponseHelper::error($response, 'Description is required', 400);
            }
            if (empty($data['location'])) {
                return ResponseHelper::error($response, 'Location is required', 400);
            }

            // Handle images upload
            $imagesJson = $data['images'] ?? null;
            $imageFiles = $uploadedFiles['images'] ?? [];
            if (!empty($imageFiles)) {
                if (!is_array($imageFiles)) {
                    $imageFiles = [$imageFiles];
                }
                try {
                    $uploadedImages = $this->uploadService->uploadMultipleFiles($imageFiles, 'image', 'issues');
                    if (!empty($uploadedImages)) {
                        $imagesJson = json_encode($uploadedImages);
                    }
                } catch (Exception $e) {
                    // Log but don't fail - images are optional
                    error_log('Issue images upload failed: ' . $e->getMessage());
                }
            }

            $report = IssueReport::create([
                'case_id' => IssueReport::generateCaseId(),
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'] ?? null,
                'location' => $data['location'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'images' => $imagesJson,
                'reporter_name' => $data['reporter_name'] ?? null,
                'reporter_email' => $data['reporter_email'] ?? null,
                'reporter_phone' => $data['reporter_phone'] ?? null,
                'status' => IssueReport::STATUS_SUBMITTED,
                'priority' => $data['priority'] ?? IssueReport::PRIORITY_MEDIUM,
            ]);

            // Log initial status
            IssueReportStatusHistory::logChange(
                $report->id,
                0, // System
                null,
                IssueReport::STATUS_SUBMITTED,
                'Report submitted'
            );

            return ResponseHelper::success($response, 'Issue report submitted successfully', [
                'report' => [
                    'id' => $report->id,
                    'case_id' => $report->case_id,
                    'status' => $report->status,
                ],
                'message' => 'Your issue has been submitted. Use your case ID to track progress.'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to submit issue report', 500, $e->getMessage());
        }
    }

    /**
     * Track issue by case ID (Public)
     * GET /api/issues/track/{caseId}
     */
    public function track(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::where('case_id', $args['caseId'])->first();

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            return ResponseHelper::success($response, 'Issue status fetched successfully', [
                'report' => $report->toPublicArray(),
                'history' => $report->statusHistory()
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(fn($h) => [
                        'status' => $h->new_status,
                        'notes' => $h->notes,
                        'date' => $h->created_at?->toDateTimeString(),
                    ])->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issue status', 500, $e->getMessage());
        }
    }

    /**
     * Get all issue reports (Admin/Officer)
     * GET /api/admin/issues
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $status = $params['status'] ?? null;
            $priority = $params['priority'] ?? null;
            $category = $params['category'] ?? null;

            $query = IssueReport::with(['assignedOfficer.user', 'submittedByAgent.user'])
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }
            if ($priority) {
                $query->where('priority', $priority);
            }
            if ($category) {
                $query->where('category', $category);
            }

            $total = $query->count();
            $reports = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Issue reports fetched successfully', [
                'reports' => $reports->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issue reports', 500, $e->getMessage());
        }
    }

    /**
     * Get issue statistics
     * GET /api/admin/issues/stats
     */
    public function stats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'total' => IssueReport::count(),
                'pending' => IssueReport::pending()->count(),
                'resolved' => IssueReport::where('status', IssueReport::STATUS_RESOLVED)->count(),
                'by_status' => [
                    'submitted' => IssueReport::where('status', IssueReport::STATUS_SUBMITTED)->count(),
                    'under_officer_review' => IssueReport::where('status', IssueReport::STATUS_UNDER_OFFICER_REVIEW)->count(),
                    'forwarded_to_admin' => IssueReport::where('status', IssueReport::STATUS_FORWARDED_TO_ADMIN)->count(),
                    'assigned_to_task_force' => IssueReport::where('status', IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE)->count(),
                    'assessment_in_progress' => IssueReport::where('status', IssueReport::STATUS_ASSESSMENT_IN_PROGRESS)->count(),
                    'assessment_submitted' => IssueReport::where('status', IssueReport::STATUS_ASSESSMENT_SUBMITTED)->count(),
                    'resources_allocated' => IssueReport::where('status', IssueReport::STATUS_RESOURCES_ALLOCATED)->count(),
                    'resolution_in_progress' => IssueReport::where('status', IssueReport::STATUS_RESOLUTION_IN_PROGRESS)->count(),
                    'resolution_submitted' => IssueReport::where('status', IssueReport::STATUS_RESOLUTION_SUBMITTED)->count(),
                    'resolved' => IssueReport::where('status', IssueReport::STATUS_RESOLVED)->count(),
                    'closed' => IssueReport::where('status', IssueReport::STATUS_CLOSED)->count(),
                ],
                'by_priority' => [
                    'urgent' => IssueReport::where('priority', IssueReport::PRIORITY_URGENT)->count(),
                    'high' => IssueReport::where('priority', IssueReport::PRIORITY_HIGH)->count(),
                    'medium' => IssueReport::where('priority', IssueReport::PRIORITY_MEDIUM)->count(),
                    'low' => IssueReport::where('priority', IssueReport::PRIORITY_LOW)->count(),
                ],
            ];

            return ResponseHelper::success($response, 'Statistics fetched successfully', $stats);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get single issue report
     * GET /api/admin/issues/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::with([
                'assignedOfficer.user',
                'assignedAgent.user',
                'submittedByAgent.user',
                'comments.user',
                'statusHistory.changedByUser'
            ])->find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            return ResponseHelper::success($response, 'Issue report fetched successfully', [
                'report' => $report->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issue report', 500, $e->getMessage());
        }
    }

    /**
     * Update issue report status
     * PUT /api/admin/issues/{id}/status
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['status'])) {
                return ResponseHelper::error($response, 'Status is required', 400);
            }

            $oldStatus = $report->status;
            $newStatus = $data['status'];

            // Update report
            $updateData = ['status' => $newStatus];

            // Handle officer acknowledgement
            if ($newStatus === IssueReport::STATUS_UNDER_OFFICER_REVIEW && !$report->acknowledged_at) {
                $updateData['acknowledged_at'] = date('Y-m-d H:i:s');
                $updateData['acknowledged_by'] = $user->id ?? null;
            }

            if ($newStatus === IssueReport::STATUS_RESOLVED && !$report->resolved_at) {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
                $updateData['resolved_by'] = $user->id ?? null;
                $updateData['resolution_notes'] = $data['notes'] ?? null;
            }

            $report->update($updateData);

            // Log status change
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id ?? 0,
                $oldStatus,
                $newStatus,
                $data['notes'] ?? null
            );

            return ResponseHelper::success($response, 'Status updated successfully', [
                'report' => $report->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update status', 500, $e->getMessage());
        }
    }

    /**
     * Assign issue to officer
     * PUT /api/admin/issues/{id}/assign
     */
    public function assign(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            $updateData = [];

            if (isset($data['officer_id'])) {
                $updateData['assigned_officer_id'] = $data['officer_id'];
            }

            if (isset($data['agent_id'])) {
                $updateData['assigned_agent_id'] = $data['agent_id'];
            }

            if (empty($updateData)) {
                return ResponseHelper::error($response, 'Officer or agent ID is required', 400);
            }

            $report->update($updateData);

            // Log assignment
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id ?? 0,
                $report->status,
                $report->status,
                'Assigned to staff'
            );

            return ResponseHelper::success($response, 'Issue assigned successfully', [
                'report' => $report->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to assign issue', 500, $e->getMessage());
        }
    }

    /**
     * Add comment to issue
     * POST /api/admin/issues/{id}/comments
     */
    public function addComment(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['comment'])) {
                return ResponseHelper::error($response, 'Comment is required', 400);
            }

            $comment = IssueReportComment::create([
                'issue_report_id' => $report->id,
                'user_id' => $user->id,
                'comment' => $data['comment'],
                'is_internal' => $data['is_internal'] ?? true,
                'attachments' => $data['attachments'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Comment added successfully', [
                'comment' => $comment->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to add comment', 500, $e->getMessage());
        }
    }

    /**
     * Agent submit issue report
     * POST /api/agent/issues
     */
    public function agentSubmit(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();
            $user = $request->getAttribute('user');

            // Get agent profile
            $agent = Agent::findByUserId($user->id);

            if (!$agent) {
                return ResponseHelper::error($response, 'Agent profile not found', 404);
            }

            if (!$agent->canSubmitReports()) {
                return ResponseHelper::error($response, 'You do not have permission to submit reports', 403);
            }

            // Validation
            if (empty($data['title'])) {
                return ResponseHelper::error($response, 'Title is required', 400);
            }
            if (empty($data['description'])) {
                return ResponseHelper::error($response, 'Description is required', 400);
            }
            if (empty($data['location'])) {
                return ResponseHelper::error($response, 'Location is required', 400);
            }

            // Handle images upload
            $imagesJson = $data['images'] ?? null;
            $imageFiles = $uploadedFiles['images'] ?? [];
            if (!empty($imageFiles)) {
                if (!is_array($imageFiles)) {
                    $imageFiles = [$imageFiles];
                }
                try {
                    $uploadedImages = $this->uploadService->uploadMultipleFiles($imageFiles, 'image', 'issues');
                    if (!empty($uploadedImages)) {
                        $imagesJson = json_encode($uploadedImages);
                    }
                } catch (Exception $e) {
                    error_log('Issue images upload failed: ' . $e->getMessage());
                }
            }

            $report = IssueReport::create([
                'case_id' => IssueReport::generateCaseId(),
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'] ?? null,
                'location' => $data['location'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'images' => $imagesJson,
                'reporter_name' => $data['reporter_name'] ?? null,
                'reporter_email' => $data['reporter_email'] ?? null,
                'reporter_phone' => $data['reporter_phone'] ?? null,
                'submitted_by_agent_id' => $agent->id,
                'status' => IssueReport::STATUS_SUBMITTED,
                'priority' => $data['priority'] ?? IssueReport::PRIORITY_MEDIUM,
            ]);

            // Increment agent's report count
            $agent->incrementReportsSubmitted();
            $agent->updateLastActive();

            // Log status
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id,
                null,
                IssueReport::STATUS_SUBMITTED,
                'Submitted by agent'
            );

            return ResponseHelper::success($response, 'Issue report submitted successfully', [
                'report' => $report->toPublicArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to submit issue report', 500, $e->getMessage());
        }
    }

    /* -----------------------------------------------------------------
     |  Admin Workflow Methods
     | -----------------------------------------------------------------
     */

    /**
     * Assign issue to task force (Admin)
     * PUT /api/admin/issues/{id}/assign-task-force
     */
    public function assignToTaskForce(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['task_force_id'])) {
                return ResponseHelper::error($response, 'Task force member ID is required', 400);
            }

            $oldStatus = $report->status;
            $report->assignToTaskForce((int)$data['task_force_id']);

            // Log status change
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id ?? 0,
                $oldStatus,
                IssueReport::STATUS_ASSIGNED_TO_TASK_FORCE,
                'Assigned to task force for investigation'
            );

            return ResponseHelper::success($response, 'Issue assigned to task force successfully', [
                'report' => $report->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to assign to task force', 500, $e->getMessage());
        }
    }

    /**
     * Allocate resources to issue (Admin)
     * PUT /api/admin/issues/{id}/allocate-resources
     */
    public function allocateResources(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            if ($report->status !== IssueReport::STATUS_ASSESSMENT_SUBMITTED) {
                return ResponseHelper::error($response, 'Issue must have submitted assessment before allocating resources', 400);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['budget'])) {
                return ResponseHelper::error($response, 'Budget is required', 400);
            }

            $oldStatus = $report->status;
            $report->allocateResources(
                (int)$user->id,
                (float)$data['budget'],
                $data['resources'] ?? null
            );

            // Log status change
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id ?? 0,
                $oldStatus,
                IssueReport::STATUS_RESOURCES_ALLOCATED,
                'Resources allocated: GHS ' . number_format((float)$data['budget'], 2)
            );

            return ResponseHelper::success($response, 'Resources allocated successfully', [
                'report' => $report->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to allocate resources', 500, $e->getMessage());
        }
    }

    /**
     * Review assessment report (Admin)
     * PUT /api/admin/issues/{id}/review-assessment
     */
    public function reviewAssessment(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::with('assessmentReport')->find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            if (!$report->assessmentReport) {
                return ResponseHelper::error($response, 'No assessment report found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['action'])) {
                return ResponseHelper::error($response, 'Action is required (approve/reject/revision)', 400);
            }

            $assessment = $report->assessmentReport;
            $action = $data['action'];
            $notes = $data['notes'] ?? null;

            switch ($action) {
                case 'approve':
                    $assessment->approve((int)$user->id, $notes);
                    break;
                case 'reject':
                    $assessment->reject((int)$user->id, $notes);
                    break;
                case 'revision':
                    $assessment->requestRevision((int)$user->id, $notes);
                    break;
                default:
                    return ResponseHelper::error($response, 'Invalid action', 400);
            }

            return ResponseHelper::success($response, 'Assessment reviewed successfully', [
                'assessment' => $assessment->fresh()->toPublicArray(),
                'report' => $report->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to review assessment', 500, $e->getMessage());
        }
    }

    /**
     * Review resolution report and finalize (Admin)
     * PUT /api/admin/issues/{id}/review-resolution
     */
    public function reviewResolution(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::with('resolutionReport')->find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            if (!$report->resolutionReport) {
                return ResponseHelper::error($response, 'No resolution report found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            if (empty($data['action'])) {
                return ResponseHelper::error($response, 'Action is required (approve/reject/revision)', 400);
            }

            $resolution = $report->resolutionReport;
            $action = $data['action'];
            $notes = $data['notes'] ?? null;

            switch ($action) {
                case 'approve':
                    $resolution->approve((int)$user->id, $notes);
                    // Log final resolution
                    IssueReportStatusHistory::logChange(
                        $report->id,
                        $user->id ?? 0,
                        $report->status,
                        IssueReport::STATUS_RESOLVED,
                        'Issue resolved and closed'
                    );
                    break;
                case 'reject':
                    $resolution->reject((int)$user->id, $notes);
                    break;
                case 'revision':
                    $resolution->requestRevision((int)$user->id, $notes);
                    break;
                default:
                    return ResponseHelper::error($response, 'Invalid action', 400);
            }

            return ResponseHelper::success($response, 'Resolution reviewed successfully', [
                'resolution' => $resolution->fresh()->toPublicArray(),
                'report' => $report->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to review resolution', 500, $e->getMessage());
        }
    }

    /**
     * Officer forwards issue to admin (Officer)
     * PUT /api/officer/issues/{id}/forward
     */
    public function officerForward(Request $request, Response $response, array $args): Response
    {
        try {
            $report = IssueReport::find($args['id']);

            if (!$report) {
                return ResponseHelper::error($response, 'Issue report not found', 404);
            }

            $user = $request->getAttribute('user');
            $data = $request->getParsedBody();

            $oldStatus = $report->status;
            $report->forwardToAdmin();

            // Log status change
            IssueReportStatusHistory::logChange(
                $report->id,
                $user->id ?? 0,
                $oldStatus,
                IssueReport::STATUS_FORWARDED_TO_ADMIN,
                $data['notes'] ?? 'Forwarded to admin by officer'
            );

            return ResponseHelper::success($response, 'Issue forwarded to admin successfully', [
                'report' => $report->fresh()->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to forward issue', 500, $e->getMessage());
        }
    }

    /**
     * Get issues awaiting admin action
     * GET /api/admin/issues/awaiting-action
     */
    public function awaitingAction(Request $request, Response $response): Response
    {
        try {
            $reports = IssueReport::with(['assignedTaskForce.user', 'assessmentReport', 'resolutionReport'])
                ->whereIn('status', [
                    IssueReport::STATUS_FORWARDED_TO_ADMIN,
                    IssueReport::STATUS_ASSESSMENT_SUBMITTED,
                    IssueReport::STATUS_RESOLUTION_SUBMITTED,
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success($response, 'Issues awaiting action fetched successfully', [
                'reports' => $reports->map(fn($r) => $r->toFullArray())->toArray(),
                'counts' => [
                    'awaiting_assignment' => $reports->where('status', IssueReport::STATUS_FORWARDED_TO_ADMIN)->count(),
                    'awaiting_assessment_review' => $reports->where('status', IssueReport::STATUS_ASSESSMENT_SUBMITTED)->count(),
                    'awaiting_resolution_review' => $reports->where('status', IssueReport::STATUS_RESOLUTION_SUBMITTED)->count(),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch issues', 500, $e->getMessage());
        }
    }
}
