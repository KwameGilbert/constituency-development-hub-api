<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FAQ;
use App\Models\WebAdmin;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * FAQController
 * 
 * Handles CRUD operations for FAQs.
 * Web Admins only for create/update/delete.
 */
class FAQController
{
    /**
     * Get all active FAQs (Public)
     * GET /api/faqs
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $category = $request->getQueryParams()['category'] ?? null;

            $query = FAQ::active();

            if ($category) {
                $query->byCategory($category);
            }

            $faqs = $query->get();

            return ResponseHelper::success($response, 'FAQs fetched successfully', [
                'faqs' => $faqs->map(fn($faq) => $faq->toPublicArray())->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch FAQs', 500, $e->getMessage());
        }
    }

    /**
     * Get FAQ categories (Public)
     * GET /api/faqs/categories
     */
    public function categories(Request $request, Response $response): Response
    {
        try {
            $categories = FAQ::getCategories();

            return ResponseHelper::success($response, 'Categories fetched successfully', [
                'categories' => $categories->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch categories', 500, $e->getMessage());
        }
    }

    /**
     * Get all FAQs including inactive (Admin)
     * GET /api/admin/faqs
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $faqs = FAQ::orderBy('display_order')->get();

            return ResponseHelper::success($response, 'FAQs fetched successfully', [
                'faqs' => $faqs->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch FAQs', 500, $e->getMessage());
        }
    }

    /**
     * Get single FAQ
     * GET /api/admin/faqs/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $faq = FAQ::find($args['id']);

            if (!$faq) {
                return ResponseHelper::error($response, 'FAQ not found', 404);
            }

            return ResponseHelper::success($response, 'FAQ fetched successfully', [
                'faq' => $faq->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch FAQ', 500, $e->getMessage());
        }
    }

    /**
     * Create new FAQ
     * POST /api/admin/faqs
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['question'])) {
                return ResponseHelper::error($response, 'Question is required', 400);
            }
            if (empty($data['answer'])) {
                return ResponseHelper::error($response, 'Answer is required', 400);
            }

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $faq = FAQ::create([
                'created_by' => $webAdmin->id ?? null,
                'question' => $data['question'],
                'answer' => $data['answer'],
                'category' => $data['category'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'status' => $data['status'] ?? FAQ::STATUS_ACTIVE,
            ]);

            return ResponseHelper::success($response, 'FAQ created successfully', [
                'faq' => $faq->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create FAQ', 500, $e->getMessage());
        }
    }

    /**
     * Update FAQ
     * PUT /api/admin/faqs/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $faq = FAQ::find($args['id']);

            if (!$faq) {
                return ResponseHelper::error($response, 'FAQ not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Fetch the web-admin profile for this user
            $webAdmin = $user ? WebAdmin::findByUserId($user->id) : null;

            $faq->update([
                'updated_by' => $webAdmin->id ?? null,
                'question' => $data['question'] ?? $faq->question,
                'answer' => $data['answer'] ?? $faq->answer,
                'category' => $data['category'] ?? $faq->category,
                'display_order' => $data['display_order'] ?? $faq->display_order,
                'status' => $data['status'] ?? $faq->status,
            ]);

            return ResponseHelper::success($response, 'FAQ updated successfully', [
                'faq' => $faq->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update FAQ', 500, $e->getMessage());
        }
    }

    /**
     * Delete FAQ
     * DELETE /api/admin/faqs/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $faq = FAQ::find($args['id']);

            if (!$faq) {
                return ResponseHelper::error($response, 'FAQ not found', 404);
            }

            $faq->delete();

            return ResponseHelper::success($response, 'FAQ deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete FAQ', 500, $e->getMessage());
        }
    }

    /**
     * Reorder FAQs
     * PUT /api/admin/faqs/reorder
     */
    public function reorder(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['order']) || !is_array($data['order'])) {
                return ResponseHelper::error($response, 'Order array is required', 400);
            }

            foreach ($data['order'] as $index => $faqId) {
                FAQ::where('id', $faqId)->update(['display_order' => $index]);
            }

            return ResponseHelper::success($response, 'FAQs reordered successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reorder FAQs', 500, $e->getMessage());
        }
    }
}
