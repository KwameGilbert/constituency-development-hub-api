<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Project Model
 * 
 * Represents a development project in the constituency.
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $managing_officer_id
 * @property string $title
 * @property string $slug
 * @property int $sector_id
 * @property string $location
 * @property string|null $description
 * @property string $status
 * @property string|null $start_date
 * @property string|null $end_date
 * @property float|null $budget
 * @property float|null $spent
 * @property int $progress_percent
 * @property int|null $beneficiaries
 * @property string|null $image
 * @property array|null $gallery
 * @property string|null $contractor
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property bool $is_featured
 * @property int $views
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_PLANNING = 'planning';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'created_by',
        'updated_by',
        'managing_officer_id',
        'title',
        'slug',
        'sector_id',
        'location',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'progress_percent',
        'beneficiaries',
        'image',
        'gallery',
        'contractor',
        'contact_person',
        'contact_phone',
        'is_featured',
        'views',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'managing_officer_id' => 'integer',
        'sector_id' => 'integer',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'progress_percent' => 'integer',
        'beneficiaries' => 'integer',
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'views' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(WebAdmin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(WebAdmin::class, 'updated_by');
    }

    public function managingOfficer()
    {
        return $this->belongsTo(Officer::class, 'managing_officer_id');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBySector($query, int $sectorId)
    {
        return $query->where('sector_id', $sectorId);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?Project
    {
        return static::where('slug', $slug)->first();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isOngoing(): bool
    {
        return $this->status === self::STATUS_ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getBudgetUtilization(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return round(($this->spent / $this->budget) * 100, 2);
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
            'sector' => $this->sector ? [
                'id' => $this->sector->id,
                'name' => $this->sector->name,
                'slug' => $this->sector->slug,
            ] : null,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'progress_percent' => $this->progress_percent,
            'beneficiaries' => $this->beneficiaries,
            'image' => $this->image,
            'gallery' => $this->gallery,
            'is_featured' => $this->is_featured,
            'views' => $this->views,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
