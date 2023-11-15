<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\MimeType;
use Illuminate\Console\Command;
use App\Actions\RecognizeLanguage;

class DocumentLanguageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:language {documents?* : The Ulid of the documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recognize the language for specified documents';

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
            ->when(empty($ulids), function($query) {
                return $query
                    ->whereJsonLength('languages', 0)
                    ->where('mime', MimeType::APPLICATION_PDF->value);
            })
            ->get()
            ->filter(function($document){
                return optional($document->languages)->isEmpty();
            });
        
        $action = new RecognizeLanguage();

        $documents
            ->each(function($document) use ($action){
                $languages = $action($document);
                
                $document->languages = $languages;
                
                $document->saveQuietly();

                $this->line("Languages recognized for document [{$document->id} - {$document->ulid}]: " . $languages->map->value->join(','));
            });
        
        return self::SUCCESS;
    }

}
