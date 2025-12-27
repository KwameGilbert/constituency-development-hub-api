<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TaskForce Model
 * 
 * Represents a Resolution Task Force member.
 * Task Force members investigate reported issues, submit assessment reports,
 * and carry out resolution work.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $employee_id
 * @property string|null $title
 * @property string|null $specialization
 * @property array|null $assigned_sectors
 * @property array|null $skills
 * @property bool $can_assess_issues
 * @property bool $can_resolve_issues
 * @property bool $can_request_resources
 * @property string|null $profile_image
 * @property string|null $id_type
 * @property string|null $id_number
 * @property bool $id_verified
 * @property \Illuminate\Support\Carbon|null $id_verified_at
 * @property string|null $address
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property int $assessments_completed
 * @property int $resolutions_completed
 * @property \Illuminate\Support\Carbon|null $last_active_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class TaskForce extends Model
{
    protected $table = 'task_force_members';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Specializations
    const SPEC_INFRASTRUCTURE = 'infrastructure';
    const SPEC_HEALTH = 'health';
    const SPEC_EDUCATION = 'education';
    const SPEC_WATER_SANITATION = 'water_sanitation';
    const SPEC_ELECTRICITY = 'electricity';
    const SPEC_ROADS = 'roads';
    const SPEC_GENERAL = 'general';

    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'specialization',
        'assigned_sectors',
        'skills',
        'can_assess_issues',
        'can_resolve_issues',
        'can_request_resources',
        'profile_image',
        'id_type',
        'id_number',
        'id_verified',
        'id_verified_at',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'assessments_completed',
        'resolutions_completed',
        'last_active_at',
    ];

    protected $hidden = [
        'id_number',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assigned_sectors' => 'array',
        'skills' => 'array',
        'can_assess_issues' => 'boolean',
        'can_resolve_issues' => 'boolean',
        'can_request_resources' => 'boolean',
        'id_verified' => 'boolean',
        'id_verified_at' => 'datetime',
        'assessments_completed' => 'integer',
        'resolutions_completed' => 'integer',
        'last_active_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedIssues()
    {
        return $this->hasMany(IssueReport::class, 'assigned_task_force_id');
    }

    public function assessmentReports()
    {
        return $this->hasMany(IssueAssessmentReport::class, 'submitted_by');
    }

    public function resolutionReports()
    {
        return $this->hasMany(IssueResolutionReport::class, 'submitted_by');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findByUserId(int $userId): ?TaskForce
    {
        return static::where('user_id', $userId)->first();
    }

    public static function findByEmployeeId(string $employeeId): ?TaskForce
    {
        return static::where('employee_id', $employeeId)->first();
    }

    public static function getBySpecialization(string $specialization)
    {
        return static::where('specialization', $specialization)->get();
    }

    public static function getVerified()
    {
        return static::where('id_verified', true)->get();
    }

    public static function generateEmployeeId(): string
    {
        $prefix = 'TF';
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeVerified($query)
    {
        return $query->where('id_verified', true);
    }

    public function scopeBySpecialization($query, string $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function scopeAvailable($query)
    {
        // Task force members with less than 5 active assignments
        return $query->withCount(['assignedIssues' => function ($q) {
            $q->whereNotIn('status', [
                IssueReport::STATUS_RESOLVED,
                IssueReport::STATUS_CLOSED
            ]);
        }])->having('assigned_issues_count', '<', 5);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function canAssessIssues(): bool
    {
        return $this->can_assess_issues;
    }

    public function canResolveIssues(): bool
    {
        return $this->can_resolve_issues;
    }

    public function canRequestResources(): bool
    {
        return $this->can_request_resources;
    }

    public function isVerified(): bool
    {
        return $this->id_verified;
    }

    public function markAsVerified(): bool
    {
        return $this->update([
            'id_verified' => true,
            'id_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function incrementAssessments(): int
    {
        return $this->increment('assessments_completed');
    }

    public function incrementResolutions(): int
    {
        return $this->increment('resolutions_completed');
    }

    public function updateLastActive(): bool
    {
        return $this->update(['last_active_at' => date('Y-m-d H:i:s')]);
    }

    public function getPublicProfile(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'title' => $this->title,
            'specialization' => $this->specialization,
            'profile_image' => $this->profile_image,
            'id_verified' => $this->id_verified,
            'assessments_completed' => $this->assessments_completed,
            'resolutions_completed' => $this->resolutions_completed,
        ];
    }

    public function getFullProfile(): array
    {
        $profile = $this->getPublicProfile();
        $profile['assigned_sectors'] = $this->assigned_sectors;
        $profile['skills'] = $this->skills;
        $profile['permissions'] = [
            'can_assess_issues' => $this->can_assess_issues,
            'can_resolve_issues' => $this->can_resolve_issues,
            'can_request_resources' => $this->can_request_resources,
        ];
        $profile['last_active_at'] = $this->last_active_at?->toDateTimeString();
        $profile['created_at'] = $this->created_at?->toDateTimeString();

        if ($this->user) {
            $profile['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'status' => $this->user->status,
            ];
        }

        return $profile;
    }
}
