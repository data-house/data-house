<?php

namespace App\Console\Commands;

use App\Models\Disk;
use App\Models\Document;
use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use App\Skos\SkosImporter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentAssociateConceptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document:concept {document?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate SKOS concepts from loaded vocabularies to documents';


    protected Filesystem $disk;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $documentId = $this->argument('document') ?? null;

        $this->disk = Storage::disk(Disk::DOCUMENT_CLASSIFICATION_RESULTS->value);

        if(filled($documentId)){
            $document = Str::isUlid($documentId) ? Document::whereUlid($documentId)->firstOrFail() : Document::findOrFail($documentId);

            $this->line("Linking document [{$document->title}] with concepts...");

            $this->associateDocument($document);

            $this->info("Document linked.");

            return;
        }

        $this->line("Linking documents with concepts...");
        
        $this->withProgressBar(Document::query()->get(), function(Document $document){
            $this->associateDocument($document);
        });

        $this->newLine(2);

        $this->info("Documents linked.");
    }

    protected function associateDocument(Document $document): void
    {
        if(!$this->disk->exists("{$document->ulid}/sdg.json")){
            return;
        }

        $json = $this->disk->json("{$document->ulid}/sdg.json");

        // As the classifier express a score for each of the SDG goal
        // we take only the first five as the related ones
        // to link the corresponding concepts
        $classification = collect($json['classification'] ?? $json['result'] ?? $json)->sortByDesc('score')->take(5)->pluck('name');

        $sdgConcepts = SkosConcept::whereIn('notation', $classification)
            ->with(['mappingMatches', 'mappingMatches.mappingMatches'])
            ->get()
            ->map(function($c){

                $linkedToLinked = $c->mappingMatches->map->mappingMatches->flatten(1)->filter(fn($c) => !Str::startsWith($c->uri, 'https://vocabulary.oneofftech.xyz/sdg/'))->pluck('id');
                
                return [
                    $c->id,
                    ...$c->mappingMatches->pluck('id'),
                    ...$linkedToLinked,
                ];
            })
            ->flatten()
            ->unique()
            ;

        // Here we calculate the closure of the concepts considering all broader relations transitive
        // In SKOS this is not the default so we might follow what SKOS does in the future

        // - grab related concepts in other vocabularies from each concept
        // - grab a unique list of those related parent concepts
        // - grab current concept parents
        // - put it a list and sync

        $document->concepts()->sync(ids: $sdgConcepts, detaching: false);
    }
}
