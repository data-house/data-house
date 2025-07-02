<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalog extends Model
{

    /**
     * Think of renaming to BASES, like Obsidian does
     */

    /** @use HasFactory<\Database\Factories\CatalogFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => Visibility::PERSONAL,
    ];


    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
        ];
    }

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


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(CatalogField::class);
    }
    
    public function entries(): HasMany
    {
        return $this->hasMany(CatalogEntry::class);
    }
    
    public function catalogValues(): HasMany
    {
        return $this->hasMany(CatalogValue::class);
    }
    
    public function flows(): HasMany
    {
        return $this->hasMany(CatalogFlow::class);
    }

    /**
     * Scope the query to return only catalogs viewable by a user
     * given visibility and team access
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query
            ->where(fn($q) => $q->whereIn('visibility', [Visibility::PUBLIC, Visibility::PROTECTED]))
            ->when($user->currentTeam, function ($query, Team $team): void {
                $query->orWhere(fn($q) => $q->where('visibility', Visibility::TEAM)->where('team_id', $team->getKey()));
            })
            ->orWhere(fn($q) => $q->where('visibility', Visibility::PERSONAL)->where('user_id', $user->getKey()));
    }

    /**
     * Check if the catalog is viewable by a user
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
}
