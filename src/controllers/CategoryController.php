<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helper\ResponseHelper;
use App\Models\Category;
use App\Models\AuditLog;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Str;
use Exception;

/**
 * CategoryController
 * 
 * Handles CRUD operations for categories (parent of sectors)
 */
class CategoryController
{
    /**
     * List all categories (public)
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $categories = Category::active()->get()->map(fn($cat) => $cat->toPublicArray());

            return ResponseHelper::success($response, 'Categories retrieved successfully', [
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all categories for admin (includes inactive)
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            
            $query = Category::query()->orderBy('display_order');
            
            // Filter by status
            if (!empty($params['status'])) {
                $query->where('status', $params['status']);
            }
            
            // Search by name
            if (!empty($params['search'])) {
                $query->where('name', 'like', '%' . $params['search'] . '%');
            }

            $categories = $query->get()->map(fn($cat) => $cat->toFullArray());

            return ResponseHelper::success($response, 'Categories retrieved successfully', [
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single category by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $category = Category::find($args['id']);

            if (!$category) {
                return ResponseHelper::error($response, 'Category not found', 404);
            }

            return ResponseHelper::success($response, 'Category retrieved successfully', [
                'category' => $category->toFullArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get category by slug (public)
     */
    public function showBySlug(Request $request, Response $response, array $args): Response
    {
        try {
            $category = Category::findBySlug($args['slug']);

            if (!$category) {
                return ResponseHelper::error($response, 'Category not found', 404);
            }

            // Include sectors with subsector counts
            $sectors = $category->sectors()
                ->where('status', 'active')
                ->get()
                ->map(fn($sector) => $sector->toPublicArray());

            return ResponseHelper::success($response, 'Category retrieved successfully', [
                'category' => $category->toPublicArray(),
                'sectors' => $sectors
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to retrieve category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new category
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validation
            $errors = $this->validateCategoryData($data);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Generate slug
            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            // Get max display order
            $maxOrder = Category::max('display_order') ?? 0;

            $category = new Category([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'color' => $data['color'] ?? null,
                'display_order' => $maxOrder + 1,
                'status' => $data['status'] ?? Category::STATUS_ACTIVE,
                'created_by' => $user->id ?? null,
            ]);
            $category->save();

            // Audit log
            AuditLog::logAction(
                $user->id ?? null,
                'create_category',
                'Category',
                $category->id,
                null,
                $category->toArray()
            );

            return ResponseHelper::success($response, 'Category created successfully', [
                'category' => $category->toFullArray()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $category = Category::find($args['id']);

            if (!$category) {
                return ResponseHelper::error($response, 'Category not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');
            $oldData = $category->toArray();

            // Validation
            $errors = $this->validateCategoryData($data, true, $category->id);
            if (!empty($errors)) {
                return ResponseHelper::validationError($response, $errors);
            }

            // Update fields
            if (isset($data['name'])) {
                $category->name = $data['name'];
                // Regenerate slug if name changed
                $slug = Str::slug($data['name']);
                if ($slug !== $category->slug) {
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                        $slug = $originalSlug . '-' . $counter++;
                    }
                    $category->slug = $slug;
                }
            }
            if (array_key_exists('description', $data)) {
                $category->description = $data['description'];
            }
            if (array_key_exists('icon', $data)) {
                $category->icon = $data['icon'];
            }
            if (array_key_exists('color', $data)) {
                $category->color = $data['color'];
            }
            if (isset($data['status'])) {
                $category->status = $data['status'];
            }

            $category->updated_by = $user->id ?? null;
            $category->save();

            // Audit log
            AuditLog::logAction(
                $user->id ?? null,
                'update_category',
                'Category',
                $category->id,
                $oldData,
                $category->toArray()
            );

            return ResponseHelper::success($response, 'Category updated successfully', [
                'category' => $category->toFullArray()
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $category = Category::find($args['id']);

            if (!$category) {
                return ResponseHelper::error($response, 'Category not found', 404);
            }

            // Check for associated sectors
            $sectorsCount = $category->getSectorsCount();
            if ($sectorsCount > 0) {
                return ResponseHelper::error(
                    $response,
                    "Cannot delete category with {$sectorsCount} associated sector(s). Please reassign or delete sectors first.",
                    400
                );
            }

            $user = $request->getAttribute('user');
            $oldData = $category->toArray();

            $category->delete();

            // Audit log
            AuditLog::logAction(
                $user->id ?? null,
                'delete_category',
                'Category',
                (int)$args['id'],
                $oldData,
                null
            );

            return ResponseHelper::success($response, 'Category deleted successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['ordered_ids']) || !is_array($data['ordered_ids'])) {
                return ResponseHelper::validationError($response, ['ordered_ids' => 'Array of category IDs is required']);
            }

            foreach ($data['ordered_ids'] as $index => $id) {
                Category::where('id', $id)->update(['display_order' => $index + 1]);
            }

            return ResponseHelper::success($response, 'Categories reordered successfully');

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder categories: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate category data
     */
    private function validateCategoryData(array $data, bool $isUpdate = false, ?int $categoryId = null): array
    {
        $errors = [];

        if (!$isUpdate || isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'Category name is required';
            } elseif (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                $errors['name'] = 'Category name must be between 2 and 100 characters';
            }
        }

        if (isset($data['status']) && !in_array($data['status'], [Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])) {
            $errors['status'] = 'Invalid status value';
        }

        return $errors;
    }
}
