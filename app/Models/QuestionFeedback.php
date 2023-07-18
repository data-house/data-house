<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionFeedback extends Model
{
    use HasFactory;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question_id',
        'user_id',
        'vote',
        'reason',
        'points',
        'note',
    ];

    protected $casts = [
        'vote' => FeedbackVote::class,
        'reason' => FeedbackReason::class,
    ];

    
    /**
     * Get the questioned model.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function scopePositive(Builder $query): void
    {
        $query->where('vote', FeedbackVote::LIKE->value);
    }
    
    public function scopeNeutral(Builder $query): void
    {
        $query->where('vote', FeedbackVote::IMPROVABLE->value);
    }
    
    public function scopeNegative(Builder $query): void
    {
        $query->where('vote', FeedbackVote::DISLIKE->value);
    }
    
    public function scopeAuthor(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

}
