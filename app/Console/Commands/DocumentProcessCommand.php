<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Pipelines\PipelineTrigger;

class DocumentProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:process {document}
                            {--event= : The event to trigger the pipeline.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a document using a defined pipeline';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $documentKey = $this->argument('document');
        $document = Str::isUlid($documentKey) ? Document::whereUlid($documentKey)->firstOrFail() : Document::findOrFail($documentKey);
        $trigger =  PipelineTrigger::from($this->option('event') ?? PipelineTrigger::ALWAYS->value);
        
        $document->dispatchPipeline($trigger);

        $this->line('');
        $this->line("Pipeline triggered.");       
        $this->line('');

        return self::SUCCESS;
    }

}
