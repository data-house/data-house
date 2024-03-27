<?php

namespace App\Models;

use App\Searchable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Star extends Model
{
    use HasFactory;

    use HasUuids;

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
        'user_id',
    ];

    protected $touches = ['starrable'];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the starred model.
     */
    public function starrable(): MorphTo
    {
        return $this->morphTo();
    }


    public function scopeByUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['starrable']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        logs()->info("Making star [{$this->id}] searchable");

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'created_at' => $this->created_at,
            'title' => $this->starrable->title,
            'description' => $this->starrable->description,
        ];
    }
}
