<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Category Model
 * 
 * Represents a top-level classification for sectors
 * (e.g., Development, Social Services, Infrastructure)
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
class Category extends Model
{
    protected $table = 'categories';
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

    public function sectors()
    {
        return $this->hasMany(Sector::class, 'category_id')->orderBy('display_order');
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

    public static function findBySlug(string $slug): ?Category
    {
        return static::where('slug', $slug)->first();
    }

    public static function getActiveCategories()
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

    public function getSectorsCount(): int
    {
        return $this->sectors()->count();
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
            'sectors_count' => $this->getSectorsCount(),
        ];
    }

    public function toFullArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'sectors_count' => $this->getSectorsCount(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
