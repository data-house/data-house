<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\SuggestDocumentAbstract;
use App\Models\Document;
use App\Models\Role;
use App\Pipelines\PipelineTrigger;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;

class DocumentSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:summary {documents?* : The Ulid of the documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an abstract for specified documents';

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
        
        $action = new SuggestDocumentAbstract();

        $documents
        ->filter(function($document){
            return empty($document->description);
        })
        ->each(function($document) use ($action){
            $abstract = $action($document, 'en');
            
            $document->description = $abstract;
            
            $document->save();

            $this->line("Abstract generated for document [{$document->id} - {$document->ulid}]");
        });
        
        return self::SUCCESS;
    }

}