<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * YouthProgram Model
 * 
 * Represents youth development programs with enrollment tracking
 */
class YouthProgram extends Model
{
    protected $table = 'youth_programs';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'start_date',
        'end_date',
        'registration_deadline',
        'status',
        'max_participants',
        'current_enrollment',
        'location_id',
        'venue',
        'image_url',
        'requirements',
        'benefits',
        'contact_email',
        'contact_phone',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'max_participants' => 'integer',
        'current_enrollment' => 'integer',
        'location_id' => 'integer',
        'created_by' => 'integer',
        'requirements' => 'array',
        'benefits' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Category constants
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_EMPLOYMENT = 'employment';
    const CATEGORY_ENTREPRENEURSHIP = 'entrepreneurship';
    const CATEGORY_SKILLS = 'skills_training';
    const CATEGORY_SPORTS = 'sports';
    const CATEGORY_ARTS = 'arts_culture';
    const CATEGORY_TECHNOLOGY = 'technology';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_OTHER = 'other';

    const VALID_CATEGORIES = [
        self::CATEGORY_EDUCATION,
        self::CATEGORY_EMPLOYMENT,
        self::CATEGORY_ENTREPRENEURSHIP,
        self::CATEGORY_SKILLS,
        self::CATEGORY_SPORTS,
        self::CATEGORY_ARTS,
        self::CATEGORY_TECHNOLOGY,
        self::CATEGORY_HEALTH,
        self::CATEGORY_OTHER
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_ACTIVE = 'active';
    const STATUS_REGISTRATION_CLOSED = 'registration_closed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const VALID_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_UPCOMING,
        self::STATUS_ACTIVE,
        self::STATUS_REGISTRATION_CLOSED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = self::generateSlug($program->title);
            }
            if (!isset($program->current_enrollment)) {
                $program->current_enrollment = 0;
            }
        });
    }

    /**
     * Generate unique slug from title
     */
    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the location
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(YouthProgramParticipant::class, 'program_id');
    }

    /**
     * Scope to filter by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by status
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for public/active programs
     */
    public function scopePublic($query)
    {
        return $query->whereIn('status', [
            self::STATUS_UPCOMING,
            self::STATUS_ACTIVE,
            self::STATUS_REGISTRATION_CLOSED
        ]);
    }

    /**
     * Scope for programs accepting registration
     */
    public function scopeAcceptingRegistration($query)
    {
        return $query->whereIn('status', [self::STATUS_UPCOMING, self::STATUS_ACTIVE])
            ->where(function ($q) {
                $q->whereNull('registration_deadline')
                    ->orWhere('registration_deadline', '>=', Carbon::today());
            })
            ->where(function ($q) {
                $q->whereNull('max_participants')
                    ->orWhereColumn('current_enrollment', '<', 'max_participants');
            });
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(): bool
    {
        if (!in_array($this->status, [self::STATUS_UPCOMING, self::STATUS_ACTIVE])) {
            return false;
        }

        if ($this->registration_deadline && Carbon::parse($this->registration_deadline)->isPast()) {
            return false;
        }

        if ($this->max_participants && $this->current_enrollment >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Get all programs with pagination and filtering
     */
    public static function getAllWithFilters(array $params = []): array
    {
        $query = self::query();

        // Filter by category
        if (!empty($params['category'])) {
            $query->where('category', $params['category']);
        }

        // Filter by status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Filter by location
        if (!empty($params['location_id'])) {
            $query->where('location_id', $params['location_id']);
        }

        // Public only filter
        if (!empty($params['public_only'])) {
            $query->public();
        }

        // Search by title
        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $q->where('title', 'LIKE', '%' . $params['search'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $params['search'] . '%');
            });
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $page = (int) ($params['page'] ?? 1);
        $limit = min((int) ($params['limit'] ?? 20), 100);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $programs = $query->offset($offset)->limit($limit)->get();

        return [
            'programs' => $programs,
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
    public function toApiResponse(bool $detailed = false): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'registration_deadline' => $this->registration_deadline?->toDateString(),
            'max_participants' => $this->max_participants,
            'current_enrollment' => $this->current_enrollment,
            'available_spots' => $this->max_participants ? $this->max_participants - $this->current_enrollment : null,
            'is_registration_open' => $this->isRegistrationOpen(),
            'venue' => $this->venue,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at?->toIso8601String()
        ];

        if ($detailed) {
            $data['location'] = $this->location ? [
                'id' => $this->location->id,
                'name' => $this->location->name
            ] : null;
            $data['requirements'] = $this->requirements;
            $data['benefits'] = $this->benefits;
            $data['contact_email'] = $this->contact_email;
            $data['contact_phone'] = $this->contact_phone;
            $data['updated_at'] = $this->updated_at?->toIso8601String();
        }

        return $data;
    }
}
