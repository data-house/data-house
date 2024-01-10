<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMap extends Model
{
    use HasFactory;

    use HasUlids;


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'configuration',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'mapped_team',
        'mapped_uploader',
        'recursive',
        'filters',
        'visibility',
    ];

    protected $casts = [
        'recursive' => 'boolean',
        'filters' => 'json',
        'status' => ImportStatus::class,
        'visibility' => Visibility::class,
    ];

    protected $attributes = [
        'visibility' => Visibility::TEAM,
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

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function documents()
    {
        return $this->hasMany(ImportDocument::class);
    }

    public function mappedTeam()
    {
        return $this->belongsTo(Team::class, 'mapped_team');
    }

    public function mappedUploader()
    {
        return $this->belongsTo(User::class, 'mapped_uploader');
    }

    public function scopeStatus($query, ImportStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function lockKey(): string
    {
        return 'import-map-lock:' . $this->ulid;
    }

    public function label()
    {
        return $this->name ?? $this->filters['paths'][0];
    }

    public function isStarted()
    {
        return $this->status === ImportStatus::RUNNING;
    }
}
