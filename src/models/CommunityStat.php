<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CommunityStat Model
 * 
 * Represents a statistic displayed on the homepage (e.g., "42+ Communities Reached").
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $label
 * @property string $value
 * @property string|null $icon
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CommunityStat extends Model
{
    protected $table = 'community_stats';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'created_by',
        'updated_by',
        'label',
        'value',
        'icon',
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

    public static function getActiveStats()
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

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'icon' => $this->icon,
            'display_order' => $this->display_order,
        ];
    }
}
