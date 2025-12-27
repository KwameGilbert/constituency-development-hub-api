<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IssueAssessmentReport Model
 * 
 * Represents an assessment report submitted by Task Force after investigating an issue.
 * Contains findings, evidence, and resource requirements.
 *
 * @property int $id
 * @property int $issue_report_id
 * @property int $submitted_by
 * @property string $assessment_summary
 * @property string|null $findings
 * @property bool $issue_confirmed
 * @property string $severity
 * @property string|null $estimated_cost
 * @property string|null $estimated_duration
 * @property array|null $required_resources
 * @property array|null $images
 * @property array|null $documents
 * @property string|null $location_verified
 * @property string|null $gps_coordinates
 * @property string|null $recommendations
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $review_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class IssueAssessmentReport extends Model
{
    protected $table = 'issue_assessment_reports';
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

    // Severity levels
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    protected $fillable = [
        'issue_report_id',
        'submitted_by',
        'assessment_summary',
        'findings',
        'issue_confirmed',
        'severity',
        'estimated_cost',
        'estimated_duration',
        'required_resources',
        'images',
        'documents',
        'location_verified',
        'gps_coordinates',
        'recommendations',
        'status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'issue_report_id' => 'integer',
        'submitted_by' => 'integer',
        'issue_confirmed' => 'boolean',
        'required_resources' => 'array',
        'images' => 'array',
        'documents' => 'array',
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
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
        ]);
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
            'assessment_summary' => $this->assessment_summary,
            'issue_confirmed' => $this->issue_confirmed,
            'severity' => $this->severity,
            'estimated_cost' => $this->estimated_cost,
            'estimated_duration' => $this->estimated_duration,
            'required_resources' => $this->required_resources,
            'images' => $this->images,
            'recommendations' => $this->recommendations,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
