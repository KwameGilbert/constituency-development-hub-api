<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IssueResolutionReport Model
 * 
 * Represents a resolution report submitted by Task Force after fixing an issue.
 * Contains work done, resources used, and evidence of completion.
 *
 * @property int $id
 * @property int $issue_report_id
 * @property int $submitted_by
 * @property string $resolution_summary
 * @property string|null $work_description
 * @property string|null $start_date
 * @property string|null $completion_date
 * @property string|null $actual_cost
 * @property array|null $resources_used
 * @property array|null $before_images
 * @property array|null $after_images
 * @property array|null $documents
 * @property string|null $challenges_faced
 * @property string|null $additional_notes
 * @property bool $requires_followup
 * @property string|null $followup_notes
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $review_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class IssueResolutionReport extends Model
{
    protected $table = 'issue_resolution_reports';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_NEEDS_REVISION = 'needs_revision';

    protected $fillable = [
        'issue_report_id',
        'submitted_by',
        'resolution_summary',
        'work_description',
        'start_date',
        'completion_date',
        'actual_cost',
        'resources_used',
        'before_images',
        'after_images',
        'documents',
        'challenges_faced',
        'additional_notes',
        'requires_followup',
        'followup_notes',
        'status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'issue_report_id' => 'integer',
        'submitted_by' => 'integer',
        'resources_used' => 'array',
        'before_images' => 'array',
        'after_images' => 'array',
        'documents' => 'array',
        'requires_followup' => 'boolean',
        'start_date' => 'date',
        'completion_date' => 'date',
        'reviewed_at' => 'datetime',
        'reviewed_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function issueReport()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    public function submitter()
    {
        return $this->belongsTo(TaskForce::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findByIssue(int $issueId)
    {
        return static::where('issue_report_id', $issueId)->first();
    }

    public static function getPendingReview()
    {
        return static::where('status', self::STATUS_SUBMITTED)->get();
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeBySubmitter($query, int $taskForceId)
    {
        return $query->where('submitted_by', $taskForceId);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function submit(): bool
    {
        return $this->update(['status' => self::STATUS_SUBMITTED]);
    }

    public function approve(int $reviewerId, ?string $notes = null): bool
    {
        $updated = $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
        ]);

        // Update the parent issue report status
        if ($updated && $this->issueReport) {
            $this->issueReport->update([
                'status' => IssueReport::STATUS_RESOLVED,
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolved_by' => $reviewerId,
            ]);
        }

        return $updated;
    }

    public function reject(int $reviewerId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
        ]);
    }

    public function requestRevision(int $reviewerId, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_NEEDS_REVISION,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
        ]);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'issue_report_id' => $this->issue_report_id,
            'resolution_summary' => $this->resolution_summary,
            'work_description' => $this->work_description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'completion_date' => $this->completion_date?->format('Y-m-d'),
            'actual_cost' => $this->actual_cost,
            'before_images' => $this->before_images,
            'after_images' => $this->after_images,
            'requires_followup' => $this->requires_followup,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
