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
        'source',
        'configuration',
        'created_by',
    ];

    protected $casts = [
        'recursive' => 'boolean',
        'status' => ImportStatus::class,
        'configuration' => 'encrypted:json',
        'filters' => 'json',


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

    public function mappedTeam()
    {
        return $this->belongsTo(Team::class);
    }

    public function mappedUploader()
    {
        return $this->belongsTo(User::class);
    }

}
