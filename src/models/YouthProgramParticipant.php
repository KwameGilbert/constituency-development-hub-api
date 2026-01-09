<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * YouthProgramParticipant Model
 * 
 * Represents enrollment/participation in youth programs
 */
class YouthProgramParticipant extends Model
{
    protected $table = 'youth_program_participants';

    protected $fillable = [
        'program_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
        'notes',
        'registered_at',
        'completed_at'
    ];

    protected $casts = [
        'program_id' => 'integer',
        'user_id' => 'integer',
        'date_of_birth' => 'date',
        'registered_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_COMPLETED = 'completed';

    const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN,
        self::STATUS_COMPLETED
    ];

    /**
     * Get the program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(YouthProgram::class, 'program_id');
    }

    /**
     * Get the user (if registered user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Format for API response
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'status' => $this->status,
            'registered_at' => $this->registered_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String()
        ];
    }
}
