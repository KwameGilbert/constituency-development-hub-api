<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * ConstituencyEvent Model
 * 
 * Represents a community event.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $event_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string $location
 * @property string|null $venue_address
 * @property string|null $map_url
 * @property string|null $image
 * @property string|null $organizer
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string $status
 * @property bool $is_featured
 * @property int|null $max_attendees
 * @property bool $registration_required
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ConstituencyEvent extends Model
{
    protected $table = 'constituency_events';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    const STATUS_UPCOMING = 'upcoming';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'venue_address',
        'map_url',
        'image',
        'organizer',
        'contact_phone',
        'contact_email',
        'status',
        'is_featured',
        'max_attendees',
        'registration_required',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_featured' => 'boolean',
        'max_attendees' => 'integer',
        'registration_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeUpcoming($query)
    {
        return $query->where('status', self::STATUS_UPCOMING)
                     ->where('event_date', '>=', date('Y-m-d'));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?ConstituencyEvent
    {
        return static::where('slug', $slug)->first();
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_UPCOMING && $this->event_date >= date('Y-m-d');
    }

    /**
     * Format time for display (e.g., "09:00" -> "09:00 AM")
     */
    protected function formatTime(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }
        
        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Exception $e) {
            return $time;
        }
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'start_time' => $this->formatTime($this->start_time),
            'end_time' => $this->formatTime($this->end_time),
            'location' => $this->location,
            'venue_address' => $this->venue_address,
            'map_url' => $this->map_url,
            'image' => $this->image,
            'organizer' => $this->organizer,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'max_attendees' => $this->max_attendees,
            'registration_required' => $this->registration_required,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

