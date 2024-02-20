<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Pipelines\PipelineTrigger;

class DocumentDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:delete {document}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a document from the library';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $documentKey = $this->argument('document');
        
        $document = Str::isUlid($documentKey) ? Document::whereUlid($documentKey)->firstOrFail() : Document::findOrFail($documentKey);

        $document->importDocument?->wipe();

        $document->pipelineRuns->each->delete();
        
        $document->summaries->each->delete();
        
        $document->questions->each->delete();

        rescue(fn() => $document->unsearchable());

        rescue(fn() => $document->unquestionable());

        $document->wipe();
        
        $this->line('');
        $this->line("Document deleted.");       
        $this->line('');

        return self::SUCCESS;
    }

}
