<?php

namespace App\Console\Commands;

use App\Actions\DeleteDocument;
use App\Models\Document;
use App\Models\ImportDocument;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Pipelines\PipelineTrigger;
use Illuminate\Support\Facades\DB;

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
    public function handle(DeleteDocument $deleteDocument)
    {
        $documentKey = $this->argument('document');
        
        $document = Str::isUlid($documentKey) ? Document::whereUlid($documentKey)->firstOrFail() : Document::findOrFail($documentKey);

        $deleteDocument($document);

        
        $this->line('');
        $this->line("Document deleted.");       
        $this->line('');

        return self::SUCCESS;
    }

}
