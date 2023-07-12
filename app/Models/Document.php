<?php

namespace App\Models;

use App\Copilot\Questionable;
use App\DocumentConversion\Contracts\Convertible;
use App\DocumentConversion\ConversionRequest;
use App\PdfProcessing\DocumentReference;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PaginatedDocumentContent;
use App\PdfProcessing\PdfDriver;
use App\Pipelines\Concerns\HasPipelines;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Laravel\Scout\Searchable;
use MeiliSearch\Exceptions\JsonEncodingException;
use Symfony\Component\HttpFoundation\HeaderUtils;

class Document extends Model implements Convertible
{
    use HasFactory;

    use HasUlids;

    use Searchable;

    use Questionable;

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
     * Get the URL for downloading the file that can be used by internal services
     * and that uses a signed route
     */
    public function internalUrl($validityInMinutes = 5): string
    {
        $url = URL::temporarySignedRoute('documents.download.internal', $validityInMinutes * Carbon::SECONDS_PER_MINUTE, $this, false);

        // TODO: check the option to use a temporary url https://laravel.com/docs/10.x/filesystem#temporary-urls

        return rtrim(config('app.internal_url'), '/') . $url;
    }

    /**
     * Get the URL to obtain the viewer of the document
     */
    public function viewerUrl(int $page = 1): string
    {
        if($this->mime !== MimeType::APPLICATION_PDF->value && (!$this->conversion_file_mime || $this->conversion_file_mime && $this->conversion_file_mime !== MimeType::APPLICATION_PDF->value)){
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
            'content' => $content,
            'draft' => $this->draft,
            'published' => $this->published_at !== null,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
    /**
     * Get the questionable data array for the model.
     *
     * @return array
     */
    public function toQuestionableArray()
    {
        logs()->info("Making document [{$this->id}] questionable");

        /**
         * @var \App\PdfProcessing\PaginatedDocumentContent
         */
        $content = null;

        try{
            $reference = $this->asReference();
            $content = Pdf::driver(PdfDriver::EXTRACTOR_SERVICE->value)->text($reference);

            if(!$content instanceof PaginatedDocumentContent){
                throw new Exception("Expecting paginated content from PDF processing. Copilot requires extracted text to be paginated.");
            }
        }
        catch(Exception $ex)
        {
            logs()->error("Error extracting text from document [{$this->id}]", ['error' => $ex->getMessage()]);
            throw $ex;
        }

        return [
            'id' => $this->id,
            'ulid' => $this->ulid,
            'title' => $this->title,
            'content' => $content->collect()->map(function($pageContent, $pageNumber){
                // TODO: maybe this transformation should be driver specific
                return [
                    "metadata" => [
                        "page_number" => $pageNumber
                    ],
                    "text" => $pageContent
                ];
            })->values()->toArray(),
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
}
