<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CommunityIdea Model
 * 
 * Represents a community-submitted idea or proposal.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $category
 * @property string|null $submitter_name
 * @property string|null $submitter_email
 * @property string|null $submitter_contact
 * @property int|null $submitter_user_id
 * @property string $status
 * @property string $priority
 * @property int $votes
 * @property string|null $estimated_cost
 * @property float|null $estimated_cost_min
 * @property float|null $estimated_cost_max
 * @property string|null $location
 * @property string|null $target_beneficiaries
 * @property string|null $implementation_timeline
 * @property array|null $images
 * @property array|null $documents
 * @property string|null $admin_notes
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $implemented_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CommunityIdea extends Model
{
    protected $table = 'community_ideas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    // Categories
    const CATEGORY_INFRASTRUCTURE = 'infrastructure';
    const CATEGORY_EDUCATION = 'education';
    const CATEGORY_HEALTHCARE = 'healthcare';
    const CATEGORY_ENVIRONMENT = 'environment';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_ECONOMIC = 'economic';
    const CATEGORY_GOVERNANCE = 'governance';
    const CATEGORY_OTHER = 'other';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IMPLEMENTED = 'implemented';

    // Priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'submitter_name',
        'submitter_email',
        'submitter_contact',
        'submitter_user_id',
        'status',
        'priority',
        'votes',
        'downvotes',
        'estimated_cost',
        'estimated_cost_min',
        'estimated_cost_max',
        'location',
        'target_beneficiaries',
        'implementation_timeline',
        'images',
        'documents',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'implemented_at',
    ];

    protected $hidden = [
        'submitter_email',
        'submitter_contact',
    ];

    protected $casts = [
        'submitter_user_id' => 'integer',
        'reviewed_by' => 'integer',
        'votes' => 'integer',
        'downvotes' => 'integer',
        'estimated_cost_min' => 'decimal:2',
        'estimated_cost_max' => 'decimal:2',
        'images' => 'array',
        'documents' => 'array',
        'reviewed_at' => 'datetime',
        'implemented_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(WebAdmin::class, 'reviewed_by');
    }

    public function ideaVotes()
    {
        return $this->hasMany(CommunityIdeaVote::class, 'idea_id');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeImplemented($query)
    {
        return $query->where('status', self::STATUS_IMPLEMENTED);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeTopVoted($query, int $limit = 10)
    {
        return $query->orderBy('votes', 'desc')->limit($limit);
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    public static function findBySlug(string $slug): ?CommunityIdea
    {
        return static::where('slug', $slug)->first();
    }

    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public static function getStatistics(): array
    {
        $total = static::count();
        $pending = static::where('status', self::STATUS_PENDING)->count();
        $underReview = static::where('status', self::STATUS_UNDER_REVIEW)->count();
        $approved = static::where('status', self::STATUS_APPROVED)->count();
        $rejected = static::where('status', self::STATUS_REJECTED)->count();
        $implemented = static::where('status', self::STATUS_IMPLEMENTED)->count();
        $totalVotes = static::sum('votes');

        return [
            'total_ideas' => $total,
            'pending_ideas' => $pending,
            'under_review_ideas' => $underReview,
            'approved_ideas' => $approved,
            'rejected_ideas' => $rejected,
            'implemented_ideas' => $implemented,
            'total_votes' => $totalVotes,
        ];
    }

    /* -----------------------------------------------------------------
     |  Helper Methods
     | -----------------------------------------------------------------
     */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isImplemented(): bool
    {
        return $this->status === self::STATUS_IMPLEMENTED;
    }

    public function markAsUnderReview(int $reviewerId): bool
    {
        return $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function approve(int $reviewerId, ?string $notes = null): bool
    {
        $data = [
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        if ($notes) {
            $data['admin_notes'] = $notes;
        }

        return $this->update($data);
    }

    public function reject(int $reviewerId, ?string $notes = null): bool
    {
        $data = [
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        if ($notes) {
            $data['admin_notes'] = $notes;
        }

        return $this->update($data);
    }

    public function markAsImplemented(): bool
    {
        return $this->update([
            'status' => self::STATUS_IMPLEMENTED,
            'implemented_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function addVote(): int
    {
        return $this->increment('votes');
    }

    public function removeVote(): int
    {
        if ($this->votes > 0) {
            return $this->decrement('votes');
        }
        return $this->votes;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'submitter_name' => $this->submitter_name,
            'status' => $this->status,
            'priority' => $this->priority,
            'votes' => $this->votes,
            'downvotes' => $this->downvotes,
            'estimated_cost' => $this->estimated_cost,
            'location' => $this->location,
            'target_beneficiaries' => $this->target_beneficiaries,
            'implementation_timeline' => $this->implementation_timeline,
            'images' => $this->images,
            'admin_notes' => $this->admin_notes,
            'reviewed_at' => $this->reviewed_at?->toDateTimeString(),
            'implemented_at' => $this->implemented_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    public function toFullArray(): array
    {
        $data = $this->toPublicArray();
        $data['submitter_email'] = $this->submitter_email;
        $data['submitter_contact'] = $this->submitter_contact;
        $data['documents'] = $this->documents;
        $data['estimated_cost_min'] = $this->estimated_cost_min;
        $data['estimated_cost_max'] = $this->estimated_cost_max;

        if ($this->submitter) {
            $data['submitter'] = [
                'id' => $this->submitter->id,
                'name' => $this->submitter->name,
                'email' => $this->submitter->email,
            ];
        }

        if ($this->reviewer) {
            $data['reviewer'] = [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->user?->name,
            ];
        }

        return $data;
    }
}
