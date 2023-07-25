<?php

namespace App\Models;

use App\Copilot\AskMultipleQuestion;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory;

    use HasUlids;

    use AskMultipleQuestion;

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
    ];

    protected $casts = [
        'type' => CollectionType::class,
        'visibility' => Visibility::class,
        'strategy' => CollectionStrategy::class,
        'draft' => 'boolean',
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

}
