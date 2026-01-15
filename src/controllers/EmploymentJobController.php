<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmploymentJob;
use App\Models\JobApplicant;
use App\Models\WebAdmin;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * EmploymentJobController
 * 
 * Handles CRUD operations for employment job listings.
 */
class EmploymentJobController
{
    /**
     * List all jobs
     * GET /v1/jobs
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 20);
            $status = $params['status'] ?? null;
            $category = $params['category'] ?? null;
            $jobType = $params['job_type'] ?? null;
            $experienceLevel = $params['experience_level'] ?? null;
            $location = $params['location'] ?? null;

            $query = EmploymentJob::query();

            if ($status) {
                $query->where('status', $status);
            }
            if ($category) {
                $query->where('category', $category);
            }
            if ($jobType) {
                $query->where('job_type', $jobType);
            }
            if ($experienceLevel) {
                $query->where('experience_level', $experienceLevel);
            }
            if ($location) {
                $query->where('location', 'LIKE', "%{$location}%");
            }

            $total = $query->count();
            $jobs = $query
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Jobs retrieved', [
                'jobs' => $jobs->map(fn($job) => $job->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
                'statistics' => EmploymentJob::getStatistics(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve jobs', 500, $e->getMessage());
        }
    }

    /**
     * Get public/active job listings (for public website)
     * GET /v1/jobs/public
     */
    public function publicList(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 20);
            $category = $params['category'] ?? null;
            $jobType = $params['job_type'] ?? null;

            $query = EmploymentJob::open();

            if ($category) {
                $query->where('category', $category);
            }
            if ($jobType) {
                $query->where('job_type', $jobType);
            }

