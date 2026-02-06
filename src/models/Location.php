<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Location Model
 * 
 * Represents a constituency location/area (community, suburb, cottage, smaller community)
 * Supports hierarchical structure with parent-child relationships
 */
class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'population',
        'area_size',
        'latitude',
        'longitude',
        'description',
        'status'
    ];

    protected $casts = [
        'population' => 'integer',
        'area_size' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'parent_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Valid location types
    const TYPE_COMMUNITY = 'community';
    const TYPE_SUBURB = 'suburb';

    const VALID_TYPES = [
        self::TYPE_COMMUNITY,
        self::TYPE_SUBURB
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get the parent location
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Get child locations
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Get all issues in this location
     */
    public function issues(): HasMany
    {
        return $this->hasMany(IssueReport::class, 'location_id');
    }

    /**
     * Get all projects in this location
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'location_id');
    }

    /**
     * Get all agents assigned to this location
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'location_id');
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter active locations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter root locations (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get statistics for this location
     */
    public function getStats(): array
    {
        return [
            'total_issues' => $this->issues()->count(),
            'pending_issues' => $this->issues()->whereIn('status', ['pending', 'assigned'])->count(),
            'resolved_issues' => $this->issues()->where('status', 'resolved')->count(),
            'total_projects' => $this->projects()->count(),
            'ongoing_projects' => $this->projects()->where('status', 'ongoing')->count(),
            'completed_projects' => $this->projects()->where('status', 'completed')->count(),
            'total_agents' => $this->agents()->count(),
            'child_locations' => $this->children()->count()
        ];
    }

    /**
     * Get all locations with pagination, filtering, and search
     */
    public static function getAllWithFilters(array $params = []): array
    {
        $query = self::query();

        // Filter by type
        if (!empty($params['type'])) {
            $query->where('type', $params['type']);
        }

        // Filter by status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Filter by parent
        if (isset($params['parent_id'])) {
            if ($params['parent_id'] === 'null' || $params['parent_id'] === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $params['parent_id']);
            }
        }

        // Search by name
        if (!empty($params['search'])) {
            $query->where('name', 'LIKE', '%' . $params['search'] . '%');
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'name';
        $sortOrder = $params['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $page = (int) ($params['page'] ?? 1);
        $limit = min((int) ($params['limit'] ?? 20), 1000);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $locations = $query->offset($offset)->limit($limit)->get();

        return [
            'locations' => $locations,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit)
            ]
        ];
    }

    /**
     * Format for API response
     */
    public function toApiResponse(bool $includeStats = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent ? $this->parent->name : null,
            'population' => $this->population,
            'area_size' => $this->area_size,
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'description' => $this->description,
            'status' => $this->status,
            'children_count' => $this->children()->count(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String()
        ];

        if ($includeStats) {
            $data['statistics'] = $this->getStats();
        }

        return $data;
    }
}
