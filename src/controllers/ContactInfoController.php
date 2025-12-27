<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ContactInfo;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * ContactInfoController
 * 
 * Handles CRUD operations for contact information.
 * Web Admins only.
 */
class ContactInfoController
{
    /**
     * Get all active contact info (Public)
     * GET /api/contact
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $grouped = ContactInfo::getGroupedContacts();

            return ResponseHelper::success($response, 'Contact info fetched successfully', $grouped);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch contact info', 500, $e->getMessage());
        }
    }

    /**
     * Get all contact info (Admin)
     * GET /api/admin/contact
     */
    public function adminIndex(Request $request, Response $response): Response
    {
        try {
            $contacts = ContactInfo::orderBy('type')->orderBy('display_order')->get();

            return ResponseHelper::success($response, 'Contact info fetched successfully', [
                'contacts' => $contacts->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch contact info', 500, $e->getMessage());
        }
    }

    /**
     * Get single contact info
     * GET /api/admin/contact/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $contact = ContactInfo::find($args['id']);

            if (!$contact) {
                return ResponseHelper::error($response, 'Contact info not found', 404);
            }

            return ResponseHelper::success($response, 'Contact info fetched successfully', [
                'contact' => $contact->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch contact info', 500, $e->getMessage());
        }
    }

    /**
     * Create new contact info
     * POST /api/admin/contact
     */
    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            // Validation
            if (empty($data['type'])) {
                return ResponseHelper::error($response, 'Type is required', 400);
            }
            if (empty($data['value'])) {
                return ResponseHelper::error($response, 'Value is required', 400);
            }

            // Validate type
            $validTypes = [
                ContactInfo::TYPE_ADDRESS,
                ContactInfo::TYPE_PHONE,
                ContactInfo::TYPE_EMAIL,
                ContactInfo::TYPE_SOCIAL,
            ];

            if (!in_array($data['type'], $validTypes)) {
                return ResponseHelper::error($response, 'Invalid type', 400);
            }

            $contact = ContactInfo::create([
                'created_by' => $user->id ?? null,
                'type' => $data['type'],
                'label' => $data['label'] ?? null,
                'value' => $data['value'],
                'icon' => $data['icon'] ?? null,
                'link' => $data['link'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'status' => $data['status'] ?? ContactInfo::STATUS_ACTIVE,
            ]);

            return ResponseHelper::success($response, 'Contact info created successfully', [
                'contact' => $contact->toArray()
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create contact info', 500, $e->getMessage());
        }
    }

    /**
     * Update contact info
     * PUT /api/admin/contact/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $contact = ContactInfo::find($args['id']);

            if (!$contact) {
                return ResponseHelper::error($response, 'Contact info not found', 404);
            }

            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');

            $contact->update([
                'updated_by' => $user->id ?? null,
                'type' => $data['type'] ?? $contact->type,
                'label' => $data['label'] ?? $contact->label,
                'value' => $data['value'] ?? $contact->value,
                'icon' => $data['icon'] ?? $contact->icon,
                'link' => $data['link'] ?? $contact->link,
                'display_order' => $data['display_order'] ?? $contact->display_order,
                'status' => $data['status'] ?? $contact->status,
            ]);

            return ResponseHelper::success($response, 'Contact info updated successfully', [
                'contact' => $contact->fresh()->toArray()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update contact info', 500, $e->getMessage());
        }
    }

    /**
     * Delete contact info
     * DELETE /api/admin/contact/{id}
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $contact = ContactInfo::find($args['id']);

            if (!$contact) {
                return ResponseHelper::error($response, 'Contact info not found', 404);
            }

            $contact->delete();

            return ResponseHelper::success($response, 'Contact info deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete contact info', 500, $e->getMessage());
        }
    }
}
