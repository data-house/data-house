<?php

namespace App\Jobs;

use App\Models\ImportMap;
use App\Models\ImportStatus;
use App\Notifications\ImportFailedNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

abstract class ImportJobBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ImportMap $importMap;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(ImportMap $map)
    {
        $this->importMap = $map;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping(
                key: $this->importMap->ulid,
                releaseAfter: 45,
                expiresAfter: 2 * Carbon::MINUTES_PER_HOUR * Carbon::SECONDS_PER_MINUTE
                )];
    }

    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If the Import has failed, or been cancelled, stop.
        if ($this->hasBeenCancelled()) {
            logs()->warning("Import map cancelled.", ['mapping' => $this->importMap->ulid]);
            return;
        }
        
        // Run the import.
        $report = $this->runImport();
        
        // If there is no next page token, this is the last page.
        if ( ! isset($report->nextPageToken)) {
            $this->lastPage();
        } else {
            // Dispatch next page
            // TODO: dispatch the next page
            // self::dispatch($this->importMap, $report->nextPageToken)->delay(now()->addSeconds(5));
        }
    }


    protected abstract function runImport(): mixed;


    protected abstract function lastPage();

    /**
     * Check if the import map or the import has been cancelled or failed
     */
    protected function hasBeenCancelled(): bool
    {
        return $this->importMap->fresh()->isCancelledOrFailed() 
            && $this->importMap->import->fresh()->isCancelledOrFailed();
    }

    public function failed()
    {
        if ($this->importMap->status == ImportStatus::FAILED) {
            return ;
        }
        
        Cache::lock($this->importMap->import->lockKey())->block(30, function(): void {
            DB::transaction(function (): void {

                $this->importMap->markAsFailed(class_basename($this));

                $import = $this->importMap->import;

                $import->creator->notify(new ImportFailedNotification($this->importMap));

                if(! $import->maps()->where('status', ImportStatus::RUNNING->value)->exists()){
                    $import->status = ImportStatus::FAILED;
                    $import->save();
                }

                // $import->wipeData(); // Not sure in case of a failed import if we need to clean-it up
            });
        });
    }

    protected function markAsComplete()
    {
        Cache::lock($this->importMap->import->lockKey())->block(30, function(): void {
            DB::transaction(function (): void {
                $this->importMap->markAsCompleted();

                if(! $this->importMap->import->maps()->where('status', ImportStatus::RUNNING->value)->exists()){
                    $import = $this->importMap->import;

                    $import->status = ImportStatus::COMPLETED;
                    $import->save();
                }
            });
        });
    }
}
