<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use PrinsFrank\Standards\Country\CountryAlpha3;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class Project extends Model
{
    use HasFactory;

    use HasUlids;
    
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
        'title',
        'topics',
        'type',
        'countries',
        'organizations',
        'properties',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'website',
    ];

    protected $casts = [
        'countries' => AsEnumCollection::class.':'. CountryAlpha3::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'links' => AsArrayObject::class,
        'organizations' => AsCollection::class,
        'properties' => AsArrayObject::class,
        'topics' => AsCollection::class,
        'type' => ProjectType::class,
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

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the geographic region according to the UN M49 classification
     * of this project based on the countries
     */
    public function regions(): Collection
    {
        return GeographicRegion::from($this->countries?->map->value);
    }
    
    /**
     * Get the countries targetted by the project
     */
    public function countries(): Collection
    {
        return $this->countries?->map->toCountryName() ?? collect();
    }



    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        logs()->info("Making project [{$this->id}] searchable");

        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'title' => $this->title,
            'title_alternate' => $this->properties['title_en'] ?? null,
            'description' => $this->description,
            'countries' => $this->countries(),
            'region' => $this->regions(), 
            'topics' => $this->topics,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'organizations' => $this->organizations,
        ];
    }
}
