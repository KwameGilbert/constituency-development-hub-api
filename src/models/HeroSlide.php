<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HeroSlide Model
 * 
 * Represents a homepage carousel slide managed by web admins.
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property string $image
 * @property string|null $cta_label
 * @property string|null $cta_link
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class HeroSlide extends Model
{
    protected $table = 'hero_slides';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'created_by',
        'updated_by',
        'title',
        'subtitle',
        'description',
        'image',
        'cta_label',
        'cta_link',
        'display_order',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'display_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
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
        $now = date('Y-m-d H:i:s');
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where(function($q) use ($now) {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', $now);
                     })
                     ->where(function($q) use ($now) {
                         $q->whereNull('ends_at')
                           ->orWhere('ends_at', '>=', $now);
                     })
                     ->orderBy('display_order');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function getActiveSlides()
    {
        return static::active()->get();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($this->starts_at && $this->starts_at->format('Y-m-d H:i:s') > $now) {
            return false;
        }
        
        if ($this->ends_at && $this->ends_at->format('Y-m-d H:i:s') < $now) {
            return false;
        }
        
        return true;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'image' => $this->image,
            'cta_label' => $this->cta_label,
            'cta_link' => $this->cta_link,
            'display_order' => $this->display_order,
        ];
    }
}
