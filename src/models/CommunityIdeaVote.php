<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CommunityIdeaVote Model
 * 
 * Tracks votes on community ideas.
 *
 * @property int $id
 * @property int $idea_id
 * @property int|null $user_id
 * @property string|null $voter_ip
 * @property string|null $voter_email
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class CommunityIdeaVote extends Model
{
    protected $table = 'community_idea_votes';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'idea_id',
        'user_id',
        'voter_ip',
        'voter_email',
        'created_at',
    ];

    protected $hidden = [
        'voter_ip',
        'voter_email',
    ];

    protected $casts = [
        'idea_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function idea()
    {
        return $this->belongsTo(CommunityIdea::class, 'idea_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* -----------------------------------------------------------------
     |  Static Methods
     | -----------------------------------------------------------------
     */

    /**
     * Check if a user has already voted on an idea
     */
    public static function hasUserVoted(int $ideaId, int $userId): bool
    {
        return static::where('idea_id', $ideaId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if an IP has already voted on an idea
     */
    public static function hasIpVoted(int $ideaId, string $ip): bool
    {
        return static::where('idea_id', $ideaId)
            ->where('voter_ip', $ip)
            ->exists();
    }

    /**
     * Record a vote for an idea
     */
    public static function recordVote(int $ideaId, ?int $userId = null, ?string $ip = null, ?string $email = null): ?CommunityIdeaVote
    {
        // Check if already voted
        if ($userId && static::hasUserVoted($ideaId, $userId)) {
            return null;
        }

        if ($ip && !$userId && static::hasIpVoted($ideaId, $ip)) {
            return null;
        }

        $vote = static::create([
            'idea_id' => $ideaId,
            'user_id' => $userId,
            'voter_ip' => $ip,
            'voter_email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Increment vote count on the idea
        CommunityIdea::where('id', $ideaId)->increment('votes');

        return $vote;
    }

    /**
     * Remove a vote from an idea
     */
    public static function removeVote(int $ideaId, int $userId): bool
    {
        $vote = static::where('idea_id', $ideaId)
            ->where('user_id', $userId)
            ->first();

        if (!$vote) {
            return false;
        }

        $vote->delete();

        // Decrement vote count on the idea
        CommunityIdea::where('id', $ideaId)->where('votes', '>', 0)->decrement('votes');

        return true;
    }
}
