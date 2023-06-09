<?php

namespace App\Models;

use App\PdfProcessing\Facades\Pdf;
use App\Pipelines\Concerns\HasPipelines;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Laravel\Scout\Searchable;
use MeiliSearch\Exceptions\JsonEncodingException;
use Symfony\Component\HttpFoundation\HeaderUtils;

class Document extends Model
{
    use HasFactory;

    use HasUlids;

    use Searchable;

    use HasPipelines;
    
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

    
    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with('team');
    }

    /**
     * Get the URL for downloading the file
     */
    public function url(): string
    {
        return route('documents.download', $this);
    }

    /**
     * Get the URL to obtain the viewer of the document
     */
    public function viewerUrl(int $page = 1): string
    {
        if($this->mime !== 'application/pdf'){
            return route('documents.download', ['document' => $this, 'disposition' => HeaderUtils::DISPOSITION_INLINE]);
        }

        return route('pdf.viewer', [
            'document' => $this->ulid,
            'file' => Str::replace(config('app.url'),'',$this->url()),
            'page' => $page
        ]) . "#page={$page}";
    }

    public function isPublished()
    {
        return !is_null($this->published_at);
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

        if($this->attributes['disk_path'] && Str::endsWith($this->attributes['disk_path'], ['.pdf'])){
            $path = Storage::disk($this->attributes['disk_name'])
                ->path($this->attributes['disk_path']);

            try{
                $content = Pdf::text($path);
            }
            catch(Exception $ex)
            {
                logs()->error("Error extracting text from document [{$this->id}]", ['error' => $ex->getMessage()]);
            }
        }

        return collect([])
            ->merge([
                'id' => $this->id,
                'ulid' => $this->ulid,
                'title' => $this->title,
                'description' => $this->description,
                'languages' => $this->languages,
                'mime' => $this->mime,
                'content' => $content,
                'draft' => $this->draft,
                'published' => $this->published_at !== null,
                'published_at' => $this->published_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
            ->all();
    }
}
