<?php

namespace App\Skos;

use App\Models\SkosCollection;
use App\Models\SkosConcept;
use App\Models\SkosConceptScheme;
use App\Models\SkosMappingRelation;
use App\Models\SkosRelation;
use App\SkosRelationType;
use EasyRdf\Resource;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use \Illuminate\Support\Str;

class SkosImporter
{
    /**
     * Import a SKOS based thesaurus from a turtle file.
     * 
     * Assumptions:
     * - one skos:ConceptScheme per file
     * - All concepts have inScheme property and all values point to the same ConceptScheme in current file
     * - en language available for all skos:prefLabel (skos:Concept and skos:ConceptScheme pref)
     * - skos:Collections are not nested (although possible in SKOS this place more complexity on the implementation)
     * - skos:Collection are labelled using skos:prefLabel property
     * - skos:Collection are always inScheme of the ConceptScheme defined in the file, even if not explicitly stated
     * - local prefix is always
     * 
     * Notes:
     * - Currently skos:OrderedCollections are not supported. Concepts are included in the order in which they appear in the collection definition in the file
     * - External vocabularies references in semantic relations are not fetched and ignore if that vocabulary is not available locally
     */
    public static function importFromTurtleFile(string $absoluteFilePath, ?string $baseUri = null): void
    {
        $parser = new SkosParser();
        $graph = $parser->parse($absoluteFilePath, $baseUri);

        // Assume one skos:ConceptScheme per file

        /**
         * @var Resource
         */
        $conceptScheme = collect($graph->allOfType("skos:ConceptScheme"))->first();

        $insertedScheme = SkosConceptScheme::query()->updateOrCreate(
            ['uri' => $conceptScheme->getUri()],
            [
                'vocabulary_base_uri' => $baseUri ?? $parser->getBaseUri(),
                'pref_label' => $conceptScheme->getLiteral(property: 'skos:prefLabel', lang: 'en'),
                // 'alt_labels',
                // 'hidden_labels',
                'description' => $conceptScheme->getLiteral('skos:definition') ?? $conceptScheme->getLiteral('dc:description'),
            ]
        );

        // Import concepts

        $conceptsInFile = collect($graph->allOfType("skos:Concept"));

        /**
         * @var Collection
         */
        $schemeTopConceptUris = collect($conceptScheme->all("skos:hasTopConcept"))->map->getUri();


        // First pass: Create basic concepts

        $conceptsToImport = $conceptsInFile->map(function(Resource $resource) use ($insertedScheme, $schemeTopConceptUris) {

            return [
                'uri' => $resource->getUri(),
                'skos_concept_scheme_id' => $insertedScheme->getKey(),
                'pref_label' => $resource->getLiteral(property: 'skos:prefLabel', lang: 'en'),
                'alt_labels' => collect($resource->all('skos:altLabel'))->map->getValue()->merge([$resource->getLiteral(property: 'skos:prefLabel', lang: 'de')?->getValue()])->flatten()->filter(),
                'hidden_labels' => collect($resource->all('skos:hiddenLabel'))->map->getValue(),
                'notation' => $resource->getLiteral('skos:notation') ?? $resource->getLiteral('dc:identifier'),
                'definition' => $resource->get('skos:definition'),
                'note' => $resource->get('skos:note'), // there could be different types of notes, so probably this must be a related entity in the end
                'top_concept' => $schemeTopConceptUris->contains($resource->getUri()) || collect($resource->all('skos:topConceptOf'))->map->getUri()->contains($insertedScheme->uri),
            ];

        });

        SkosConcept::query()->upsert($conceptsToImport->toArray(), 'uri');

        // Get all broader relations and insert them

        $broaderConcepts = $conceptsInFile->map(function(Resource $conceptResource) use ($insertedScheme) {

            return [
                'concept_uri' => $conceptResource->getUri(),
                'skos_concept_scheme_id' => $insertedScheme->getKey(),
                'broader_concepts_uri' => collect($conceptResource->all('skos:broader'))->map->getUri(),
            ];

        });

        $conceptsCache = SkosConcept::query()
            ->whereIn('uri', $broaderConcepts->pluck('concept_uri')->unique()->merge($broaderConcepts->pluck('broader_concepts_uri')->flatten()->unique()))
            ->pluck('id', 'uri');

        $broaderRelations = $broaderConcepts->map(function($entry) use ($conceptsCache){

            $sourceConcept = $conceptsCache->get($entry['concept_uri']);

            return $entry['broader_concepts_uri']->map(function($b) use ($conceptsCache, $sourceConcept){

                throw_unless($conceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    'source_skos_concept_id' => $sourceConcept,
                    'target_skos_concept_id' => $conceptsCache->get($b),
                    'type' => SkosRelationType::BROADER,
                ];
            });
        })
        ->flatten(1);

        SkosRelation::query()->upsert($broaderRelations->toArray(), [
            'source_skos_concept_id',
            'target_skos_concept_id',
            'type',
        ]);
        
        // Get all narrower relations and insert them

        $narrowerConcepts = $conceptsInFile->map(function(Resource $conceptResource) use ($insertedScheme) {

            return [
                'concept_uri' => $conceptResource->getUri(),
                'skos_concept_scheme_id' => $insertedScheme->getKey(),
                'narrower_concepts_uri' => collect($conceptResource->all('skos:narrower'))->map->getUri(),
            ];

        });

        $narrowerConceptsCache = SkosConcept::query()
            ->whereIn('uri', $narrowerConcepts->pluck('concept_uri')->unique()->merge($narrowerConcepts->pluck('narrower_concepts_uri')->flatten()->unique()))
            ->pluck('id', 'uri');

        $narrowerRelations = $narrowerConcepts->map(function($entry) use ($narrowerConceptsCache){

            $sourceConcept = $narrowerConceptsCache->get($entry['concept_uri']);

            return $entry['narrower_concepts_uri']->map(function($b) use ($narrowerConceptsCache, $sourceConcept){

                throw_unless($narrowerConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    'source_skos_concept_id' => $sourceConcept,
                    'target_skos_concept_id' => $narrowerConceptsCache->get($b),
                    'type' => SkosRelationType::NARROWER,
                ];
            });
        })
        ->flatten(1);

        SkosRelation::query()->upsert($narrowerRelations->toArray(), [
            'source_skos_concept_id',
            'target_skos_concept_id',
            'type',
        ]);
        
        // Get all related relations and insert them

        $relatedConcepts = $conceptsInFile->map(function(Resource $conceptResource) use ($insertedScheme) {

            return [
                'concept_uri' => $conceptResource->getUri(),
                'skos_concept_scheme_id' => $insertedScheme->getKey(),
                'related_concepts_uri' => collect($conceptResource->all('skos:related'))->map->getUri(),
            ];

        });

        $relatedConceptsCache = SkosConcept::query()
            ->whereIn('uri', $relatedConcepts->pluck('concept_uri')->unique()->merge($relatedConcepts->pluck('related_concepts_uri')->flatten()->unique()))
            ->pluck('id', 'uri');

        $relatedRelations = $relatedConcepts->map(function($entry) use ($relatedConceptsCache){

            $sourceConcept = $relatedConceptsCache->get($entry['concept_uri']);

            return $entry['related_concepts_uri']->map(function($b) use ($relatedConceptsCache, $sourceConcept){

                throw_unless($relatedConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    'source_skos_concept_id' => $sourceConcept,
                    'target_skos_concept_id' => $relatedConceptsCache->get($b),
                    'type' => SkosRelationType::RELATED,
                ];
            });
        })
        ->flatten(1);

        SkosRelation::query()->upsert($relatedRelations->toArray(), [
            'source_skos_concept_id',
            'target_skos_concept_id',
            'type',
        ]);



        // Import skos:Collection and link concepts

        collect($graph->allOfType("skos:Collection"))
            ->merge($graph->allOfType("skos:OrderedCollection"))
            ->each(function(Resource $resource) use ($insertedScheme){
                $insertedCollection = SkosCollection::query()->updateOrCreate(
                    [
                            'uri' => $resource->getUri(),
                    ],
                    [
                        'skos_concept_scheme_id' => $insertedScheme->getKey(),
                        'pref_label' => $resource->getLiteral(property: 'skos:prefLabel', lang: 'en'),
                        'alt_labels' => collect($resource->all('skos:altLabel'))->map->getValue(),
                        'hidden_labels' => collect($resource->all('skos:hiddenLabel'))->map->getValue(),
                        'notation' => $resource->getLiteral('skos:notation') ?? $resource->getLiteral('dc:identifier'),
                        'definition' => $resource->get('skos:definition') ?? $resource->get('dc:description'),
                        'note' => $resource->get('skos:note'),
                    ]
                );

                $memberList = $resource->all('skos:memberList');

                if(count($memberList) == 0){
                    return;
                }

                $memberUris = collect($memberList[0])->map->getUri();

                $conceptMapping = SkosConcept::query()->whereIn('uri', $memberUris)->pluck('id', 'uri');

                // to keep the order as found in the file
                $members = $memberUris->map(fn($uri) => $conceptMapping[$uri]);

                $insertedCollection->concepts()->sync($members);
            });

        // Get related matches cross vocabularies

        $knownVocabularies = SkosConceptScheme::query()->pluck('vocabulary_base_uri');

        $crossSchemeRelations = $conceptsInFile->map(function(Resource $conceptResource) use ($insertedScheme, $knownVocabularies) {

            return [
                'concept_uri' => $conceptResource->getUri(),
                'skos_concept_scheme_id' => $insertedScheme->getKey(),
                'close_match_uri' => collect($conceptResource->all('skos:closeMatch'))->map->getUri()->filter(fn($uri) => Str::startsWith($uri, $knownVocabularies)),
                'exact_match_uri' => collect($conceptResource->all('skos:exactMatch'))->map->getUri()->filter(fn($uri) => Str::startsWith($uri, $knownVocabularies)),
                'related_match_uri' => collect($conceptResource->all('skos:relatedMatch'))->map->getUri()->filter(fn($uri) => Str::startsWith($uri, $knownVocabularies)),
                'broad_match_uri' => collect($conceptResource->all('skos:broadMatch'))->map->getUri()->filter(fn($uri) => Str::startsWith($uri, $knownVocabularies)),
                'narrow_match_uri' => collect($conceptResource->all('skos:narrowMatch'))->map->getUri()->filter(fn($uri) => Str::startsWith($uri, $knownVocabularies)),
            ];

        });

        $matchConceptsCache = SkosConcept::query()
            ->whereIn('uri', $crossSchemeRelations->pluck('concept_uri')->unique()
                ->concat($crossSchemeRelations->pluck('close_match_uri')->unique())
                ->concat($crossSchemeRelations->pluck('exact_match_uri')->unique())
                ->concat($crossSchemeRelations->pluck('related_match_uri')->unique())
                ->concat($crossSchemeRelations->pluck('broad_match_uri')->unique())
                ->concat($crossSchemeRelations->pluck('narrow_match_uri')->unique())->flatten(1)
                )
            ->pluck('id', 'uri');

        

        $crossSchemeRelationsToInsert = $crossSchemeRelations->map(function($entry) use ($matchConceptsCache){

            $sourceConcept = $matchConceptsCache->get($entry['concept_uri']);

            $close_match_uri = $entry['close_match_uri']->map(function($b) use ($matchConceptsCache, $sourceConcept){
                throw_unless($matchConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    [
                        'source_skos_concept_id' => $sourceConcept,
                        'target_skos_concept_id' => $matchConceptsCache->get($b),
                        'type' => SkosRelationType::CLOSE_MATCH,
                    ],
                    [
                        'source_skos_concept_id' => $matchConceptsCache->get($b),
                        'target_skos_concept_id' => $sourceConcept,
                        'type' => SkosRelationType::CLOSE_MATCH,
                    ],
                ];
            });

            $exact_match_uri = $entry['exact_match_uri']->map(function($b) use ($matchConceptsCache, $sourceConcept){
                throw_unless($matchConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    [
                        'source_skos_concept_id' => $sourceConcept,
                        'target_skos_concept_id' => $matchConceptsCache->get($b),
                        'type' => SkosRelationType::EXACT_MATCH,
                    ],
                    [
                        'source_skos_concept_id' => $matchConceptsCache->get($b),
                        'target_skos_concept_id' => $sourceConcept,
                        'type' => SkosRelationType::EXACT_MATCH,
                    ],
                ];
            });

            $related_match_uri = $entry['related_match_uri']->map(function($b) use ($matchConceptsCache, $sourceConcept){
                throw_unless($matchConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    [
                        'source_skos_concept_id' => $sourceConcept,
                        'target_skos_concept_id' => $matchConceptsCache->get($b),
                        'type' => SkosRelationType::RELATED_MATCH,
                    ],
                    [
                        'source_skos_concept_id' => $matchConceptsCache->get($b),
                        'target_skos_concept_id' => $sourceConcept,
                        'type' => SkosRelationType::RELATED_MATCH,
                    ],
                ];
            });

            $broad_match_uri = $entry['broad_match_uri']->map(function($b) use ($matchConceptsCache, $sourceConcept){
                throw_unless($matchConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    [
                        'source_skos_concept_id' => $sourceConcept,
                        'target_skos_concept_id' => $matchConceptsCache->get($b),
                        'type' => SkosRelationType::BROAD_MATCH,
                    ],
                    [
                        'source_skos_concept_id' => $matchConceptsCache->get($b),
                        'target_skos_concept_id' => $sourceConcept,
                        'type' => SkosRelationType::NARROW_MATCH,
                    ],
                ];
            });

            $narrow_match_uri = $entry['narrow_match_uri']->map(function($b) use ($matchConceptsCache, $sourceConcept){
                throw_unless($matchConceptsCache->get($b), InvalidArgumentException::class, "Cannot find concept [{$b}]");

                return [
                    [
                        'source_skos_concept_id' => $sourceConcept,
                        'target_skos_concept_id' => $matchConceptsCache->get($b),
                        'type' => SkosRelationType::NARROW_MATCH,
                    ],
                    [
                        'source_skos_concept_id' => $matchConceptsCache->get($b),
                        'target_skos_concept_id' => $sourceConcept,
                        'type' => SkosRelationType::BROAD_MATCH,
                    ],
                ];
            });


            return $close_match_uri
                ->concat($exact_match_uri)
                ->concat($related_match_uri)
                ->concat($broad_match_uri)
                ->concat($narrow_match_uri)
                ->flatten(1);
        })
        ->flatten(1);

        SkosMappingRelation::query()->upsert($crossSchemeRelationsToInsert->toArray(), [
            'source_skos_concept_id',
            'target_skos_concept_id',
            'type',
        ]);

    }
}