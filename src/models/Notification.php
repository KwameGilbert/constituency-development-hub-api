<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Notification Model
 * 
 * Represents user notifications for system events
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'action_text',
        'data',
        'is_read',
        'read_at',
        'expires_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Notification type constants
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_ISSUE = 'issue';
    const TYPE_PROJECT = 'project';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_SYSTEM = 'system';

    const VALID_TYPES = [
        self::TYPE_INFO,
        self::TYPE_SUCCESS,
        self::TYPE_WARNING,
        self::TYPE_ERROR,
        self::TYPE_ISSUE,
        self::TYPE_PROJECT,
        self::TYPE_ANNOUNCEMENT,
        self::TYPE_ASSIGNMENT,
        self::TYPE_SYSTEM
    ];

    /**
     * Get the user this notification belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to filter read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to filter unexpired notifications
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = Carbon::now();
        return $this->save();
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        $this->is_read = false;
        $this->read_at = null;
        return $this->save();
    }

    /**
     * Create a notification for a user
     */
    public static function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $data = null,
        ?\DateTime $expiresAt = null
    ): self {
        $notification = new self();
        $notification->user_id = $userId;
        $notification->type = $type;
        $notification->title = $title;
        $notification->message = $message;
        $notification->action_url = $actionUrl;
        $notification->action_text = $actionText;
        $notification->data = $data;
        $notification->is_read = false;
        $notification->expires_at = $expiresAt;
        $notification->save();

        return $notification;
    }

    /**
     * Create notifications for multiple users
     */
    public static function createForUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $data = null
    ): int {
        $count = 0;
        foreach ($userIds as $userId) {
            self::createForUser($userId, $type, $title, $message, $actionUrl, $actionText, $data);
            $count++;
        }
        return $count;
    }

    /**
     * Get notifications for a user with pagination
     */
    public static function getForUserPaginated(int $userId, array $params = []): array
    {
        $query = self::forUser($userId)->notExpired();

        // Filter by read status
        if (isset($params['is_read'])) {
            $query->where('is_read', $params['is_read']);
        }

        // Filter by type
        if (!empty($params['type'])) {
            $query->where('type', $params['type']);
        }

        // Sorting
        $query->orderBy('created_at', 'desc');

        // Pagination
        $page = (int) ($params['page'] ?? 1);
        $limit = min((int) ($params['limit'] ?? 20), 100);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $notifications = $query->offset($offset)->limit($limit)->get();

        return [
            'notifications' => $notifications,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit)
            ]
        ];
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::forUser($userId)->unread()->notExpired()->count();
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now()
            ]);
    }

    /**
     * Delete old read notifications
     */
    public static function deleteOldRead(int $daysOld = 30): int
    {
        return self::read()
            ->where('read_at', '<', Carbon::now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Format for API response
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'data' => $this->data,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'time_ago' => $this->created_at ? $this->created_at->diffForHumans() : null
        ];
    }
}
