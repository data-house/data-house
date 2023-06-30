<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class Question extends Model
{
    use HasFactory;

    use HasUuids;

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
        'question',
        'hash',
        'user_id',
        'questionable',
        'language',
        'answer',
        'execution_time',
    ];

    protected $casts = [
        // 'language' => LanguageAlpha2::class,
        'published_at' => 'datetime',
        'answer' => AsArrayObject::class,
        'execution_time' => 'float',
    ];

    
    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the questioned model.
     */
    public function questionable(): MorphTo
    {
        return $this->morphTo();
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
}
