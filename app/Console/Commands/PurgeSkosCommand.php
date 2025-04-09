<?php

namespace App\Console\Commands;

use App\Models\SkosCollection;
use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use App\Models\SkosMappingRelation;
use App\Models\SkosRelation;
use App\Skos\SkosImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeSkosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skos:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove vocabulary data';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        
        $this->line("Purging imported SKOS vocabularies...");
        
        DB::table('skos_collection_skos_concept')->truncate();
        DB::table('document_skos_concept')->truncate();
        SkosCollection::query()->truncate();
        SkosRelation::query()->truncate();
        SkosMappingRelation::query()->truncate();
        SkosConceptScheme::query()->truncate();
        SkosConcept::query()->truncate();

        $this->info("Vocabularies entries purged.");

    }
}
