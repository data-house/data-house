<?php

namespace App\Models;

use App\Data\TeamSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

/**
 * @property \App\Data\TeamSettings settings
 */
class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
        'settings' => TeamSettings::class . ':default',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function managedProjects()
    {
        return $this->projects()
            ->wherePivotIn('role', [Role::ADMIN->value, Role::MANAGER->value]);
    }
    
    public function collaboratingProjects()
    {
        return $this->projects()
            ->wherePivotNotIn('role', [Role::ADMIN->value, Role::MANAGER->value]);
    }

    public function scopeQuestionReviewers($query)
    {
        return $query
            ->whereNotNull('settings->review')
            ->where('settings->review->questionReview', 'true');
    }


    public function canReviewQuestions(): bool
    {
        return $this->settings->review?->questionReview ?? false;
    }
}
