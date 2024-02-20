<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
        'status',
        'document_date',
        'document_size',
        'document_hash',
        'import_hash',
        'document_id',
    ];

    protected $casts = [
        'retrieved_at' => 'datetime',
        'processed_at' => 'datetime',
        'document_date' => 'datetime',
        'status' => ImportDocumentStatus::class,
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => ImportDocumentStatus::PENDING,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function import()
    {
        return $this->hasOneThrough(Import::class, ImportMap::class, 'import_id', 'id', 'import_map_id');
    }
    
    public function importMap()
    {
        return $this->belongsTo(ImportMap::class);
    }
    
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    
    public function lockKey(): string
    {
        return 'import-document:' . $this->getKey();
    }


    public function wipe()
    {
        $storage = Storage::disk($this->disk_name);

        if($storage->exists($this->disk_path)){
            $storage->delete($this->disk_path);
        }

        $this->deleteQuietly();
    }


    public function generateLocalPath()
    {
        $hash = $this->import_map_id .'/'. Str::random(60);

        if ($extension = $this->guessExtension()) {
            $extension = '.'.$extension;
        }

        return $hash.$extension;
    }

    protected function guessExtension()
    {
        return MimeTypes::getDefault()->getExtensions($this->mime)[0] ?? null;
    }

    public function moveToDisk(Disk|string $disk): string
    {        
        $path = basename($this->disk_path);

        Storage::disk(is_string($disk) ? $disk : $disk->value)
            ->writeStream($path, Storage::disk($this->disk_name)->readStream($this->disk_path));

        return $path;
    }

    /**
     * Check if the document is viewable by a user given visibility and team access
     */
    public function isVisibleBy(User $user): bool
    {        
        return (
                $user->currentTeam &&
                $user->currentTeam->getKey() === $this->team_id
            ) || 
            $user->getKey() === $this->uploaded_by
            ;
    }
}