            $total = $query->count();
            $jobs = $query
                ->orderBy('is_featured', 'desc')
                ->orderBy('published_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Public jobs retrieved', [
                'jobs' => $jobs->map(fn($job) => $job->toPublicArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve jobs', 500, $e->getMessage());
        }
    }

    /**
     * Get a single job by ID or slug
     * GET /v1/jobs/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];

            if (is_numeric($identifier)) {
                $job = EmploymentJob::find((int) $identifier);
            } else {
                $job = EmploymentJob::findBySlug($identifier);
            }

            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            // Increment views
            $job->incrementViews();

            return ResponseHelper::success($response, 'Job retrieved', [
                'job' => $job->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve job', 500, $e->getMessage());
        }
    }

    /**
     * Create a new job
     * POST /v1/jobs
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validate required fields
            if (empty($data['title']) || empty($data['description']) || empty($data['location'])) {
                return ResponseHelper::error($response, 'Title, description, and location are required', 400);
            }

            // Get web admin ID if the user is a web_admin, otherwise allow admin users too
            $webAdmin = WebAdmin::findByUserId($user->id);
            $createdBy = $webAdmin ? $webAdmin->id : null;

            // Generate slug
            $slug = EmploymentJob::generateSlug($data['title']);

            $job = EmploymentJob::create([
                'created_by' => $createdBy,
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'company' => $data['company'] ?? null,
                'location' => $data['location'],
                'job_type' => $data['job_type'] ?? 'full_time',
                'salary_range' => $data['salary_range'] ?? null,
                'salary_min' => $data['salary_min'] ?? null,
                'salary_max' => $data['salary_max'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'responsibilities' => $data['responsibilities'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'application_deadline' => $data['application_deadline'] ?? null,
                'application_url' => $data['application_url'] ?? null,
                'application_email' => $data['application_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'category' => $data['category'] ?? 'other',
                'experience_level' => $data['experience_level'] ?? 'entry',
                'is_featured' => $data['is_featured'] ?? false,
            ]);

            // If status is published, set published_at
            if ($job->status === 'published') {
                $job->update(['published_at' => date('Y-m-d H:i:s')]);
            }

            return ResponseHelper::success($response, 'Job created', [
                'job' => $job->toPublicArray(),
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create job', 500, $e->getMessage());
        }
    }

    /**
     * Update a job
     * PUT /v1/jobs/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            $job = EmploymentJob::find($id);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            // Get web admin ID if the user is a web_admin, otherwise allow admin users too
            $webAdmin = WebAdmin::findByUserId($user->id);
            $updatedBy = $webAdmin ? $webAdmin->id : null;

            $updateData = ['updated_by' => $updatedBy];

            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
                if ($data['title'] !== $job->title) {
                    $updateData['slug'] = EmploymentJob::generateSlug($data['title']);
                }
            }
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['company'])) $updateData['company'] = $data['company'];
            if (isset($data['location'])) $updateData['location'] = $data['location'];
            if (isset($data['job_type'])) $updateData['job_type'] = $data['job_type'];
            if (isset($data['salary_range'])) $updateData['salary_range'] = $data['salary_range'];
            if (isset($data['salary_min'])) $updateData['salary_min'] = $data['salary_min'];
            if (isset($data['salary_max'])) $updateData['salary_max'] = $data['salary_max'];
            if (isset($data['requirements'])) $updateData['requirements'] = $data['requirements'];
            if (isset($data['responsibilities'])) $updateData['responsibilities'] = $data['responsibilities'];
            if (isset($data['benefits'])) $updateData['benefits'] = $data['benefits'];
            if (isset($data['application_deadline'])) $updateData['application_deadline'] = $data['application_deadline'];
            if (isset($data['application_url'])) $updateData['application_url'] = $data['application_url'];
            if (isset($data['application_email'])) $updateData['application_email'] = $data['application_email'];
            if (isset($data['contact_phone'])) $updateData['contact_phone'] = $data['contact_phone'];
            if (isset($data['category'])) $updateData['category'] = $data['category'];
            if (isset($data['experience_level'])) $updateData['experience_level'] = $data['experience_level'];
            if (isset($data['is_featured'])) $updateData['is_featured'] = $data['is_featured'];
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
                if ($data['status'] === 'published' && $job->status !== 'published') {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }

            $job->update($updateData);

            return ResponseHelper::success($response, 'Job updated', [
                'job' => $job->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update job', 500, $e->getMessage());
        }
    }

    /**
     * Delete a job
     * DELETE /v1/jobs/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $job = EmploymentJob::find($id);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            $job->delete();

            return ResponseHelper::success($response, 'Job deleted');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete job', 500, $e->getMessage());
        }
    }

    /**
     * Publish a job
     * POST /v1/jobs/{id}/publish
     */
    public function publish(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $job = EmploymentJob::find($id);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            $job->publish();

            return ResponseHelper::success($response, 'Job published', [
                'job' => $job->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to publish job', 500, $e->getMessage());
        }
    }

    /**
     * Close a job
     * POST /v1/jobs/{id}/close
     */
    public function close(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];

            $job = EmploymentJob::find($id);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            $job->close();

            return ResponseHelper::success($response, 'Job closed', [
                'job' => $job->fresh()->toPublicArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to close job', 500, $e->getMessage());
        }
    }

    /**
     * Get applicants for a job
     * GET /v1/jobs/{id}/applicants
     */
    public function getApplicants(Request $request, Response $response, array $args): Response
    {
        try {
            $jobId = (int) $args['id'];
            $params = $request->getQueryParams();
            $page = (int) ($params['page'] ?? 1);
            $limit = (int) ($params['limit'] ?? 20);
            $status = $params['status'] ?? null;

            $job = EmploymentJob::find($jobId);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            $query = JobApplicant::where('job_id', $jobId);

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $applicants = $query
                ->orderBy('applied_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            return ResponseHelper::success($response, 'Applicants retrieved', [
                'applicants' => $applicants->map(fn($a) => $a->toArray()),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve applicants', 500, $e->getMessage());
        }
    }

    /**
     * Update applicant status
     * PUT /v1/jobs/{id}/applicants/{applicantId}
     */
    public function updateApplicantStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $jobId = (int) $args['id'];
            $applicantId = (int) $args['applicantId'];
            $data = $request->getParsedBody();

            $job = EmploymentJob::find($jobId);
            if (!$job) {
                return ResponseHelper::error($response, 'Job not found', 404);
            }

            $applicant = JobApplicant::where('id', $applicantId)
                ->where('job_id', $jobId)
                ->first();

            if (!$applicant) {
                return ResponseHelper::error($response, 'Applicant not found', 404);
            }

            if (empty($data['status'])) {
                return ResponseHelper::error($response, 'Status is required', 400);
            }

            $validStatuses = JobApplicant::getValidStatuses();
            if (!in_array($data['status'], $validStatuses)) {
                return ResponseHelper::error($response, 'Invalid status. Valid statuses: ' . implode(', ', $validStatuses), 400);
            }

            $applicant->update(['status' => $data['status']]);

            return ResponseHelper::success($response, 'Applicant status updated', [
                'applicant' => $applicant->fresh()->toArray(),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update applicant status', 500, $e->getMessage());
        }
    }
}
