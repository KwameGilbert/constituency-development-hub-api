<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Announcement Model
 * 
 * Represents a public announcement or notice.
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $category
 * @property string $priority
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $publish_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string|null $image
 * @property string|null $attachment
 * @property int $views
 * @property bool $is_pinned
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Categories
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_EVENTS = 'events';
    const CATEGORY_INFRASTRUCTURE = 'infrastructure';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_EMERGENCY = 'emergency';
    const CATEGORY_OTHER = 'other';

    // Priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'created_by',
        'updated_by',
        'title',
        'slug',
        'content',
        'category',
        'priority',
        'status',
        'publish_date',
        'expiry_date',
        'image',
        'attachment',
        'views',
        'is_pinned',
        'published_at',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'views' => 'integer',
        'is_pinned' => 'boolean',
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'published_at' => 'datetime',
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

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeActive($query)
    {
        $now = date('Y-m-d');
        return $query->published()
            ->where(function ($q) use ($now) {
                $q->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $now);
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?Announcement
    {
        return static::where('slug', $slug)->first();
    }

    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isActive(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        $now = date('Y-m-d');

        if ($this->publish_date !== null && $this->publish_date->format('Y-m-d') > $now) {
            return false;
        }

        if ($this->expiry_date !== null && $this->expiry_date->format('Y-m-d') < $now) {
            return false;
        }

        return true;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function publish(): bool
    {
        return $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function archive(): bool
    {
        return $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function incrementViews(): int
    {
        return $this->increment('views');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'publish_date' => $this->publish_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'image' => $this->image,
            'attachment' => $this->attachment,
            'views' => $this->views,
            'is_pinned' => $this->is_pinned,
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
