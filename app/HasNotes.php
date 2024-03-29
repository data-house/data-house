<?php

namespace App;

use App\Actions\Collection\AddStar;
use App\Actions\Collection\RemoveStar;
use App\Models\Note;
use App\Models\Star;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{

    /**
     * Get all of the model's notes.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
    
    public function annotatedByAuthor(): MorphMany
    {
        return $this->notes()->where('user_id', $this->user_id);
    }

    public function annotatedByUser(?User $user = null): MorphMany
    {
        $user = $user ?? auth()->user();

        return $this->notes()->byUser($user);
    }

    public function addNote(string $note, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        $this->notes()->create([
            'user_id' => $user->getKey(),
            'content' => $note,
        ]);
    }

}