<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

class UploadController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Generic upload handler (defaults to image type)
     * POST /v1/admin/upload
     */
    public function upload(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['file'] ?? null;
            $data = $request->getParsedBody() ?? [];
            $folder = $data['folder'] ?? null;
            $type = $data['type'] ?? 'image'; // Default to image if not specified

            if (!$uploadedFile instanceof UploadedFileInterface) {
                return ResponseHelper::error($response, 'No file uploaded', 400);
            }

            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return ResponseHelper::error($response, 'File upload error: ' . $uploadedFile->getError(), 400);
            }

            $url = $this->uploadService->uploadFile($uploadedFile, $type, $folder);

            return ResponseHelper::success($response, 'File uploaded successfully', [
                'url' => $url,
                'filename' => $uploadedFile->getClientFilename()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Upload failed', 500, $e->getMessage());
        }
    }

    public function uploadImage(Request $request, Response $response): Response
    {
        return $this->upload($request, $response);
    }

    public function uploadBanner(Request $request, Response $response): Response
    {
        // Force type to banner
        $request = $request->withParsedBody(array_merge($request->getParsedBody() ?? [], ['type' => 'banner']));
        return $this->upload($request, $response);
    }

    public function uploadDocument(Request $request, Response $response): Response
    {
        // Force type to document
        $request = $request->withParsedBody(array_merge($request->getParsedBody() ?? [], ['type' => 'document']));
        return $this->upload($request, $response);
    }

    public function uploadVideo(Request $request, Response $response): Response
    {
        // Force type to video
        $request = $request->withParsedBody(array_merge($request->getParsedBody() ?? [], ['type' => 'video']));
        return $this->upload($request, $response);
    }

    public function uploadMultipleImages(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $files = $uploadedFiles['files'] ?? [];
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $data = $request->getParsedBody() ?? [];
            $folder = $data['folder'] ?? null;

            $urls = $this->uploadService->uploadMultipleFiles($files, 'image', $folder);

            return ResponseHelper::success($response, 'Files uploaded successfully', [
                'urls' => $urls
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Upload failed', 500, $e->getMessage());
        }
    }

    public function uploadMultipleDocuments(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            $files = $uploadedFiles['files'] ?? [];
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $data = $request->getParsedBody() ?? [];
            $folder = $data['folder'] ?? null;

            $urls = $this->uploadService->uploadMultipleFiles($files, 'document', $folder);

            return ResponseHelper::success($response, 'Files uploaded successfully', [
                'urls' => $urls
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Upload failed', 500, $e->getMessage());
        }
    }

    public function deleteFile(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $url = $data['url'] ?? null;

            if (!$url) {
                return ResponseHelper::error($response, 'URL is required', 400);
            }

            $this->uploadService->deleteFile($url);

            return ResponseHelper::success($response, 'File deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Delete failed', 500, $e->getMessage());
        }
    }
    
    public function getUploadInfo(Request $request, Response $response): Response
    {
        return ResponseHelper::success($response, 'Upload service info', [
            'max_post_size' => ini_get('post_max_size'),
            'max_upload_size' => ini_get('upload_max_filesize'),
        ]);
    }
}
