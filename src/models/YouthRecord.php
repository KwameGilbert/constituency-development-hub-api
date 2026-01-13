<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * YouthRecord Model
 * 
 * Represents individual youth registration records with personal,
 * educational, and employment information.
 */
class YouthRecord extends Model
{
    protected $table = 'youth_records';

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'gender',
        'national_id',
        'phone',
        'email',
        'hometown',
        'community',
        'location_id',
        'education_level',
        'jhs_completed',
        'shs_qualification',
        'certificate_qualification',
        'diploma_qualification',
        'degree_qualification',
        'postgraduate_qualification',
        'professional_qualification',
        'employment_status',
        'availability_status',
        'current_employment',
        'preferred_location',
        'salary_expectation',
        'employment_notes',
        'work_experiences',
        'skills',
        'interests',
        'status',
        'admin_notes',
        'created_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'jhs_completed' => 'boolean',
        'salary_expectation' => 'decimal:2',
        'work_experiences' => 'array',
        'location_id' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Record Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED
    ];

    // Employment Status constants
    const EMP_STATUS_EMPLOYED = 'employed';
    const EMP_STATUS_UNEMPLOYED = 'unemployed';
    const EMP_STATUS_STUDENT = 'student';
    const EMP_STATUS_SELF_EMPLOYED = 'self_employed';

    const VALID_EMPLOYMENT_STATUSES = [
        self::EMP_STATUS_EMPLOYED,
        self::EMP_STATUS_UNEMPLOYED,
        self::EMP_STATUS_STUDENT,
        self::EMP_STATUS_SELF_EMPLOYED
    ];

    // Availability Status constants
    const AVAIL_STATUS_AVAILABLE = 'available';
    const AVAIL_STATUS_UNAVAILABLE = 'unavailable';

    const VALID_AVAILABILITY_STATUSES = [
        self::AVAIL_STATUS_AVAILABLE,
        self::AVAIL_STATUS_UNAVAILABLE
    ];

    // Education level options
    const EDUCATION_LEVELS = [
        'non_formal',
        'jhs',
        'shs',
        'certificate',
        'diploma',
        'degree',
        'postgraduate'
    ];

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
     * Scope to filter by status
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by employment status
     */
    public function scopeOfEmploymentStatus($query, string $status)
    {
        return $query->where('employment_status', $status);
    }

    /**
     * Scope to filter by location
     */
    public function scopeOfLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Calculate age from date of birth
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Generate a unique record ID
     */
    public static function generateRecordId(): string
    {
        $year = date('Y');
        $latestRecord = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $latestRecord ? ((int) substr($latestRecord->id, -3)) + 1 : 1;
        return sprintf('YTH-%s-%03d', $year, $nextNumber);
    }

    /**
     * Get all records with pagination and filtering
     */
    public static function getAllWithFilters(array $params = []): array
    {
        $query = self::query();

        // Filter by status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Filter by employment status
        if (!empty($params['employment_status'])) {
            $query->where('employment_status', $params['employment_status']);
        }

        // Filter by education level
        if (!empty($params['education_level'])) {
            $query->where('education_level', $params['education_level']);
        }

        // Filter by location
        if (!empty($params['location_id'])) {
            $query->where('location_id', $params['location_id']);
        }

        // Filter by community
        if (!empty($params['community'])) {
            $query->where('community', 'LIKE', '%' . $params['community'] . '%');
        }

        // Search by name, phone, national_id, or community
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%')
                    ->orWhere('national_id', 'LIKE', '%' . $search . '%')
                    ->orWhere('community', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        
        $allowedSortFields = ['created_at', 'full_name', 'employment_status', 'status', 'date_of_birth'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(max(1, (int) ($params['limit'] ?? 20)), 100);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $records = $query->offset($offset)->limit($limit)->get();

        return [
            'records' => $records,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit)
            ]
        ];
    }

    /**
     * Get statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'pending' => self::where('status', self::STATUS_PENDING)->count(),
            'approved' => self::where('status', self::STATUS_APPROVED)->count(),
            'rejected' => self::where('status', self::STATUS_REJECTED)->count(),
            'employed' => self::where('employment_status', self::EMP_STATUS_EMPLOYED)->count(),
            'unemployed' => self::where('employment_status', self::EMP_STATUS_UNEMPLOYED)->count(),
            'students' => self::where('employment_status', self::EMP_STATUS_STUDENT)->count(),
            'self_employed' => self::where('employment_status', self::EMP_STATUS_SELF_EMPLOYED)->count(),
        ];
    }

    /**
     * Format for API response
     */
    public function toApiResponse(bool $detailed = false): array
    {
        $data = [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'age' => $this->age,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'community' => $this->community,
            'education_level' => $this->education_level,
            'employment_status' => $this->employment_status,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String()
        ];

        if ($detailed) {
            $data['national_id'] = $this->national_id;
            $data['email'] = $this->email;
            $data['hometown'] = $this->hometown;
            $data['location_id'] = $this->location_id;
            $data['location'] = $this->location ? [
                'id' => $this->location->id,
                'name' => $this->location->name
            ] : null;
            $data['jhs_completed'] = $this->jhs_completed;
            $data['shs_qualification'] = $this->shs_qualification;
            $data['certificate_qualification'] = $this->certificate_qualification;
            $data['diploma_qualification'] = $this->diploma_qualification;
            $data['degree_qualification'] = $this->degree_qualification;
            $data['postgraduate_qualification'] = $this->postgraduate_qualification;
            $data['professional_qualification'] = $this->professional_qualification;
            $data['availability_status'] = $this->availability_status;
            $data['current_employment'] = $this->current_employment;
            $data['preferred_location'] = $this->preferred_location;
            $data['salary_expectation'] = $this->salary_expectation;
            $data['employment_notes'] = $this->employment_notes;
            $data['work_experiences'] = $this->work_experiences;
            $data['skills'] = $this->skills;
            $data['interests'] = $this->interests;
            $data['admin_notes'] = $this->admin_notes;
            $data['updated_at'] = $this->updated_at?->toIso8601String();
        }

        return $data;
    }
}
