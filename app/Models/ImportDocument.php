<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nette\InvalidStateException;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

class ImportDocument extends Model
{
    use HasFactory;

    use Prunable;

    
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
        'processed_at',
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


    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::whereNull('document_id')
            ->where('created_at', '<=', now()->subDays((int)config('import.prune_older_than_days', 60)))
            ->whereNotIn('status', [
                ImportDocumentStatus::COMPLETED->value,
                ImportDocumentStatus::PENDING->value,
                ImportDocumentStatus::IMPORTING->value
            ]);
    }

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        if(is_null($this->disk_path) || is_null($this->disk_name)){
            return;
        }

        $storage = Storage::disk($this->disk_name);

        if($storage->exists($this->disk_path)){
            $storage->delete($this->disk_path);
        }
    }

    public function wipe()
    {
        try {
            /**
             * @var \Illuminate\Contracts\Filesystem\Filesystem
             */
            $remoteDisk = $this->importMap?->import?->connection();

            if(is_null($remoteDisk)){
                logs()->error('Could not get source disk of imported document. Import map or import is null');
            }
    
            if(!is_null($remoteDisk) && $remoteDisk->exists($this->source_path)){
                $remoteDisk->delete($this->source_path);
            }

        } catch (Throwable $th) {
            logs()->error('File on source disk not removed', ['error' => $th->getMessage(), 'import_map' => $this->import_map_id, 'source_path' => $this->source_path]);
        }

        $this->prune();
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

        $disk = Storage::disk(is_string($disk) ? $disk : $disk->value);

        $disk->writeStream($path, Storage::disk($this->disk_name)->readStream($this->disk_path));

        if(!$disk->exists($path)){
            throw new InvalidStateException("File not found in expected position after move");
        }

        $this->pruning();

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
    protected function casts(): array
    {
        return [
            'retrieved_at' => 'datetime',
            'processed_at' => 'datetime',
            'document_date' => 'datetime',
            'status' => ImportDocumentStatus::class,
        ];
    }
}
