<?php

namespace App;

use App\Actions\Collection\AddStar;
use App\Actions\Collection\RemoveStar;
use App\Models\Star;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Starrable
{

    /**
     * Get all of the model's stars.
     */
    public function stars(): MorphMany
    {
        return $this->morphMany(Star::class, 'starrable');
    }
    
    public function staredByUser(?User $user = null): MorphMany
    {
        $user = $user ?? auth()->user();

        return $this->morphMany(Star::class, 'starrable')->byUser($user);
    }

    public function star(?User $user = null, ?string $note = null)
    {
        $addStar = app()->make(AddStar::class);

        $addStar($user ?? auth()->user(), $this, $note);
    }

    public function unstar(?User $user = null)
    {
        $removeStar = app()->make(RemoveStar::class);

        $removeStar($user ?? auth()->user(), $this);
    }

}