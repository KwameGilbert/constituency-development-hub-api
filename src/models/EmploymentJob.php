<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * EmploymentJob Model
 * 
 * Represents a job listing or employment opportunity.
 *
 * @property int $id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string|null $company
 * @property string $location
 * @property string $job_type
 * @property string|null $salary_range
 * @property float|null $salary_min
 * @property float|null $salary_max
 * @property string|null $requirements
 * @property string|null $responsibilities
 * @property string|null $benefits
 * @property \Illuminate\Support\Carbon|null $application_deadline
 * @property string|null $application_url
 * @property string|null $application_email
 * @property string|null $contact_phone
 * @property string $status
 * @property string $category
 * @property string $experience_level
 * @property int $applicants_count
 * @property int $views
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class EmploymentJob extends Model
{
    protected $table = 'employment_jobs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Job Types
    const TYPE_FULL_TIME = 'full_time';
    const TYPE_PART_TIME = 'part_time';
    const TYPE_CONTRACT = 'contract';
    const TYPE_INTERNSHIP = 'internship';
    const TYPE_TEMPORARY = 'temporary';
    const TYPE_VOLUNTEER = 'volunteer';

    // Categories
    const CATEGORY_ADMINISTRATION = 'administration';
    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_SOCIAL_SERVICES = 'social_services';
    const CATEGORY_FINANCE = 'finance';
    const CATEGORY_COMMUNICATIONS = 'communications';
    const CATEGORY_MONITORING_EVALUATION = 'monitoring_evaluation';
    const CATEGORY_OTHER = 'other';

    // Experience Levels
    const LEVEL_ENTRY = 'entry';
    const LEVEL_MID = 'mid';
    const LEVEL_SENIOR = 'senior';
    const LEVEL_EXECUTIVE = 'executive';

    // Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'created_by',
        'updated_by',
        'title',
        'slug',
        'description',
        'company',
        'location',
        'job_type',
        'salary_range',
        'salary_min',
        'salary_max',
        'requirements',
        'responsibilities',
        'benefits',
        'application_deadline',
        'application_url',
        'application_email',
        'contact_phone',
        'status',
        'category',
        'experience_level',
        'applicants_count',
        'views',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'applicants_count' => 'integer',
        'views' => 'integer',
        'is_featured' => 'boolean',
        'application_deadline' => 'date',
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

    public function scopeOpen($query)
    {
        $now = date('Y-m-d');
        return $query->published()
            ->where(function ($q) use ($now) {
                $q->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', $now);
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByJobType($query, string $jobType)
    {
        return $query->where('job_type', $jobType);
    }

    public function scopeByExperienceLevel($query, string $level)
    {
        return $query->where('experience_level', $level);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?EmploymentJob
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

    public static function getStatistics(): array
    {
        $total = static::count();
        $published = static::where('status', self::STATUS_PUBLISHED)->count();
        $draft = static::where('status', self::STATUS_DRAFT)->count();
        $closed = static::where('status', self::STATUS_CLOSED)->count();
        $totalApplicants = static::sum('applicants_count');

        return [
            'total_jobs' => $total,
            'published_jobs' => $published,
            'draft_jobs' => $draft,
            'closed_jobs' => $closed,
            'total_applicants' => $totalApplicants,
        ];
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

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isOpen(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        if ($this->application_deadline !== null && $this->application_deadline->format('Y-m-d') < date('Y-m-d')) {
            return false;
        }

        return true;
    }

    public function publish(): bool
    {
        return $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function close(): bool
    {
        return $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function archive(): bool
    {
        return $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function incrementViews(): int
    {
        return $this->increment('views');
    }

    public function incrementApplicants(): int
    {
        return $this->increment('applicants_count');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'company' => $this->company,
            'location' => $this->location,
            'job_type' => $this->job_type,
            'salary_range' => $this->salary_range,
            'requirements' => $this->requirements,
            'responsibilities' => $this->responsibilities,
            'benefits' => $this->benefits,
            'application_deadline' => $this->application_deadline?->format('Y-m-d'),
            'application_url' => $this->application_url,
            'application_email' => $this->application_email,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'category' => $this->category,
            'experience_level' => $this->experience_level,
            'applicants_count' => $this->applicants_count,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
