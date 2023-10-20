<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StartImportJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Import $import)
    {
        //
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
                key: $this->import->lockKey(),
                releaseAfter: 45
                )];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // TODO: check if connection to service can be established
        $this->import->maps()
            ->where('status', ImportStatus::CREATED)
            ->each(function($map){
                dispatch(new RetrieveDocumentsToImportJob($map));
            });

    }
}
