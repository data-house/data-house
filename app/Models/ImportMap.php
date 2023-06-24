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
        'mapped_team',
        'mapped_uploader',
        'recursive',
        'filters',
    ];

    protected $casts = [
        'recursive' => 'boolean',
        'filters' => 'json',
        'status' => ImportStatus::class,
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

    public function lockKey(): string
    {
        return $this->ulid;
    }
}
