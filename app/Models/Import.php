<?php

namespace App\Models;

use App\Jobs\StartImportJob;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Import extends Model
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
        'configuration',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'source',
        'name',
        'configuration',
        'created_by',
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


    public function maps()
    {
        return $this->hasMany(ImportMap::class);
    }
    
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function scopeCreatedBy($query, User $creator)
    {
        return $query->where('created_by', $creator->getKey());
    }


    /**
     * Start the import.
     */
    public function start()
    {
        $started = Cache::lock($this->lockKey())->block(30, function() {
            return DB::transaction(function () {

                if(! $this->maps()->status(ImportStatus::CREATED)->exists()){
                    return false;
                }

                $this->status = ImportStatus::RUNNING;

                $this->save();

                return true;
            });
        });

        if(!$started){
            return;
        }

        StartImportJob::dispatch($this);
    }


    public function lockKey(): string
    {
        return 'import-lock:' . $this->ulid;
    }


    /**
     * Clear the data imported so far
     */
    public function wipeData()
    {

    }


    public function cancel()
    {
        if ($this->status == ImportStatus::CANCELLED) {
            return ;
        }
           
        Cache::lock($this->lockKey())->block(30, function(): void {
            DB::transaction(function (): void {
                $this->status = ImportStatus::CANCELLED;
                $this->save();
            
                $this->maps()->update(['status' => ImportStatus::CANCELLED]);
            
                $this->wipeData();
            });
        });
    }

    public function isCancelledOrFailed()
    {
        return $this->status == ImportStatus::CANCELLED || $this->status == ImportStatus::FAILED;
    }


    /**
     * Get the connection to the file service as a Filesystem
     */
    public function connection(): Filesystem
    {
        $disk = Storage::build([
            'driver' => $this->source->value,
            ...$this->configuration,
        ]);
        return $disk;
    }


    public function label(): string
    {
        return $this->name ?? __('Import from :source', ['source' => Str::domain($this->configuration['url'])]);
    }
    protected function casts(): array
    {
        return [
            'source' => ImportSource::class,
            'status' => ImportStatus::class,
            'configuration' => 'encrypted:json',
        ];
    }

}
