<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

/**
 * UploadController
 * 
 * Handles file upload operations.
 * Provides endpoints for uploading images, documents, and other files.
 */
class UploadController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Upload a single image
     * POST /api/v1/upload/image
     */
    public function uploadImage(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            // Get the file from 'image' or 'file' key
            $file = $uploadedFiles['image'] ?? $uploadedFiles['file'] ?? null;

            if (!$file || !($file instanceof UploadedFileInterface)) {
                return ResponseHelper::error($response, 'No image file provided', 400);
            }

            // Optional subdirectory for organization
            $subDirectory = $params['category'] ?? null;

            $url = $this->uploadService->uploadFile($file, 'image', $subDirectory);

            return ResponseHelper::success($response, 'Image uploaded successfully', [
                'url' => $url,
                'type' => 'image'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Upload a banner image (larger size limit)
     * POST /api/v1/upload/banner
     */
    public function uploadBanner(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            $file = $uploadedFiles['banner'] ?? $uploadedFiles['image'] ?? $uploadedFiles['file'] ?? null;

            if (!$file || !($file instanceof UploadedFileInterface)) {
                return ResponseHelper::error($response, 'No banner file provided', 400);
            }

            $subDirectory = $params['category'] ?? null;

            $url = $this->uploadService->uploadFile($file, 'banner', $subDirectory);

            return ResponseHelper::success($response, 'Banner uploaded successfully', [
                'url' => $url,
                'type' => 'banner'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Upload a document (PDF, DOC, DOCX)
     * POST /api/v1/upload/document
     */
    public function uploadDocument(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            $file = $uploadedFiles['document'] ?? $uploadedFiles['file'] ?? null;

            if (!$file || !($file instanceof UploadedFileInterface)) {
                return ResponseHelper::error($response, 'No document file provided', 400);
            }

            $subDirectory = $params['category'] ?? null;

            $url = $this->uploadService->uploadFile($file, 'document', $subDirectory);

            return ResponseHelper::success($response, 'Document uploaded successfully', [
                'url' => $url,
                'type' => 'document'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Upload a video
     * POST /api/v1/upload/video
     */
    public function uploadVideo(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            $file = $uploadedFiles['video'] ?? $uploadedFiles['file'] ?? null;

            if (!$file || !($file instanceof UploadedFileInterface)) {
                return ResponseHelper::error($response, 'No video file provided', 400);
            }

            $subDirectory = $params['category'] ?? null;

            $url = $this->uploadService->uploadFile($file, 'video', $subDirectory);

            return ResponseHelper::success($response, 'Video uploaded successfully', [
                'url' => $url,
                'type' => 'video'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Upload multiple images
     * POST /api/v1/upload/images
     */
    public function uploadMultipleImages(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            // Handle both 'images' array and 'images[]' from form data
            $files = $uploadedFiles['images'] ?? $uploadedFiles['files'] ?? [];

            if (empty($files)) {
                return ResponseHelper::error($response, 'No image files provided', 400);
            }

            // Ensure it's an array
            if (!is_array($files)) {
                $files = [$files];
            }

            $subDirectory = $params['category'] ?? null;

            $urls = $this->uploadService->uploadMultipleFiles($files, 'image', $subDirectory);

            if (empty($urls)) {
                return ResponseHelper::error($response, 'No files were uploaded successfully', 400);
            }

            return ResponseHelper::success($response, 'Images uploaded successfully', [
                'urls' => $urls,
                'count' => count($urls),
                'type' => 'image'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Upload multiple documents
     * POST /api/v1/upload/documents
     */
    public function uploadMultipleDocuments(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $params = $request->getParsedBody() ?? [];
            
            $files = $uploadedFiles['documents'] ?? $uploadedFiles['files'] ?? [];

            if (empty($files)) {
                return ResponseHelper::error($response, 'No document files provided', 400);
            }

            if (!is_array($files)) {
                $files = [$files];
            }

            $subDirectory = $params['category'] ?? null;

            $urls = $this->uploadService->uploadMultipleFiles($files, 'document', $subDirectory);

            if (empty($urls)) {
                return ResponseHelper::error($response, 'No files were uploaded successfully', 400);
            }

            return ResponseHelper::success($response, 'Documents uploaded successfully', [
                'urls' => $urls,
                'count' => count($urls),
                'type' => 'document'
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Delete a file by URL
     * DELETE /api/v1/upload
     */
    public function deleteFile(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['url'])) {
                return ResponseHelper::error($response, 'File URL is required', 400);
            }

            $deleted = $this->uploadService->deleteFile($data['url']);

            if ($deleted) {
                return ResponseHelper::success($response, 'File deleted successfully');
            } else {
                return ResponseHelper::error($response, 'File not found or already deleted', 404);
            }
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete file', 500, $e->getMessage());
        }
    }

    /**
     * Get allowed file types and limits
     * GET /api/v1/upload/info
     */
    public function getUploadInfo(Request $request, Response $response): Response
    {
        try {
            return ResponseHelper::success($response, 'Upload information', [
                'types' => [
                    'image' => [
                        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        'max_size_mb' => $this->uploadService->getMaxFileSizeMB('image'),
                    ],
                    'banner' => [
                        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        'max_size_mb' => $this->uploadService->getMaxFileSizeMB('banner'),
                    ],
                    'document' => [
                        'extensions' => ['pdf', 'doc', 'docx'],
                        'max_size_mb' => $this->uploadService->getMaxFileSizeMB('document'),
                    ],
                    'video' => [
                        'extensions' => ['mp4', 'mpeg', 'mov', 'avi'],
                        'max_size_mb' => $this->uploadService->getMaxFileSizeMB('video'),
                    ],
                ],
                'endpoints' => [
                    'image' => 'POST /api/v1/upload/image',
                    'banner' => 'POST /api/v1/upload/banner',
                    'document' => 'POST /api/v1/upload/document',
                    'video' => 'POST /api/v1/upload/video',
                    'images' => 'POST /api/v1/upload/images',
                    'documents' => 'POST /api/v1/upload/documents',
                    'delete' => 'DELETE /api/v1/upload',
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to get upload info', 500, $e->getMessage());
        }
    }
}
