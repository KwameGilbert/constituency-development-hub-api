<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NewsletterSubscriber Model
 * 
 * Represents a newsletter/SMS subscription.
 *
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string|null $phone
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $subscribed_at
 * @property \Illuminate\Support\Carbon|null $unsubscribed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_ACTIVE = 'active';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'email',
        'name',
        'phone',
        'status',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', self::STATUS_UNSUBSCRIBED);
    }

    public function scopeWithPhone($query)
    {
        return $query->whereNotNull('phone');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findByEmail(string $email): ?NewsletterSubscriber
    {
        return static::where('email', $email)->first();
    }

    public static function subscribe(string $email, ?string $name = null, ?string $phone = null): NewsletterSubscriber
    {
        $subscriber = static::findByEmail($email);
        
        if ($subscriber) {
            // Resubscribe if unsubscribed
            if ($subscriber->status === self::STATUS_UNSUBSCRIBED) {
                $subscriber->update([
                    'status' => self::STATUS_ACTIVE,
                    'name' => $name ?? $subscriber->name,
                    'phone' => $phone ?? $subscriber->phone,
                    'subscribed_at' => date('Y-m-d H:i:s'),
                    'unsubscribed_at' => null,
                ]);
            }
            return $subscriber;
        }

        return static::create([
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'status' => self::STATUS_ACTIVE,
            'subscribed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getActiveSubscribers()
    {
        return static::active()->get();
    }

    public static function getActiveCount(): int
    {
        return static::active()->count();
    }

    public static function getActiveEmails(): array
    {
        return static::active()->pluck('email')->toArray();
    }

    public static function getActiveWithPhones()
    {
        return static::active()->withPhone()->get();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function unsubscribe(): bool
    {
        return $this->update([
            'status' => self::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function resubscribe(): bool
    {
        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'subscribed_at' => date('Y-m-d H:i:s'),
            'unsubscribed_at' => null,
        ]);
    }

    public function hasPhone(): bool
    {
        return !is_null($this->phone) && !empty($this->phone);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'status' => $this->status,
            'subscribed_at' => $this->subscribed_at?->toDateTimeString(),
        ];
    }
}
