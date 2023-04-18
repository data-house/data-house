<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class Document extends Model
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
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'disk_name',
        'disk_path',
        'draft',
        'title',
        'mime',
        'uploaded_by',
        'team_id',
        'languages',
        'description',
        'thumbnail_disk_name',
        'thumbnail_disk_path',
        'published_at',
        'published_by',
        'published_to_url',
        'properties',
    ];

    protected $casts = [
        'draft' => 'boolean',
        'languages' => AsEnumCollection::class.':'. LanguageAlpha2::class,
        'published_at' => 'datetime',
        'properties' => AsArrayObject::class,
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

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
