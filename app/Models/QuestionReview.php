<?php

namespace App\Models;

use App\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionReview extends Model
{
    use HasFactory;

    use HasNotes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question_id',
        'user_id',
        'team_id',
        'status',
        'evaluation_result',
        'remarks',
    ];

    protected $casts = [
        'status' => ReviewStatus::class,
        'evaluation_result' => ReviewEvaluationResult::class,
    ];

    protected $attributes = [
        'status' => ReviewStatus::SUBMITTED,
    ];


    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class)
                        ->withTimestamps()
                        ->as('assignee');
    }


    public function scopePending($query)
    {
        return $query->where('status', ReviewStatus::SUBMITTED);
    }
    
    public function scopeUnderReview($query)
    {
        return $query->whereIn('status', [ReviewStatus::SUBMITTED, ReviewStatus::IN_PROGRESS]);
    }
    
    public function scopeApproved($query)
    {
        return $query->where('evaluation_result', ReviewEvaluationResult::APPROVED);
    }
    
    public function scopeApprovedWithChanges($query)
    {
        return $query->where('evaluation_result', ReviewEvaluationResult::CHANGES_APPLIED);
    }
    
    public function scopeRejected($query)
    {
        return $query->where('evaluation_result', ReviewEvaluationResult::REJECTED);
    }


    public function statusLabel(): string
    {
        if($this->status === ReviewStatus::COMPLETED){
            return match ($this->evaluation_result) {
                ReviewEvaluationResult::APPROVED =>  __('Approved'),
                ReviewEvaluationResult::CHANGES_APPLIED => __('Reviewed'),
                ReviewEvaluationResult::REJECTED => __('Rejected'),
            };
        }

        return match ($this->status) {
            ReviewStatus::SUBMITTED => __('Review in progress...'),
            ReviewStatus::IN_PROGRESS => __('Review in progress...'),
        };
    }

    public function statusIcon(): string
    {
        if($this->status === ReviewStatus::COMPLETED){
            return match ($this->evaluation_result) {
                ReviewEvaluationResult::APPROVED =>  'heroicon-o-check-circle',
                ReviewEvaluationResult::CHANGES_APPLIED => 'heroicon-o-check-circle',
                ReviewEvaluationResult::REJECTED => 'heroicon-o-x-circle',
            };
        }

        return 'heroicon-o-ellipsis-horizontal-circle';
    }
}
