<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IssueReportStatusHistory Model
 * 
 * Tracks status changes on issue reports.
 *
 * @property int $id
 * @property int $issue_report_id
 * @property int $changed_by
 * @property string|null $old_status
 * @property string $new_status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class IssueReportStatusHistory extends Model
{
    protected $table = 'issue_report_status_history';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'issue_report_id',
        'changed_by',
        'old_status',
        'new_status',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'issue_report_id' => 'integer',
        'changed_by' => 'integer',
        'created_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function issueReport()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    /**
     * Log a status change.
     * @param int $reportId
     * @param int $userId
     * @param string|null $oldStatus
     * @param string $newStatus
     * @param string|null $notes
     * @return IssueReportStatusHistory
     */
    public static function logChange(
        int $reportId,
        int $userId,
        ?string $oldStatus,
        string $newStatus,
        ?string $notes = null
    ): IssueReportStatusHistory {
        return static::create([
            'issue_report_id' => $reportId,
            'changed_by' => $userId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'notes' => $this->notes,
            'changed_by' => $this->changedByUser ? [
                'id' => $this->changedByUser->id,
                'name' => $this->changedByUser->name,
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
