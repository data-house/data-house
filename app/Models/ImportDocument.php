<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class ImportDocument extends Model
{
    use HasFactory;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'source_path',
        'disk_name',
        'disk_path',
        'mime',
        'uploaded_by',
        'team_id',
    ];

    protected $casts = [
        'retrieved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];


    public function import()
    {
        return $this->belongsTo(Import::class);
    }
    
    public function lockKey(): string
    {
        return 'import-document:' . $this->getKey();
    }


    public function wipe()
    {
        // TODO: remove file from disk
        // TODO: remove entry from database
    }


    public function generateLocalPath()
    {
        $hash = $this->getKey() .'/'. Str::random(60);

        if ($extension = $this->guessExtension()) {
            $extension = '.'.$extension;
        }

        return $hash.$extension;
    }

    protected function guessExtension()
    {
        return MimeTypes::getDefault()->getExtensions($this->mime)[0] ?? null;
    }
}
