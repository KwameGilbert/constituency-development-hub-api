<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Gallery;
use App\Helper\ResponseHelper;
use App\Services\UploadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Exception;

/**
 * GalleryController
 * 
 * Handles management of gallery albums.
 */
class GalleryController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (!Capsule::schema()->hasTable('galleries')) {
            Capsule::schema()->create('galleries', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 255);
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->string('category', 50);
                $table->date('date');
                $table->string('location', 255);
                $table->string('cover_image', 500);
                $table->json('images')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->index('category');
                $table->index('status');
                $table->index('date');
            });
        }
    }

    /**
     * Get all galleries for admin
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $galleries = Gallery::orderBy('date', 'desc')->get();
            return ResponseHelper::success($response, 'Galleries retrieved successfully', [
                'galleries' => $galleries
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve galleries: ' . $e->getMessage());
        }
    }

    /**
     * Get all galleries for public website
     */
    public function publicIndex(Request $request, Response $response): Response
    {
        try {
            $galleries = Gallery::where('status', 'active')
                ->orderBy('date', 'desc')
                ->get();

            $formattedGalleries = $galleries->map(fn($gallery) => $gallery->toPublicArray());

            return ResponseHelper::success($response, 'Galleries retrieved successfully', [
                'galleries' => $formattedGalleries
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve galleries: ' . $e->getMessage());
        }
    }

    /**
     * Get a single gallery by ID or slug
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $idOrSlug = $args['id'];
            $gallery = is_numeric($idOrSlug) 
                ? Gallery::find($idOrSlug) 
                : Gallery::where('slug', $idOrSlug)->first();

            if (!$gallery) {
                return ResponseHelper::error($response, 'Gallery not found', 404);
            }

            return ResponseHelper::success($response, 'Gallery retrieved successfully', [
                'gallery' => $gallery
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve gallery: ' . $e->getMessage());
        }
    }

    /**
     * Create a new gallery album
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $files = $request->getUploadedFiles();

            // Validation
            if (empty($data['title'])) {
                return ResponseHelper::error($response, 'Title is required', 400);
            }

            // Handle cover image
            $coverImageUrl = '';
            if (isset($files['cover_image']) && $files['cover_image']->getError() === UPLOAD_ERR_OK) {
                $coverImageUrl = $this->uploadService->uploadFile($files['cover_image'], 'image', 'galleries');
            } else {
                return ResponseHelper::error($response, 'Cover image is required', 400);
            }

            // Handle gallery images
            $galleryImages = [];
            if (isset($files['gallery_images'])) {
                $uploadedFiles = $files['gallery_images'];
                $captions = $data['gallery_captions'] ?? [];

                // Re-indexing files if they are not in array format but individual named keys (some clients do this)
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $index => $file) {
                    if ($file->getError() === UPLOAD_ERR_OK) {
                        try {
                            $url = $this->uploadService->uploadFile($file, 'image', 'galleries/items');
                            $galleryImages[] = [
                                'url' => $url,
                                'caption' => $captions[$index] ?? ''
                            ];
                        } catch (Exception $e) {
                            // Log and skip failed images
                            error_log("Gallery image upload failed: " . $e->getMessage());
                        }
                    }
                }
            }

            $gallery = Gallery::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']) . '-' . uniqid(),
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'General',
                'date' => $data['date'] ?? date('Y-m-d'),
                'location' => $data['location'] ?? 'Unknown',
                'cover_image' => $coverImageUrl,
                'images' => $galleryImages,
                'status' => $data['status'] ?? 'active',
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return ResponseHelper::success($response, 'Gallery created successfully', [
                'gallery' => $gallery
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create gallery: ' . $e->getMessage());
        }
    }

    /**
     * Update a gallery album
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return ResponseHelper::error($response, 'Gallery not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $files = $request->getUploadedFiles();

            // Handle cover image update
            if (isset($files['cover_image']) && $files['cover_image']->getError() === UPLOAD_ERR_OK) {
                // Delete old one if exists
                if ($gallery->cover_image) {
                    $this->uploadService->deleteFile($gallery->cover_image);
                }
                $gallery->cover_image = $this->uploadService->uploadFile($files['cover_image'], 'image', 'galleries');
            }

            // Handle gallery images update
            // Note: For simplicity in the first version, we'll replace the whole gallery if new images are sent, 
            // OR if the JSON 'existing_images' is sent, we'll merge them.
            // A more robust way would be to send image IDs or URLs to KEEP.
            
            $existingImages = [];
            if (isset($data['existing_images'])) {
                $existingImages = is_string($data['existing_images']) 
                    ? json_decode($data['existing_images'], true) 
                    : $data['existing_images'];
            } else {
                // If not provided, assume we keep current ones (useful for partial updates)
                $existingImages = $gallery->images ?? [];
            }

            $newGalleryImages = [];
            if (isset($files['gallery_images'])) {
                $uploadedFiles = $files['gallery_images'];
                $newCaptions = $data['new_gallery_captions'] ?? [];

                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $index => $file) {
                    if ($file->getError() === UPLOAD_ERR_OK) {
                        try {
                            $url = $this->uploadService->uploadFile($file, 'image', 'galleries/items');
                            $newGalleryImages[] = [
                                'url' => $url,
                                'caption' => $newCaptions[$index] ?? ''
                            ];
                        } catch (Exception $e) {
                            error_log("Gallery image upload failed: " . $e->getMessage());
                        }
                    }
                }
            }

            // Combine existing and new
            $gallery->images = array_merge($existingImages, $newGalleryImages);

            $gallery->update([
                'title' => $data['title'] ?? $gallery->title,
                'description' => $data['description'] ?? $gallery->description,
                'category' => $data['category'] ?? $gallery->category,
                'date' => $data['date'] ?? $gallery->date,
                'location' => $data['location'] ?? $gallery->location,
                'status' => $data['status'] ?? $gallery->status,
                'updated_by' => $request->getAttribute('user_id'),
            ]);

            return ResponseHelper::success($response, 'Gallery updated successfully', [
                'gallery' => $gallery
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update gallery: ' . $e->getMessage());
        }
    }

    /**
     * Delete a gallery album
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return ResponseHelper::error($response, 'Gallery not found', 404);
            }

            // Delete cover image
            if ($gallery->cover_image) {
                $this->uploadService->deleteFile($gallery->cover_image);
            }

            // Delete gallery images
            if ($gallery->images && is_array($gallery->images)) {
                foreach ($gallery->images as $img) {
                    if (!empty($img['url'])) {
                        $this->uploadService->deleteFile($img['url']);
                    }
                }
            }

            $gallery->delete();

            return ResponseHelper::success($response, 'Gallery deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete gallery: ' . $e->getMessage());
        }
    }
}
