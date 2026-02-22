<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Agent Model
 * 
 * Represents a field agent profile linked to a user.
 * Agents work in communities, submit issue reports, and collect data.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $agent_code
 * @property int|null $supervisor_id
 * @property array|null $assigned_communities
 * @property string|null $assigned_location
 * @property bool $can_submit_reports
 * @property bool $can_collect_data
 * @property bool $can_register_residents
 * @property string|null $profile_image
 * @property string|null $id_type
 * @property string|null $id_number
 * @property bool $id_verified
 * @property \Illuminate\Support\Carbon|null $id_verified_at
 * @property string|null $address
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property int $reports_submitted
 * @property \Illuminate\Support\Carbon|null $last_active_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Agent extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'agents';

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

    // ID Types
    const ID_TYPE_GHANA_CARD = 'ghana_card';
    const ID_TYPE_VOTER_ID = 'voter_id';
    const ID_TYPE_PASSPORT = 'passport';
    const ID_TYPE_DRIVERS_LICENSE = 'drivers_license';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'user_id',
        'agent_code',
        'supervisor_id',
        'assigned_communities',
        'assigned_location',
        'can_submit_reports',
        'can_collect_data',
        'can_register_residents',
        'profile_image',
        'id_type',
        'id_number',
        'id_verified',
        'id_verified_at',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'reports_submitted',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array
     */
    protected $hidden = [
        'id_number',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'supervisor_id' => 'integer',
        'assigned_communities' => 'array',
        'can_submit_reports' => 'boolean',
        'can_collect_data' => 'boolean',
        'can_register_residents' => 'boolean',
        'id_verified' => 'boolean',
        'id_verified_at' => 'datetime',
        'reports_submitted' => 'integer',
        'last_active_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the user that owns this agent profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the officer supervising this agent.
     */
    public function supervisor()
    {
        return $this->belongsTo(Officer::class, 'supervisor_id');
    }

    /**
     * Get issue reports submitted by this agent.
     */
    public function submittedReports()
    {
        return $this->hasMany(IssueReport::class, 'submitted_by_agent_id');
    }

    /**
     * Get issue reports assigned to this agent for field work.
     */
    public function assignedReports()
    {
        return $this->hasMany(IssueReport::class, 'assigned_agent_id');
    }

    /* -----------------------------------------------------------------
     |  Static Search Methods
     | -----------------------------------------------------------------
     */

    /**
     * Find agent by user ID.
     * @param int $userId
     * @return Agent|null
     */
    public static function findByUserId(int $userId): ?Agent
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Find agent by agent code.
     * @param string $agentCode
     * @return Agent|null
     */
    public static function findByAgentCode(string $agentCode): ?Agent
    {
        return static::where('agent_code', $agentCode)->first();
    }

    /**
     * Get agents by supervisor.
     * @param int $supervisorId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBySupervisor(int $supervisorId)
    {
        return static::where('supervisor_id', $supervisorId)->get();
    }

    /**
     * Get verified agents.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getVerified()
    {
        return static::where('id_verified', true)->get();
    }

    /**
     * Get unverified agents.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUnverified()
    {
        return static::where('id_verified', false)->get();
    }

    /**
     * Get agents by location.
     * @param string $location
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByLocation(string $location)
    {
        return static::where('assigned_location', $location)->get();
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope to get verified agents.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('id_verified', true);
    }

    /**
     * Scope to get unverified agents.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('id_verified', false);
    }

    /**
     * Scope to get active agents (active in last 7 days).
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('last_active_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')));
    }

    /**
     * Scope to get agents by location.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $location
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('assigned_location', $location);
    }

    /**
     * Scope to get agents by community.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $community
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCommunity($query, string $community)
    {
        return $query->whereJsonContains('assigned_communities', $community);
    }

    /**
     * Scope to get agents who can submit reports.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanSubmitReports($query)
    {
        return $query->where('can_submit_reports', true);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if agent can submit reports.
     * @return bool
     */
    public function canSubmitReports(): bool
    {
        return $this->can_submit_reports;
    }

    /**
     * Check if agent can collect data.
     * @return bool
     */
    public function canCollectData(): bool
    {
        return $this->can_collect_data;
    }

    /**
     * Check if agent can register residents.
     * @return bool
     */
    public function canRegisterResidents(): bool
    {
        return $this->can_register_residents;
    }

    /**
     * Check if agent's ID is verified.
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->id_verified;
    }

    /**
     * Mark agent's ID as verified.
     * @return bool
     */
    public function markAsVerified(): bool
    {
        return $this->update([
            'id_verified' => true,
            'id_verified_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check if agent is assigned to a specific community.
     * @param string $community
     * @return bool
     */
    public function isAssignedToCommunity(string $community): bool
    {
        $communities = $this->assigned_communities ?? [];
        return in_array($community, $communities);
    }

    /**
     * Assign agent to a community.
     * @param string $community
     * @return bool
     */
    public function assignToCommunity(string $community): bool
    {
        $communities = $this->assigned_communities ?? [];
        
        if (!in_array($community, $communities)) {
            $communities[] = $community;
            return $this->update(['assigned_communities' => $communities]);
        }
        
        return true;
    }

    /**
     * Remove agent from a community.
     * @param string $community
     * @return bool
     */
    public function removeFromCommunity(string $community): bool
    {
        $communities = $this->assigned_communities ?? [];
        $communities = array_filter($communities, fn($c) => $c !== $community);
        
        return $this->update(['assigned_communities' => array_values($communities)]);
    }

    /**
     * Increment reports submitted count.
     * @return int
     */
    public function incrementReportsSubmitted(): int
    {
        return $this->increment('reports_submitted');
    }

    /**
     * Update last active timestamp.
     * @return bool
     */
    public function updateLastActive(): bool
    {
        return $this->update(['last_active_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Check if agent has a profile image.
     * @return bool
     */
    public function hasProfileImage(): bool
    {
        return !is_null($this->profile_image) && !empty($this->profile_image);
    }

    /**
     * Check if agent has emergency contact.
     * @return bool
     */
    public function hasEmergencyContact(): bool
    {
        return !is_null($this->emergency_contact_name) && !is_null($this->emergency_contact_phone);
    }

    /**
     * Get agent's public profile data.
     * @return array
     */
    public function getPublicProfile(): array
    {
        return [
            'id' => $this->id,
            'agent_code' => $this->agent_code,
            'assigned_location' => $this->assigned_location,
            'assigned_communities' => $this->assigned_communities,
            'profile_image' => $this->profile_image,
            'id_verified' => $this->id_verified,
            'reports_submitted' => $this->reports_submitted,
            'last_active_at' => $this->last_active_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Get agent's full data including user info and permissions.
     * @return array
     */
    public function getFullProfile(): array
    {
        $profile = $this->getPublicProfile();
        $profile['id_type'] = $this->id_type;
        $profile['id_verified_at'] = $this->id_verified_at?->toDateTimeString();
        $profile['address'] = $this->address;
        $profile['emergency_contact'] = [
            'name' => $this->emergency_contact_name,
            'phone' => $this->emergency_contact_phone,
        ];
        $profile['permissions'] = [
            'can_submit_reports' => $this->can_submit_reports,
            'can_collect_data' => $this->can_collect_data,
            'can_register_residents' => $this->can_register_residents,
        ];
        
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

        if ($this->supervisor) {
            $profile['supervisor'] = [
                'id' => $this->supervisor->id,
                'name' => $this->supervisor->user?->name,
                'title' => $this->supervisor->title,
                'office_phone' => $this->supervisor->office_phone,
            ];
        }

        return $profile;
    }

    /**
     * Generate unique agent code.
     * @return string
     */
    public static function generateAgentCode(): string
    {
        $prefix = 'AGT';
        $year = date('Y');
        $codePrefix = sprintf('%s-%s-', $prefix, $year);

        $maxSequence = static::query()
            ->where('agent_code', 'like', $codePrefix . '%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(agent_code, '-', -1) AS UNSIGNED)) as max_sequence")
            ->value('max_sequence');

        $nextSequence = ((int) $maxSequence) + 1;
        $candidate = sprintf('%s%04d', $codePrefix, $nextSequence);

        while (static::where('agent_code', $candidate)->exists()) {
            $nextSequence++;
            $candidate = sprintf('%s%04d', $codePrefix, $nextSequence);
        }

        return $candidate;
    }
}
