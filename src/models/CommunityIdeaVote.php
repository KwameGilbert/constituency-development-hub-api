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
        'type',
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
    public static function getUserVote(int $ideaId, int $userId): ?CommunityIdeaVote
    {
        return static::where('idea_id', $ideaId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Check if an IP has already voted on an idea
     */
    public static function getIpVote(int $ideaId, string $ip): ?CommunityIdeaVote
    {
        return static::where('idea_id', $ideaId)
            ->where('voter_ip', $ip)
            ->first();
    }

    /**
     * Record a vote for an idea (Toggle logic)
     */
    public static function recordVote(int $ideaId, ?int $userId = null, ?string $ip = null, string $type = 'up', ?string $email = null): array
    {
        $existingVote = null;

        if ($userId) {
            $existingVote = static::getUserVote($ideaId, $userId);
        } elseif ($ip) {
            $existingVote = static::getIpVote($ideaId, $ip);
        } else {
            return ['action' => 'error', 'message' => 'User or IP required'];
        }

        $idea = CommunityIdea::find($ideaId);
        if (!$idea) {
            return ['action' => 'error', 'message' => 'Idea not found'];
        }

        if ($existingVote) {
            // If voting same type, remove vote (toggle off)
            if ($existingVote->type === $type) {
                $existingVote->delete();
                
                // Decrement count
                if ($type === 'up') {
                    $idea->decrement('votes');
                } else {
                    $idea->decrement('downvotes');
                }

                return ['action' => 'removed', 'vote' => null];
            } 
            // If voting different type, switch vote
            else {
                $oldType = $existingVote->type;
                $existingVote->type = $type;
                $existingVote->save();

                // Update counts
                if ($type === 'up') {
                    $idea->increment('votes');
                    $idea->decrement('downvotes');
                } else {
                    $idea->increment('downvotes');
                    $idea->decrement('votes');
                }

                return ['action' => 'switched', 'vote' => $existingVote];
            }
        }

        // Create new vote
        $vote = static::create([
            'idea_id' => $ideaId,
            'user_id' => $userId,
            'voter_ip' => $ip,
            'voter_email' => $email,
            'type' => $type,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Increment count
        if ($type === 'up') {
            $idea->increment('votes');
        } else {
            $idea->increment('downvotes');
        }

        return ['action' => 'created', 'vote' => $vote];
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

        $type = $vote->type;
        $vote->delete();

        // Decrement vote count on the idea
        if ($type === 'up') {
            CommunityIdea::where('id', $ideaId)->where('votes', '>', 0)->decrement('votes');
        } else {
            CommunityIdea::where('id', $ideaId)->where('downvotes', '>', 0)->decrement('downvotes');
        }

        return true;
    }
}
