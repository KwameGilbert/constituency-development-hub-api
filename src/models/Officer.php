<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Officer Model
 * 
 * Represents a constituency officer profile linked to a user.
 * Officers handle issue reports, manage projects, and supervise agents.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $employee_id
 * @property string|null $title
 * @property string|null $department
 * @property array|null $assigned_sectors
 * @property array|null $assigned_locations
 * @property bool $can_manage_projects
 * @property bool $can_manage_reports
 * @property bool $can_manage_events
 * @property bool $can_publish_content
 * @property string|null $profile_image
 * @property string|null $bio
 * @property string|null $office_location
 * @property string|null $office_phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Officer extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'officers';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'department',
        'assigned_sectors',
        'assigned_locations',
        'can_manage_projects',
        'can_manage_reports',
        'can_manage_events',
        'can_publish_content',
        'profile_image',
        'bio',
        'office_location',
        'office_phone',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'assigned_sectors' => 'array',
        'assigned_locations' => 'array',
        'can_manage_projects' => 'boolean',
        'can_manage_reports' => 'boolean',
        'can_manage_events' => 'boolean',
        'can_publish_content' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the user that owns this officer profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get agents supervised by this officer.
     */
    public function supervisedAgents()
    {
        return $this->hasMany(Agent::class, 'supervisor_id');
    }

    /**
     * Get projects managed by this officer.
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'managing_officer_id');
    }

    /**
     * Get issue reports assigned to this officer.
     */
    public function assignedReports()
    {
        return $this->hasMany(IssueReport::class, 'assigned_officer_id');
    }

    /**
     * Get issue reports acknowledged by this officer.
     */
    public function acknowledgedReports()
    {
        return $this->hasMany(IssueReport::class, 'acknowledged_by');
    }

    /* -----------------------------------------------------------------
     |  Static Search Methods
     | -----------------------------------------------------------------
     */

    /**
     * Find officer by user ID.
     * @param int $userId
     * @return Officer|null
     */
    public static function findByUserId(int $userId): ?Officer
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Find officer by employee ID.
     * @param string $employeeId
     * @return Officer|null
     */
    public static function findByEmployeeId(string $employeeId): ?Officer
    {
        return static::where('employee_id', $employeeId)->first();
    }

    /**
     * Get officers by department.
     * @param string $department
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByDepartment(string $department)
    {
        return static::where('department', $department)->get();
    }

    /**
     * Get officers who can manage projects.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getProjectManagers()
    {
        return static::where('can_manage_projects', true)->get();
    }

    /**
     * Get officers who can handle reports.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getReportHandlers()
    {
        return static::where('can_manage_reports', true)->get();
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope to get officers who can manage projects.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanManageProjects($query)
    {
        return $query->where('can_manage_projects', true);
    }

    /**
     * Scope to get officers who can manage reports.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanManageReports($query)
    {
        return $query->where('can_manage_reports', true);
    }

    /**
     * Scope to get officers by location.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $location
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->whereJsonContains('assigned_locations', $location);
    }

    /**
     * Scope to get officers by sector.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $sectorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySector($query, int $sectorId)
    {
        return $query->whereJsonContains('assigned_sectors', $sectorId);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if officer can manage projects.
     * @return bool
     */
    public function canManageProjects(): bool
    {
        return $this->can_manage_projects;
    }

    /**
     * Check if officer can manage reports.
     * @return bool
     */
    public function canManageReports(): bool
    {
        return $this->can_manage_reports;
    }

    /**
     * Check if officer can manage events.
     * @return bool
     */
    public function canManageEvents(): bool
    {
        return $this->can_manage_events;
    }

    /**
     * Check if officer can publish content.
     * @return bool
     */
    public function canPublishContent(): bool
    {
        return $this->can_publish_content;
    }

    /**
     * Check if officer is assigned to a specific sector.
     * @param int $sectorId
     * @return bool
     */
    public function isAssignedToSector(int $sectorId): bool
    {
        $sectors = $this->assigned_sectors ?? [];
        return in_array($sectorId, $sectors);
    }

    /**
     * Check if officer covers a specific location.
     * @param string $location
     * @return bool
     */
    public function coversLocation(string $location): bool
    {
        $locations = $this->assigned_locations ?? [];
        return in_array($location, $locations);
    }

    /**
     * Get the count of supervised agents.
     * @return int
     */
    public function getSupervisedAgentsCount(): int
    {
        return $this->supervisedAgents()->count();
    }

    /**
     * Get the count of pending assigned reports.
     * @return int
     */
    public function getPendingReportsCount(): int
    {
        return $this->assignedReports()
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->count();
    }

    /**
     * Assign an agent to this officer.
     * @param Agent $agent
     * @return bool
     */
    public function assignAgent(Agent $agent): bool
    {
        return $agent->update(['supervisor_id' => $this->id]);
    }

    /**
     * Check if officer has a profile image.
     * @return bool
     */
    public function hasProfileImage(): bool
    {
        return !is_null($this->profile_image) && !empty($this->profile_image);
    }

    /**
     * Get officer's public profile data.
     * @return array
     */
    public function getPublicProfile(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'title' => $this->title,
            'department' => $this->department,
            'profile_image' => $this->profile_image,
            'bio' => $this->bio,
            'office_location' => $this->office_location,
            'office_phone' => $this->office_phone,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Get officer's full data including user info and permissions.
     * @return array
     */
    public function getFullProfile(): array
    {
        $profile = $this->getPublicProfile();
        $profile['assigned_sectors'] = $this->assigned_sectors;
        $profile['assigned_locations'] = $this->assigned_locations;
        $profile['permissions'] = [
            'can_manage_projects' => $this->can_manage_projects,
            'can_manage_reports' => $this->can_manage_reports,
            'can_manage_events' => $this->can_manage_events,
            'can_publish_content' => $this->can_publish_content,
        ];
        $profile['supervised_agents_count'] = $this->getSupervisedAgentsCount();
        $profile['pending_reports_count'] = $this->getPendingReportsCount();
        
        if ($this->user) {
            $profile['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'status' => $this->user->status,
                'email_verified' => $this->user->email_verified,
            ];
        }

        return $profile;
    }

    /**
     * Generate unique employee ID.
     * @return string
     */
    public static function generateEmployeeId(): string
    {
        $prefix = 'OFF';
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }
}
