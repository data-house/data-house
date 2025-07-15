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

class RemoveSkosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skos:remove {vocabulary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a specific vocabulary';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ref = $this->argument('vocabulary');

        $vocabulary = SkosConceptScheme::query()->whereUri($ref)->orWhere('id', $ref)->first();

        
        if (!$vocabulary) {
            $this->error("Vocabulary '{$ref}' not found.");
            return Command::FAILURE;
        }

        if (!$this->confirm("Are you sure you want to remove {$vocabulary->pref_label}? This action cannot be undone.")) {
            $this->info("Operation cancelled.");
            return Command::SUCCESS;
        }

        $this->line("Removing {$vocabulary->pref_label}...");

        DB::table('skos_collection_skos_concept')->whereIn('skos_collection_id',  $vocabulary->collections()->select('id'))->delete();
        DB::table('document_skos_concept')->whereIn('skos_concept_id', $vocabulary->concepts()->select('id'))->delete();

        $vocabulary->collections()->delete();

        SkosRelation::query()
            ->whereIn('source_skos_concept_id', $vocabulary->concepts()->select('id'))
            ->orWhereIn('target_skos_concept_id', $vocabulary->concepts()->select('id'))
            ->delete();

        $vocabulary->concepts()->delete();

        $vocabulary->delete();

        $this->info("Vocabulary {$vocabulary->pref_label} removed.");

    }
}
