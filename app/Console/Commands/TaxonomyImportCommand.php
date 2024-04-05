<?php

namespace App\Console\Commands;

use App\Actions\Project\InsertProject;
use App\Models\Concept;
use App\Models\ConceptCollection;
use App\Models\ConceptRelationType;
use App\Models\Document;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use PrinsFrank\Standards\Country\CountryAlpha3;
use \Illuminate\Support\Str;
use Throwable;

class TaxonomyImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxonomy:import-topics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import topics from the configured topics file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $topics = Topic::all();

        $user = User::first();

        // TODO: Create instance wide concept schema, if not defined

        $topics->each(function($taxonomy, $topicKey) use ($user){

            DB::transaction(function() use ($taxonomy, $user){

                $collection = ConceptCollection::create([
                    'title' => $taxonomy['name'],
                    'description' => $taxonomy['description'] ?? null,
                    'user_id' => $user->getKey(),
                ]);

                $topLevelConcept = Concept::create([
                    'title' => $taxonomy['name'],
                    'alternateLabel' => $taxonomy['id'] ?? null,
                    'description' => $taxonomy['description'] ?? null,
                    'user_id' => $user->getKey(),
                ]);

                $concepts = $this->createConcepts($taxonomy['children'] ?? [], $user, $topLevelConcept);

                $collection->concepts()->attach($concepts);
            });
        });
    }

    protected function createConcepts(array $entries, User $user, ?Concept $topLevelConcept = null): Collection
    {
        $concepts = collect($entries)->map(function($entry) use ($user){
            return Concept::create([
                'title' => $entry['name'],
                'alternateLabel' => $entry['id'] ?? null,
                'description' => $entry['description'] ?? null,
                'user_id' => $user->getKey(),
            ]);
        })->map->getKey();

        // The top level concept, which is also the name of the collection, is broader than all children
        if($topLevelConcept){
            $topLevelConcept->broader()->attach($concepts->mapWithKeys(fn($id) => [$id => ['type' => ConceptRelationType::BROADER]]));
        }

        return $concepts;
    }
}
