<?php

namespace App\Models;

use App\Data\CountryData;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Searchable;
use App\Topics\Facades\Topic;
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
        'funding',
        'status',
        'website',
        'links',
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
        'status' => ProjectStatus::class,
        'funding' => AsArrayObject::class,
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
     * Get the geographic region according to the UN M49 classification
     * of this project based on the countries
     */
    public function facetRegions(): Collection
    {
        return GeographicRegion::facets($this->countries?->map->value);
    }
    
    /**
     * Get the countries targetted by the project
     *
     * @return \Illuminate\Support\Collection<\App\Data\CountryData>
     */
    public function countries(): Collection
    {
        if(!$this->countries){
            return collect();
        }

        return $this->countries?->map(fn($code) => CountryData::fromCountryCode($code));
    }

    public function formattedTopics(): Collection
    {
        return Topic::from($this->topics);
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
            'slug' => $this->slug,
            'title' => $this->title,
            'title_alternate' => $this->properties['title_en'] ?? null,
            'description' => $this->description,
            'countries' => $this->countries()->map->name,
            'region' => $this->regions()->flatten(), 
            'topics' => $this->topics,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'organizations' => $this->organizations,
            'type' => $this->type?->name,
            'status' => $this->status?->name,
        ];
    }
}
