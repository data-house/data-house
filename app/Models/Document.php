<?php

namespace App\Models;

use App\Copilot\Questionable;
use App\Data\FileFormatData;
use App\DocumentConversion\Contracts\Convertible;
use App\DocumentConversion\ConversionRequest;
use App\Http\Requests\RetrievalRequest;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Exceptions\PdfParsingException;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use App\Pipelines\Concerns\HasPipelines;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use App\Searchable;
use App\Sorting\Sorting;
use App\Starrable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use MeiliSearch\Exceptions\JsonEncodingException;
use Oneofftech\LaravelLanguageRecognizer\Support\Facades\LanguageRecognizer;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\Uid\Ulid;

/**
 *  @property \PrinsFrank\Standards\Language\LanguageAlpha2|null language
 */
class Document extends Model implements Convertible
{
    use HasFactory;

    use HasUlids;

    use Searchable;

    use Questionable;

    use HasPipelines;

    use Starrable;
    
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
        'type',
        'document_date',
        'document_size',
        'document_hash',
        'visibility',
    ];

    protected $with = [
        'project',
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

    /**
     * Get the documents's language.
     */
    protected function language(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->languages[0] ?? null,
        );
    }
    
    /**
     * Get the documents's size.
     */
    protected function size(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->document_size ? Number::fileSize($this->document_size) : '-',
        );
    }
    
    /**
     * Get the documents's format.
     */
    protected function format(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => new FileFormatData($this->mime),
        );
    }

    /**
     * Scope the query to return only documents that are viewable by a user
     * given visibility and team access
     */
    public function scopeVisibleBy($query, User $user)
    {
        return $query
            ->where(fn($q) => $q->whereIn('visibility', [Visibility::PUBLIC, Visibility::PROTECTED]))
            ->when($user->currentTeam, function ($query, Team $team): void {
                $query->orWhere(fn($q) => $q->where('visibility', Visibility::TEAM)->where('team_id', $team->getKey()));
            })
            ->orWhere(fn($q) => $q->where('visibility', Visibility::PERSONAL)->where('uploaded_by', $user->getKey()));
    }
    
    public function scopeInProject($query, Project $project)
    {
        return $query
            ->where('project_id', $project->getKey());
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function importDocument()
    {
        return $this->hasOne(ImportDocument::class);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(DocumentSummary::class)->orderBy('created_at', 'DESC')->where('document_summaries.all_document', true); // ->wholeDocument()

    }
    
    public function concepts(): BelongsToMany
    {
        return $this->belongsToMany(SkosConcept::class)
            ->withTimestamps()
            ;
    }

    public function latestSummary(): HasOne
    {
        return $this->hasOne(DocumentSummary::class)->ofMany(['id' => 'MAX'], function($query){
            return $query->where('document_summaries.all_document', true);
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(DocumentSection::class)->sortedByOrder();
    }
    
    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['team', 'project', 'concepts', 'collections' => function($query): void{
            $query->library();
        }]);
    }

    /**
     * Get the URL for downloading the file
     */
    public function url($original = true): string
    {
        return route('documents.download', [
            'document' => $this,
            'filename' => $this->filenameForDownload(),
            'original' => $original
        ]);
    }

    public function filenameForDownload($original = true): string
    {
        $extension = $this->format->extension ?? '';

        $downloadExtension = $original ? ".{$extension}" : '.pdf';

        return str($this->title)
            ->ascii()
            ->replaceEnd(".{$extension}", '')
            ->slug()
            ->append($downloadExtension)
            ->toString();
    }
    
    public function pageUrl(): string
    {
        return route('documents.show', $this);
    }

    /**
     * Get the URL for the file thumbnail
     */
    public function thumbnailUrl(): string
    {
        return route('documents.thumbnail', $this);
    }
    
    /**
     * Get the URL for the file thumbnail
     */
    public function hasThumbnail(): bool
    {
        return !is_null($this->thumbnail_disk_path);
    }
    
    /**
     * Get the URL for downloading the file that can be used by internal services
     * and that uses a signed route
     */
    public function internalUrl($validityInMinutes = 5): string
    {
        $url = URL::temporarySignedRoute('documents.download.internal', $validityInMinutes * Carbon::SECONDS_PER_MINUTE, $this, false);

        // TODO: check the option to use a temporary url https://laravel.com/docs/10.x/filesystem#temporary-urls

        return rtrim(config('app.internal_url'), '/') . $url;
    }

    public function hasPreview(): bool
    {
        return $this->mime === MimeType::APPLICATION_PDF->value ||
               ($this->conversion_file_mime && $this->conversion_file_mime === MimeType::APPLICATION_PDF->value);
    }

    /**
     * Get the URL to obtain the viewer of the document
     */
    public function viewerUrl(int $page = 1): string
    {
        if(! $this->hasPreview()){
            return route('documents.download', ['document' => $this, 'disposition' => HeaderUtils::DISPOSITION_INLINE]);
        }

        return route('pdf.viewer', [
            'document' => $this->ulid,
            'file' => Str::replace(config('app.url'),'',$this->url(false)),
            'page' => $page
        ]);
    }

    public function wipe()
    {
        $storage = Storage::disk($this->disk_name);

        if($storage->exists($this->disk_path)){
            $storage->delete($this->disk_path);
        }

        if(isset($this->attributes['conversion_disk_path']) && isset($this->attributes['conversion_disk_name']) && $this->attributes['conversion_disk_path']){
            $storage = Storage::disk($this->conversion_disk_name);

            if($storage->exists($this->conversion_disk_path)){
                $storage->delete($this->conversion_disk_path);
            }
        }

        $this->deleteQuietly();
    }

    public function isPublished()
    {
        return !is_null($this->published_at);
    }

    /**
     * Check if the document is viewable by a user given visibility and team access
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
                $user->getKey() === $this->uploaded_by
            );
    }
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        logs()->info("Making document [{$this->id}] searchable");

        $content = null;

        $reference = $this->asReference();

        try{
            $content = Pdf::text($reference);
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$this->id}]", ['error' => $ex->getMessage()]);
        }

        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'title' => $this->title,
            'description' => $this->description,
            'languages' => $this->languages,
            'mime' => $this->mime,
            'format' => $this->format->name,
            'type' => $this->type?->name,
            'content' => $content?->all(),
            'draft' => $this->draft,
            'published' => $this->published_at !== null,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'uploaded_by' => $this->uploaded_by,
            'team_id' => $this->team_id,
            'team_name' => $this->team?->name,
            'project_id' => $this->project?->getKey(),
            'project_title' => $this->project?->title,
            'project_region' => $this->project?->regions(),
            'project_countries' => $this->project?->countries()->map->name,
            'project_topics' => $this->project?->topics,
            'visibility' => $this->visibility?->value,
            'stars' => $this->stars()->get(['user_id'])->pluck('user_id')->values(),
            'library_collections' => $this->collections->modelKeys(),
            'concepts' => $this->concepts->map(function($concept){
                
                return [
                    $concept->pref_label,
                    $concept->alt_labels,
                    $concept->ancestorsOfConcept()->get()->map->pref_label,
                ];
            })->flatten()->all(),
        ];
    }
    

    /**
     * Get the value used to index the model.
     *
     * Override the method from the Questionable trait in order to transform the Ulid into a valid UUID
     * As the Copilot API accepts only a valid UUID
     * 
     * @return mixed
     */
    public function getCopilotKey()
    {
        return $this->getAttribute($this->getCopilotKeyName());
    }


    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getCopilotKeyName()
    {
        return 'ulid';
    }

    /**
     * Get the questionable data array for the model.
     *
     * @return array
     */
    public function toQuestionableArray()
    {
        logs()->info("Making document [{$this->id} - {$this->ulid}] questionable");

        /**
         * @var \App\PdfProcessing\DocumentContent
         */
        $content = null;

        try{
            $reference = $this->asReference();
            $content = Cache::rememberForever("pdf-extraction-parse-default-{$this->getKey()}", function() use ($reference) {
                return Pdf::driver(PdfDriver::PARSE)->text($reference);
            });
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$this->id}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }

        if(is_null($content)){
            throw new Exception("Document without textual content. AI interaction requires a document that contains text.");
        }

        return [
            'id' => $this->getCopilotKey(),
            'lang' => $this->language?->value ?? LanguageAlpha2::English->value,
            'data' => $content,
        ];
    }

    public function toConvertible(): ConversionRequest
    {
        return new ConversionRequest(
            key: $this->getKey(),
            url: $this->internalUrl(),
            mimetype: $this->mime,
            title: $this->title
        );
    }

    public function asReference()
    {
        $path = Storage::disk($this->attributes['disk_name'])
            ->path($this->attributes['disk_path']);

        if(isset($this->attributes['conversion_disk_path']) && $this->attributes['conversion_disk_path'] && Str::endsWith($this->attributes['conversion_disk_path'], ['.pdf'])){
            $path = Storage::disk($this->attributes['conversion_disk_name'])
            ->path($this->attributes['conversion_disk_path']);

            return (new DocumentReference($this->conversion_file_mime))
                ->path($path)
                ->url($this->internalUrl());
        }
        

        return (new DocumentReference($this->mime))
            ->path($path)
            ->url($this->internalUrl());
    }

    /**
     * Get the textual content of the document
     */
    public function getContent(): DocumentContent
    {
        try{
            return Pdf::text($this->asReference());
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$this->id}]", ['error' => $ex->getMessage()]);

            throw $ex;
        }
    }

    public function hasTextualContent(): bool
    {
        $propertyValue = $this->properties['has_textual_content'] ?? null;

        if(is_null($propertyValue)){
            return rescue(function(){
                return $this->getContent()->isNotEmpty();
            }, false);
        }

        return $propertyValue;
    }

    /**
     * @return \Spatie\QueryBuilder\QueryBuilder|\Laravel\Scout\Builder
     */
    public static function retrieve(RetrievalRequest $request, ?Project $project = null, ?User $user = null)
    {
        $sorting = Sorting::for(static::class);

        $filters = $request->filters()->except(['source']);

        $user = $user ?? $request->user();

        if ($request->isSearch() || $filters->isNotEmpty()){

            $defaultSort = $sorting->defaultSort();

            $sorts = $sorting->mapRequested($request->sorts())->whenEmpty(function($collection) use ($defaultSort){
                return $collection->push($defaultSort);
            });
            
            return static::tenantSearch($request->searchQuery(), $filters->toArray(), $user, $project)
                ->when($sorts, function($builder, $requestedSorts){

                    $requestedSorts->each(function($sort) use ($builder): void{
                        $builder->orderBy($sort->field, $sort->direction);
                    });

                    return $builder;
                });
        }

        return QueryBuilder::for(static::class, $request)
            ->defaultSort($sorting->defaultSort()->toFieldString())
            ->allowedSorts($sorting->allowedSortsForBuilder())
            ->allowedFilters([])
            ->allowedFields([])
            ->allowedIncludes([])
            ->visibleBy($user)
            ->when($project, fn($builder) => $builder->inProject($project))
            ;
    }
    protected function casts(): array
    {
        return [
            'draft' => 'boolean',
            'languages' => AsEnumCollection::class.':'. LanguageAlpha2::class,
            'published_at' => 'datetime',
            'properties' => AsArrayObject::class,
            'type' => DocumentType::class,
            'document_date' => 'datetime',
            'visibility' => Visibility::class,
        ];
    }
}
