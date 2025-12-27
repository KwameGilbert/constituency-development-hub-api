<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User Model
 * 
 * Represents a user in the system.
 * Merges functionality for authentication, relationships, and status checks.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $password
 * @property string|null $remember_token
 * @property string $role
 * @property bool $email_verified
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $status
 * @property bool $first_login
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 */
class User extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'users';

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

    // Roles - Constituency Development Hub
    const ROLE_WEB_ADMIN = 'web_admin';
    const ROLE_OFFICER = 'officer';
    const ROLE_AGENT = 'agent';
    const ROLE_TASK_FORCE = 'task_force';

    // Status
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'remember_token',
        'role',
        'email_verified',
        'email_verified_at',
        'status',
        'first_login',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'email_verified' => 'boolean',
        'first_login' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Mutators & Accessors
     | -----------------------------------------------------------------
     */

    /**
     * Auto-hash password with Argon2id on set.
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        // Check if value is already hashed (starts with $argon2 or $2y$)
        if (preg_match('/^(\$argon2|\$2y\$)/', $value)) {
            $this->attributes['password'] = $value;
        } else {
            // Hash with Argon2id
            $this->attributes['password'] = password_hash($value, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,  // 64 MB
                'time_cost' => 4,        // 4 iterations
                'threads' => 2           // 2 parallel threads
            ]);
        }
    }

    /* -----------------------------------------------------------------
     |  Static Search Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get user by email.
     * @param string $email
     * @return User|null
     */
    public static function findByEmail(string $email): ?User
    {
        return static::where('email', $email)->first();
    }

    /**
     * Check if email exists.
     * @param string $email Email to check
     * @param int|null $excludeId Optional user ID to exclude (useful for updates)
     * @return bool
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = static::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get all active users.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveUsers()
    {
        return static::where('status', self::STATUS_ACTIVE)->get();
    }

    /**
     * Get users by role.
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByRole(string $role)
    {
        return static::where('role', $role)->get();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods - Status Checks
     | -----------------------------------------------------------------
     */

    /**
     * Check if email is verified.
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if user is active.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is pending.
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if user is suspended.
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /* -----------------------------------------------------------------
     |  Helper Methods - Role Checks (Constituency Hub Roles)
     | -----------------------------------------------------------------
     */

    /**
     * Check if user is a web admin.
     * @return bool
     */
    public function isWebAdmin(): bool
    {
        return $this->role === self::ROLE_WEB_ADMIN;
    }

    /**
     * Check if user is an officer.
     * @return bool
     */
    public function isOfficer(): bool
    {
        return $this->role === self::ROLE_OFFICER;
    }

    /**
     * Check if user is an agent.
     * @return bool
     */
    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    /**
     * Check if user is staff (web_admin or officer).
     * @return bool
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_WEB_ADMIN, self::ROLE_OFFICER]);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods - Role Checks
     | -----------------------------------------------------------------
     */

    /**
     * Check if user is task force member.
     * @return bool
     */
    public function isTaskForce(): bool
    {
        return $this->role === self::ROLE_TASK_FORCE;
    }

    /* -----------------------------------------------------------------
     |  Helper Methods - Profile Access
     | -----------------------------------------------------------------
     */

    /**
     * Get the role-specific profile for this user.
     * @return WebAdmin|Officer|Agent|TaskForce|null
     */
    public function getRoleProfile()
    {
        return match($this->role) {
            self::ROLE_WEB_ADMIN => $this->webAdmin,
            self::ROLE_OFFICER => $this->officer,
            self::ROLE_AGENT => $this->agent,
            self::ROLE_TASK_FORCE => $this->taskForce,
            default => null,
        };
    }

    /**
     * Get full user data with role profile.
     * @return array
     */
    public function getFullProfile(): array
    {
        $profile = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'email_verified' => $this->email_verified,
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'first_login' => $this->first_login,
            'last_login_at' => $this->last_login_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];

        // Add role-specific profile
        $roleProfile = $this->getRoleProfile();
        if ($roleProfile) {
            $profile['role_profile'] = $roleProfile->getPublicProfile();
        }

        return $profile;
    }

    /* -----------------------------------------------------------------
     |  Relationships - Constituency Hub Roles
     | -----------------------------------------------------------------
     */

    /**
     * Get the web admin profile.
     */
    public function webAdmin()
    {
        return $this->hasOne(WebAdmin::class, 'user_id');
    }

    /**
     * Get the officer profile.
     */
    public function officer()
    {
        return $this->hasOne(Officer::class, 'user_id');
    }

    /**
     * Get the agent profile.
     */
    public function agent()
    {
        return $this->hasOne(Agent::class, 'user_id');
    }

    /**
     * Get the task force profile.
     */
    public function taskForce()
    {
        return $this->hasOne(TaskForce::class, 'user_id');
    }

    /* -----------------------------------------------------------------
     |  Relationships - Authentication & Logging
     | -----------------------------------------------------------------
     */

    /**
     * Get refresh tokens for this user.
     */
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    /**
     * Get audit logs for this user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get email verification tokens for this user.
     */
    public function emailVerificationTokens()
    {
        return $this->hasMany(EmailVerificationToken::class);
    }

    /* -----------------------------------------------------------------
     |  Relationships - Issue Reports (for agents/officers)
     | -----------------------------------------------------------------
     */

    /**
     * Get issue reports resolved by this user.
     */
    public function resolvedIssueReports()
    {
        return $this->hasMany(IssueReport::class, 'resolved_by');
    }

    /**
     * Get issue report comments by this user.
     */
    public function issueReportComments()
    {
        return $this->hasMany(IssueReportComment::class, 'user_id');
    }

    /**
     * Get issue report status changes by this user.
     */
    public function issueStatusChanges()
    {
        return $this->hasMany(IssueReportStatusHistory::class, 'changed_by');
    }
}