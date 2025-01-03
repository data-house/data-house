<?php

namespace App\Console\Commands;

use App\Models\ImportDocument;
use App\Models\ImportDocumentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PruneImportDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prune-documents
                            {--dry-run : Simulate the execution of the command and print the imports that will be cleared.}
                            {--hours= : The number of hours to retain import documents data.}
                            {--dangling : Prune scheduled and currently running documents. Can cause side-effects if run when an import map is being processed.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune skipped, cancelled or errored import documents.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = $this->option('hours') ?? null;

        $dangling = $this->option('dangling') ?? false;
        
        $dryRun = $this->option('dry-run') ?? false;

        $defaultPruneDays = (int)config('import.prune_older_than_days', 60);

        $pruningDate = $hours ? now()->subHours($hours) : now()->subDays($defaultPruneDays);

        if($hours){
            $this->comment("Pruning import documents older than {$hours} hours. [{$pruningDate->toDateTimeString()}]");
        }
        else {
            $this->comment("Pruning import documents older than {$defaultPruneDays} days. [{$pruningDate->toDateTimeString()}]");
        }



        $docsQuery = ImportDocument::query()
            ->whereNull('document_id')
            ->where('created_at', '<=', $pruningDate)
            ->when(!$dangling, fn($query) => $query->whereNotIn('status', [ImportDocumentStatus::COMPLETED->value, ImportDocumentStatus::PENDING->value, ImportDocumentStatus::IMPORTING->value]))
            
            ;

        $this->line("Pruning {$docsQuery->count()} documents...");

        if($dryRun){
            $this->comment('dry run results');

            $docsQuery->each(fn($d) => $this->line("[{$d->getKey()}] {$d->status->label()} ({$d->created_at->toDateString()})"));

            return self::SUCCESS;
        }

        if($dangling && 'no' === $this->choice('You\'re about to prune dangling documents. This might have undesidered effects to running imports.', ['yes', 'no'], 'no')){
            $this->comment('Execution aborted.');
            return self::SUCCESS;
        }

        $pruned = 0;

        $docsQuery->each(function($importDocument) use (&$pruned): void{
            $importDocument->prune();

            $pruned++;
        });

        $this->line("Pruned {$pruned} documents.");
        
        return self::SUCCESS;
    }

}
