<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IssueReportComment Model
 * 
 * Represents a comment on an issue report by officers or agents.
 *
 * @property int $id
 * @property int $issue_report_id
 * @property int $user_id
 * @property string $comment
 * @property bool $is_internal
 * @property array|null $attachments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class IssueReportComment extends Model
{
    protected $table = 'issue_report_comments';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'issue_report_id',
        'user_id',
        'comment',
        'is_internal',
        'attachments',
    ];

    protected $casts = [
        'issue_report_id' => 'integer',
        'user_id' => 'integer',
        'is_internal' => 'boolean',
        'attachments' => 'array',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'is_internal' => $this->is_internal,
            'attachments' => $this->attachments,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role,
            ] : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
