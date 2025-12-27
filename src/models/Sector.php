<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sector Model
 * 
 * Represents a project category/sector (Education, Healthcare, Infrastructure, etc.)
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Sector extends Model
{
    protected $table = 'sectors';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'created_by',
        'updated_by',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'display_order',
        'status',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function projects()
    {
        return $this->hasMany(Project::class, 'sector_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(WebAdmin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(WebAdmin::class, 'updated_by');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)->orderBy('display_order');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?Sector
    {
        return static::where('slug', $slug)->first();
    }

    public static function getActiveSectors()
    {
        return static::active()->get();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getProjectsCount(): int
    {
        return $this->projects()->count();
    }

    public function getOngoingProjectsCount(): int
    {
        return $this->projects()->where('status', Project::STATUS_ONGOING)->count();
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'display_order' => $this->display_order,
            'projects_count' => $this->getProjectsCount(),
        ];
    }
}
