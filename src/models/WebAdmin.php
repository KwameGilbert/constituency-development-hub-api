<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WebAdmin Model
 * 
 * Represents a web administrator profile linked to a user.
 * Web admins handle CMS content: blog posts, events, carousel, FAQs, etc.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $employee_id
 * @property string $admin_level
 * @property string|null $department
 * @property array|null $permissions
 * @property string|null $profile_image
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WebAdmin extends Model
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'web_admins';

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

    // Admin Levels
    const LEVEL_SUPER_ADMIN = 'super_admin';
    const LEVEL_ADMIN = 'admin';
    const LEVEL_MODERATOR = 'moderator';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'admin_level',
        'department',
        'permissions',
        'profile_image',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Get the user that owns this admin profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get blog posts created by this admin.
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'created_by');
    }

    /**
     * Get events created by this admin.
     */
    public function events()
    {
        return $this->hasMany(ConstituencyEvent::class, 'created_by');
    }

    /**
     * Get hero slides created by this admin.
     */
    public function heroSlides()
    {
        return $this->hasMany(HeroSlide::class, 'created_by');
    }

    /**
     * Get FAQs created by this admin.
     */
    public function faqs()
    {
        return $this->hasMany(FAQ::class, 'created_by');
    }

    /* -----------------------------------------------------------------
     |  Static Search Methods
     | -----------------------------------------------------------------
     */

    /**
     * Find admin by user ID.
     * @param int $userId
     * @return WebAdmin|null
     */
    public static function findByUserId(int $userId): ?WebAdmin
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Find admin by employee ID.
     * @param string $employeeId
     * @return WebAdmin|null
     */
    public static function findByEmployeeId(string $employeeId): ?WebAdmin
    {
        return static::where('employee_id', $employeeId)->first();
    }

    /**
     * Get all super admins.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSuperAdmins()
    {
        return static::where('admin_level', self::LEVEL_SUPER_ADMIN)->get();
    }

    /**
     * Get admins by department.
     * @param string $department
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByDepartment(string $department)
    {
        return static::where('department', $department)->get();
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    /**
     * Scope to get super admins.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('admin_level', self::LEVEL_SUPER_ADMIN);
    }

    /**
     * Scope to get regular admins.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins($query)
    {
        return $query->where('admin_level', self::LEVEL_ADMIN);
    }

    /**
     * Scope to get moderators.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModerators($query)
    {
        return $query->where('admin_level', self::LEVEL_MODERATOR);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if admin is a super admin.
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->admin_level === self::LEVEL_SUPER_ADMIN;
    }

    /**
     * Check if admin is at least admin level.
     * @return bool
     */
    public function isAtLeastAdmin(): bool
    {
        return in_array($this->admin_level, [self::LEVEL_SUPER_ADMIN, self::LEVEL_ADMIN]);
    }

    /**
     * Check if admin has a specific permission.
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Add a permission to this admin.
     * @param string $permission
     * @return bool
     */
    public function addPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            return $this->update(['permissions' => $permissions]);
        }
        
        return true;
    }

    /**
     * Remove a permission from this admin.
     * @param string $permission
     * @return bool
     */
    public function removePermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        
        return $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Check if admin has a profile image.
     * @return bool
     */
    public function hasProfileImage(): bool
    {
        return !is_null($this->profile_image) && !empty($this->profile_image);
    }

    /**
     * Get admin's public profile data.
     * @return array
     */
    public function getPublicProfile(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'admin_level' => $this->admin_level,
            'department' => $this->department,
            'profile_image' => $this->profile_image,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Get admin's full data including user info.
     * @return array
     */
    public function getFullProfile(): array
    {
        $profile = $this->getPublicProfile();
        $profile['permissions'] = $this->permissions;
        $profile['notes'] = $this->notes;
        
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
        $prefix = 'ADM';
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }
}
