<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewFeedback extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question_review_id',
        'reviewer_user_id',
        'vote',
    ];

    protected $casts = [
        'vote' => FeedbackVote::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
    
    public function review()
    {
        return $this->belongsTo(QuestionReview::class, 'question_review_id');
    }
}
