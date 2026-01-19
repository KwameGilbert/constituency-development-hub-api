<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SubSector Model
 * 
 * Represents sub-sector classifications under sectors
 * 
 * @property int $id
 * @property int $sector_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property string|null $icon
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SubSector extends Model
{
    protected $table = 'sub_sectors';

    protected $fillable = [
        'sector_id',
        'name',
        'code',
        'description',
        'icon',
        'display_order',
        'status',
    ];

    protected $casts = [
        'sector_id' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the sector this sub-sector belongs to
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Get issues in this sub-sector
     */
    public function issues()
    {
        return $this->hasMany(IssueReport::class);
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope: Only active sub-sectors
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Scope: Filter by sector
     */
    public function scopeBySector($query, int $sectorId)
    {
        return $query->where('sector_id', $sectorId);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get active sub-sectors for a sector
     */
    public static function getActiveBySector(int $sectorId): array
    {
        return self::active()
            ->bySector($sectorId)
            ->ordered()
            ->get()
            ->map(fn($s) => $s->toPublicArray())
            ->toArray();
    }

    /**
     * Find by code
     */
    public static function findByCode(string $code): ?SubSector
    {
        return self::where('code', $code)->first();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if sub-sector is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get issues count
     */
    public function getIssuesCount(): int
    {
        return $this->issues()->count();
    }

    /**
     * Convert to public array for API
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'sector_id' => $this->sector_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'icon' => $this->icon,
            'display_order' => $this->display_order,
        ];
    }

    /**
     * Convert to full array with relationships
     */
    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'sector_id' => $this->sector_id,
            'sector_name' => $this->sector ? $this->sector->name : null,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'icon' => $this->icon,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'issues_count' => $this->getIssuesCount(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
