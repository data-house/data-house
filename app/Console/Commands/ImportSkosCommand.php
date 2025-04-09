<?php

namespace App\Console\Commands;

use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use App\Skos\SkosImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportSkosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skos:import {--base-uri : Vocabulary base uri} {filename : The name of the turtle file to import from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a SKOS-based vocabulary/thesaurus as represented in turtle format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = Storage::disk('vocabularies');

        $filename = $this->argument('filename');

        if(!$disk->exists($filename)){
            $this->error("File [{$filename}] not found in [vocabulary] disk.");

            return self::FAILURE;
        }

        $baseUri = $this->option('base-uri') ?: null;

        $this->line("Importing SKOS from [{$filename}]...");

        $existingSchemesCount = SkosConceptScheme::count();
        $existingConceptsCount = SkosConcept::count();

        SkosImporter::importFromTurtleFile($disk->path($filename), $baseUri);

        $schemesCount = SkosConceptScheme::count() - $existingSchemesCount;
        $conceptsCount = SkosConcept::count() - $existingConceptsCount;

        $this->info("{$schemesCount} new schemes and {$conceptsCount} new concepts imported.");

    }
}
