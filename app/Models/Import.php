<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
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
        'source' => ImportSource::class,
        'status' => ImportStatus::class,
        'configuration' => 'encrypted:json',
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


    public function maps()
    {
        return $this->hasMany(ImportMap::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function scopeCreatedBy($query, User $creator)
    {
        return $query->where('created_by', $creator->getKey());
    }


    /**
     * Start the import.
     */
    public function start()
    {

    }


}
