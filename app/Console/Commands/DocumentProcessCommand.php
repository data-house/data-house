<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\Fortify\CreateNewUser;
use App\Models\Document;
use App\Models\Role;
use App\Pipelines\PipelineTrigger;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;

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
        $document = Document::findOrFail($documentKey);
        $trigger =  PipelineTrigger::from($this->option('event') ?? PipelineTrigger::ALWAYS->value);
        
        $document->dispatchPipeline($trigger);

        $this->line('');
        $this->line("Pipeline triggered.");       
        $this->line('');

        return self::SUCCESS;
    }

}
