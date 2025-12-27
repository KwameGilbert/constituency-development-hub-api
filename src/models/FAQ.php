<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FAQ Model
 * 
 * Represents a frequently asked question managed by web admins.
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $question
 * @property string $answer
 * @property string|null $category
 * @property int $display_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FAQ extends Model
{
    protected $table = 'faqs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'created_by',
        'updated_by',
        'question',
        'answer',
        'category',
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

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function getActiveFAQs()
    {
        return static::active()->get();
    }

    public static function getCategories()
    {
        return static::whereNotNull('category')
                     ->distinct()
                     ->pluck('category');
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
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
            'display_order' => $this->display_order,
        ];
    }
}
