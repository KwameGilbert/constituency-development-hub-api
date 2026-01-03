<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BlogPost;
use App\Models\WebAdmin;
use App\Services\UploadService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

/**
 * BlogPostController
 * 
 * Handles CRUD operations for blog posts/news articles.
 * Web Admins only for create/update/delete.
 */
class BlogPostController
{
    private UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    /**
     * Get all published posts (Public)
     * GET /api/blog
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $category = $params['category'] ?? null;
            $featured = isset($params['featured']) ? filter_var($params['featured'], FILTER_VALIDATE_BOOLEAN) : null;

            $query = BlogPost::published()->orderBy('published_at', 'desc');

            if ($category) {
                $query->where('category', $category);
            }

            if ($featured !== null) {
                $query->where('is_featured', $featured);
            }

            $total = $query->count();
            $posts = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Blog posts fetched successfully', [
                'posts' => $posts->map(fn($post) => $post->toPublicArray())->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch blog posts', 500, $e->getMessage());
        }
    }

    /**
     * Get featured posts (Public)
     * GET /api/blog/featured
     */
    public function featured(Request $request, Response $response): Response
    {
        try {
            $limit = min((int)($request->getQueryParams()['limit'] ?? 3), 10);
            $posts = BlogPost::published()->featured()->orderBy('published_at', 'desc')->take($limit)->get();

            return ResponseHelper::success($response, 'Featured posts fetched successfully', [
                'posts' => $posts->map(fn($post) => $post->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch featured posts', 500, $e->getMessage());
        }
    }

    /**
     * Get single post by slug (Public)
     * GET /api/blog/{slug}
     */
    public function showBySlug(Request $request, Response $response, array $args): Response
    {
        try {
            $post = BlogPost::where('slug', $args['slug'])->published()->first();

            if (!$post) {
                return ResponseHelper::error($response, 'Blog post not found', 404);
            }

            // Increment views
            $post->incrementViews();

            return ResponseHelper::success($response, 'Blog post fetched successfully', [
                'post' => $post->toPublicArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch blog post', 500, $e->getMessage());
        }
    }

    /**
     * Get all posts including drafts (Admin)
     * GET /api/admin/blog
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 10), 50);
            $status = $params['status'] ?? null;

            $query = BlogPost::orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            $total = $query->count();
            $posts = $query->skip(($page - 1) * $limit)->take($limit)->get();

            return ResponseHelper::success($response, 'Blog posts fetched successfully', [
                'posts' => $posts->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                ]
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch blog posts', 500, $e->getMessage());
        }
    }

    /**
     * Get single post by ID (Admin)
     * GET /api/admin/blog/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $post = BlogPost::find($args['id']);

            if (!$post) {
                return ResponseHelper::error($response, 'Blog post not found', 404);
            }

            return ResponseHelper::success($response, 'Blog post fetched successfully', [
                'post' => $post->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch blog post', 500, $e->getMessage());
        }
    }

    /**
     * Create new post
     * POST /api/admin/blog
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['title'])) {
                return ResponseHelper::error($response, 'Title is required', 400);
            }

            // Handle image upload
            $imageUrl = $data['image'] ?? null;
            $imageFile = $uploadedFiles['image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    $imageUrl = $this->uploadService->uploadFile($imageFile, 'image', 'blog');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Generate slug if not provided
            $slug = $data['slug'] ?? $this->generateSlug($data['title']);

            // Check for unique slug
            if (BlogPost::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . time();
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $post = BlogPost::create([
                'created_by' => $webAdmin->id ?? null,
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
                'image' => $imageUrl,
                'author' => $data['author'] ?? null,
                'category' => $data['category'] ?? null,
                'tags' => $data['tags'] ?? null,
                'status' => $data['status'] ?? BlogPost::STATUS_DRAFT,
                'is_featured' => $data['is_featured'] ?? false,
                'published_at' => ($data['status'] ?? '') === BlogPost::STATUS_PUBLISHED ? date('Y-m-d H:i:s') : null,
            ]);

            return ResponseHelper::success($response, 'Blog post created successfully', [
                'post' => $post->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create blog post', 500, $e->getMessage());
        }
    }

    /**
     * Update post
     * PUT /api/admin/blog/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $post = BlogPost::find($args['id']);

            if (!$post) {
                return ResponseHelper::error($response, 'Blog post not found', 404);
            }

            $data = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles();
            $user = $request->getAttribute('user');

            // Handle image upload
            $imageUrl = $data['image'] ?? $post->image;
            $imageFile = $uploadedFiles['image'] ?? null;
            if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                try {
                    // Upload new image and delete old one
                    $imageUrl = $this->uploadService->replaceFile($imageFile, $post->image, 'image', 'blog');
                } catch (Exception $e) {
                    return ResponseHelper::error($response, 'Image upload failed: ' . $e->getMessage(), 400);
                }
            }

            // Handle publishing
            $publishedAt = $post->published_at;
            if (isset($data['status']) && $data['status'] === BlogPost::STATUS_PUBLISHED && !$post->published_at) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $post->update([
                'updated_by' => $webAdmin->id ?? null,
                'title' => $data['title'] ?? $post->title,
                'slug' => $data['slug'] ?? $post->slug,
                'excerpt' => $data['excerpt'] ?? $post->excerpt,
                'content' => $data['content'] ?? $post->content,
                'image' => $imageUrl,
                'author' => $data['author'] ?? $post->author,
                'category' => $data['category'] ?? $post->category,
                'tags' => $data['tags'] ?? $post->tags,
                'status' => $data['status'] ?? $post->status,
                'is_featured' => $data['is_featured'] ?? $post->is_featured,
                'published_at' => $publishedAt,
            ]);

            return ResponseHelper::success($response, 'Blog post updated successfully', [
                'post' => $post->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update blog post', 500, $e->getMessage());
        }
    }

    /**
     * Delete post
     * DELETE /api/admin/blog/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $post = BlogPost::find($args['id']);

            if (!$post) {
                return ResponseHelper::error($response, 'Blog post not found', 404);
            }

            $post->delete();

            return ResponseHelper::success($response, 'Blog post deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete blog post', 500, $e->getMessage());
        }
    }

    /**
     * Publish post
     * POST /api/admin/blog/{id}/publish
     */
    public function publish(Request $request, Response $response, array $args): Response
    {
        try {
            $post = BlogPost::find($args['id']);

            if (!$post) {
                return ResponseHelper::error($response, 'Blog post not found', 404);
            }

            $post->publish();

            return ResponseHelper::success($response, 'Blog post published successfully', [
                'post' => $post->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to publish blog post', 500, $e->getMessage());
        }
    }

    /**
     * Get categories list
     * GET /api/blog/categories
     */
    public function categories(Request $request, Response $response): Response
    {
        try {
            $categories = BlogPost::published()
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category');

            return ResponseHelper::success($response, 'Categories fetched successfully', [
                'categories' => $categories->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch categories', 500, $e->getMessage());
        }
    }

    /**
     * Generate URL-friendly slug from title
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
