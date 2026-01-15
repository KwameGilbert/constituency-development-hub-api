<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * JobApplicant Model
 * 
 * Represents an applicant for a job posting.
 *
 * @property int $id
 * @property int $job_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $resume_url
 * @property string|null $cover_letter
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class JobApplicant extends Model
{
    protected $table = 'job_applicants';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_SHORTLISTED = 'shortlisted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';

    protected $fillable = [
        'job_id',
        'name',
        'email',
        'phone',
        'resume_url',
        'cover_letter',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'job_id' => 'integer',
        'applied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function job()
    {
        return $this->belongsTo(EmploymentJob::class, 'job_id');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeByJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', self::STATUS_SHORTLISTED);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_REVIEWED,
            self::STATUS_SHORTLISTED,
            self::STATUS_REJECTED,
            self::STATUS_ACCEPTED,
        ];
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function markAsReviewed(): bool
    {
        return $this->update(['status' => self::STATUS_REVIEWED]);
    }

    public function shortlist(): bool
    {
        return $this->update(['status' => self::STATUS_SHORTLISTED]);
    }

    public function reject(): bool
    {
        return $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function accept(): bool
    {
        return $this->update(['status' => self::STATUS_ACCEPTED]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'resume_url' => $this->resume_url,
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'applied_at' => $this->applied_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
