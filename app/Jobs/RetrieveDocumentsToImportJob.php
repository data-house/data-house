<?php

namespace App\Jobs;

use App\Models\Disk;
use App\Models\ImportMap;
use App\Models\ImportReport;
use App\Models\MimeType as ModelsMimeType;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Str;

class RetrieveDocumentsToImportJob extends ImportJobBase
{

    public ?string $nextPage = null;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportMap $map, ?string $nextPage = null)
    {
        parent::__construct($map);

        $this->nextPage = $nextPage;
    }

    /**
     * Execute the job.
     */
    public function runImport(): mixed
    {
        /**
         * @var \Illuminate\Filesystem\FilesystemAdapter
         */
        $disk = $this->importMap->import->connection();
        
        $storagePathPrefix = ltrim(parse_url($this->importMap->import->configuration['url'], PHP_URL_PATH), '/');

        $paths = collect($this->importMap->filters['paths']);
        
        $expectedNormalizedPaths = $paths->count();

        $normalizedPaths = $paths
            ->mapWithKeys(function($path) use ($storagePathPrefix, $disk) {

                $normalizedPath = Str::after($path, $storagePathPrefix);

                $is_dir = $disk->directoryExists($normalizedPath);
                $is_file = $disk->fileExists($normalizedPath);

                if(! ($is_dir || $is_file)){
                    return [$normalizedPath => null];
                }

                return [$normalizedPath => $is_dir ? 'folder' : 'file'];
            })
            ->filter();

        if($normalizedPaths->isEmpty() || $normalizedPaths->count() < $expectedNormalizedPaths){

            $this->fail("Some folders or file doesn't exist.");

            return null;
        }

        // TODO: handle the fake pagination using the given $nextPage file name

        // TODO: a check is required since we don't have paginated entries from the filesystem and so, in case the job is retried we might insert again the same data

        $rows = $this->getDocumentsToImport($disk, $normalizedPaths, $this->importMap->recursive, $storagePathPrefix);

        if($rows->isEmpty()){
            $this->fail("The selected criteria for this import resulted in no files");

            return null;
        }

        $processed = 0;

        try {
            // Wait 180 seconds to try and acquire the lock
            Cache::lock($this->importMap->import->lockKey())->block(180, function () use ($rows, &$processed) {
                // If the Import has been cancelled, we don't want to insert anything
                if ($this->hasBeenCancelled()) {
                    return ;
                }

                DB::transaction(function() use ($rows, &$processed) {
                    $chunks = $rows->chunk(100);

                    // TODO: a check is required since we don't have paginated entries from the filesystem and so, in case the job is retried we might insert again the same data
            
                    $chunks->each(function($chunk) use(&$processed) {
                        $this->importMap->documents()->createMany($chunk->toArray());
                        $processed+=$chunk->count();
                    });
                });
            });
        } catch (LockTimeoutException) {
            // Retry the job with a 20-second delay
            $this->release(20);
        }

        $nextPageToken = null;

        if($processed < $rows->count()){
            $entry = $rows->get($processed+1);

            $nextPageToken = $entry['source_path'] ?? null;
        }

        return new ImportReport($processed, $nextPageToken);
    }

    protected function lastPage()
    {
        dispatch(new ImportFileDataJob($this->importMap));
    }


    protected function getDocumentsToImport(Filesystem $disk, Collection $startingPaths, bool $recursive = false, ?string $prefix = null): Collection
    {
        $entries = $startingPaths->map(function($type, $path) use ($disk, $recursive) {
                return $type == 'file' ? $path : $disk->files($path, $recursive);
            })
            ->flatten()
            ->map(function($path) use ($prefix) {
                return $prefix ? Str::after($path, $prefix) : $path;
            })
            ->unique()
            ->filter()
            ->filter(function($path){
                // Filtering only fully supported documents so far
                return MimeType::fromFilename($path) !== ModelsMimeType::APPLICATION_PDF;
            });

        return $entries->map(function($file){
            return [
                'source_path' => $file,
                'disk_name' => Disk::IMPORTS->value,
                'disk_path' => null,
                'mime' => MimeType::fromFilename($file),
                'uploaded_by' => $this->importMap->mapped_uploader,
                'team_id' => $this->importMap->mapped_team,
            ];
        });
    }
}
