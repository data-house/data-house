<?php

namespace App\Models;

use App\Copilot\AskMultipleQuestion;
use App\HasNotes;
use App\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory;

    use HasUlids;

    use AskMultipleQuestion;

    use HasNotes;

    use Searchable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'type',
        'visibility',
        'strategy',
        'user_id',
        'team_id',
        'draft',
        'topic_name',
        'topic_group',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type' => CollectionType::STATIC,
        'visibility' => Visibility::PERSONAL,
        'strategy' => CollectionStrategy::STATIC,
        'draft' => true,
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['ulid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'ulid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

    public function url()
    {
        if($this->visibility == Visibility::SYSTEM && $this->strategy == CollectionStrategy::LIBRARY){
            return route('documents.library');
        }

        return route('collections.show', $this);
    }


    public function scopeWithoutSystem($query)
    {
        return $query->whereNot('visibility', Visibility::SYSTEM);
    }
    
    public function scopeLibrary($query)
    {
        return $query->where('visibility', Visibility::PROTECTED);
    }

    /**
     * Scope the query to return only collections that are viewable by a user
     * given visibility and team access
     */
    public function scopeVisibleBy($query, User $user)
    {
        return $query
            ->where(fn($q) => $q->whereIn('visibility', [Visibility::PUBLIC, Visibility::PROTECTED]))
            ->when($user->currentTeam, function ($query, Team $team): void {
                $query->orWhere(fn($q) => $q->where('visibility', Visibility::TEAM)->where('team_id', $team->getKey()));
            })
            ->orWhere(fn($q) => $q->where('visibility', Visibility::PERSONAL)->where('user_id', $user->getKey()))
            ->orWhere(fn($q) => $q->where('visibility', Visibility::SYSTEM)->where('user_id', $user->getKey()))
            ;
    }

    /**
     * Check if the document is viewable by a user given visibility and team access
     */
    public function isVisibleBy(User $user): bool
    {        
        if(in_array($this->visibility, [Visibility::PUBLIC, Visibility::PROTECTED])){
            return true;
        }

        return (
                $this->visibility === Visibility::TEAM &&
                $user->currentTeam &&
                $user->currentTeam->getKey() === $this->team_id
            ) || (
                $this->visibility === Visibility::PERSONAL &&
                $user->getKey() === $this->user_id
            ) || (
                $this->visibility === Visibility::SYSTEM &&
                $user->getKey() === $this->user_id
            );
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['notes', 'notes.user']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        logs()->info("Making collection [{$this->id}] searchable");

        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'team_name' => $this->team?->name,
            'visibility' => $this->visibility?->value,
            'topic_name' => $this->topic_name,
            'topic_group' => $this->topic_group,
            'notes' => $this->notes->map(function($note){
                return $note->user?->name . ' - ' . $note->created_at->toDateString() . ' - ' . $note->content;
            })->toArray(),
        ];
    }
    protected function casts(): array
    {
        return [
            'type' => CollectionType::class,
            'visibility' => Visibility::class,
            'strategy' => CollectionStrategy::class,
            'draft' => 'boolean',
        ];
    }

}
