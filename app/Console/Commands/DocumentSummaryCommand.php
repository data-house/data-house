<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\SuggestDocumentAbstract;
use App\Copilot\Copilot;
use App\Models\Document;
use App\Models\Role;
use App\Pipelines\PipelineTrigger;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use PrinsFrank\Standards\Language\LanguageAlpha2;

class DocumentSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:summary {documents?* : The Ulid of the documents} {--language= : The desidered summary language}';

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
        // if(Copilot::disabled() || ! Copilot::hasSummaryFeatures()){
        //     $this->error(__('Summary module disabled.'));
        //     return self::INVALID;
        // }

        $ulids = $this->argument('documents') ?? [];

        $language = $this->option('language') ? LanguageAlpha2::from($this->option('language')) : null;
        
        $documents = Document::query()
            ->when(!empty($ulids), function($query) use ($ulids) {
                return $query->whereIn('ulid', $ulids);
            })
            ->get();
        
        $action = new SuggestDocumentAbstract();

        $documents
            ->each(function($document) use ($action, $language){

                $language = $language ?? $document->language ?? LanguageAlpha2::English;

                $abstract = 'Summary'; // $action($document, $language);
                
                $document->summaries()->create([
                    'text' => $abstract,
                    'ai_generated' => true,
                    'language' => $language,
                ]);

                $this->line("Abstract generated for document [{$document->id} - {$document->ulid}]");
            });
        
        return self::SUCCESS;
    }

}
