<?php

namespace App\Console\Commands;

use App\Actions\ExtractDocumentSections;
use Illuminate\Console\Command;
use App\Models\Document;

class DocumentSectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:sections {documents?* : The Ulid of the documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve the document\'s table of contents.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $ulids = $this->argument('documents') ?? [];
        
        $documents = Document::query()
            ->when(!empty($ulids), function($query) use ($ulids) {
                return $query->whereIn('ulid', $ulids);
            })
            ->get();
        
        $action = new ExtractDocumentSections();

        $documents
            ->each(function($document) use ($action){

                try {
                    $sections = $action($document);
    
                    $document->sections()->createMany($sections->toArray());
    
                    $this->line("Sections extracted for document [{$document->id} - {$document->ulid}]");
                } catch (\Throwable $th) {
                    $this->error("Error extracting sections for [{$document->id} - {$document->ulid}]: {$th->getMessage()}");
                }

            });
        
        return self::SUCCESS;
    }

}
