<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * AuditLogController
 * 
 * Handles retrieval of system audit logs.
 */
class AuditLogController
{
    /**
     * Get all audit logs with filtering and pagination
     * GET /api/admin/audit-logs
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            $search = $params['search'] ?? '';
            $actionType = $params['action_type'] ?? 'all';

            $query = AuditLog::with('user')->orderBy('created_at', 'desc');

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'LIKE', "%$search%")
                      ->orWhere('ip_address', 'LIKE', "%$search%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'LIKE', "%$search%")
                             ->orWhere('email', 'LIKE', "%$search%");
                      });
                });
            }

            // Apply action type filter
            if ($actionType !== 'all') {
                if ($actionType === 'login') {
                    $query->whereIn('action', ['login', 'login_failed', 'logout']);
                } else if ($actionType === 'create') {
                    $query->where('action', 'LIKE', 'create_%');
                } else if ($actionType === 'update') {
                    $query->where('action', 'LIKE', 'update_%');
                } else if ($actionType === 'delete') {
                    $query->where('action', 'LIKE', 'delete_%');
                } else {
                    $query->where('action', $actionType);
                }
            }

            // Pagination manually implemented if Eloquent pagination isn't globally configured or preferred
            $total = $query->count();
            $totalPages = ceil($total / $limit);
            $offset = ($page - 1) * $limit;
            
            $logs = $query->skip($offset)->take($limit)->get();

            // Transform data for frontend
            $formattedLogs = $logs->map(function ($log) {
                $status = 'success';
                if (strpos($log->action, 'failed') !== false) {
                    $status = 'failed';
                } elseif (strpos($log->action, 'warning') !== false || strpos($log->action, 'rejected') !== false) {
                    $status = 'warning';
                }

                $resource = '-';
                if (!empty($log->metadata) && isset($log->metadata['entity_type'])) {
                    $resource = ucfirst($log->metadata['entity_type']);
                    if (isset($log->metadata['entity_id'])) {
                        $resource .= ' #' . $log->metadata['entity_id'];
                    }
                }

                return [
                    'id' => $log->id,
                    'user' => $log->user ? $log->user->name : 'System/Unknown',
                    'action' => $log->action,
                    'resource' => $resource,
                    'ip' => $log->ip_address,
                    'timestamp' => $log->created_at->toIso8601String(),
                    'status' => $status,
                    'user_agent' => $log->user_agent
                ];
            });

            // Calculate summary (Basic counts based on total dataset - simplified for performance)
            // Ideally should specific queries for counts
            $successCount = AuditLog::where('action', 'NOT LIKE', '%failed%')->count();
            $failedCount = AuditLog::where('action', 'LIKE', '%failed%')->count();
            $warningCount = 0; // Placeholder

            return ResponseHelper::success($response, 'Audit logs fetched successfully', [
                'auditLogs' => $formattedLogs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages
                ],
                'summary' => [
                    'total_logs' => AuditLog::count(),
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'warning_count' => $warningCount,
                    'last_updated' => date('c')
                ]
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch audit logs', 500, $e->getMessage());
        }
    }
}
