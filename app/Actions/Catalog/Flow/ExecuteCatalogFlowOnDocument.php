<?php

namespace App\Actions\Catalog\Flow;

use App\Actions\Catalog\CreateCatalogEntry;
use App\Catalog\Flow\FlowTargetEntity;
use App\Catalog\Flow\FlowTrigger;
use App\CatalogFieldType;
use App\Data\Catalog\Flows\FlowConfiguration;
use App\Data\Catalog\Flows\StructuredExtractionConfigurationData;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\CatalogFlow;
use App\Models\CatalogFlowRun;
use App\Models\Project;
use App\Models\SkosCollection;
use App\Models\User;
use App\Models\Document;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OneOffTech\LibrarianClient\Dto\Document as DtoDocument;
use Smalot\PdfParser\Exception\NotImplementedException;

class ExecuteCatalogFlowOnDocument
{

    /**
     * Execute the flow over a document and store the result in the catalog
     */
    public function __invoke(CatalogFlow $flow, Document $document, ?CatalogFlowRun $flowRun = null ): array
    {
        throw_unless($flow->configuration instanceof StructuredExtractionConfigurationData, 'Only structured extraction flows are supported');

        // Get document content in the same format acceptable by the AI layer
        $content = $document->toQuestionableArray();

        /**
         * @var \App\Data\Catalog\Flows\StructuredExtractionConfigurationData
         */
        $flowConfiguration = $flow->configuration;

        $schema = $flowConfiguration->schema['json_schema']['schema'];

        // assuming that the top level entries to retrieve are marked as required

        $properties = collect($schema['properties'])->only($schema['required']);

        throw_if($properties->isEmpty(), 'No required properties in schema definition');
        throw_if($properties->count() > 1, 'Currently we only support one top level property');

        $propertyOfInterest = $properties->first();

        throw_if($propertyOfInterest['type'] !== 'array', 'Currently we only support a top level property defined as an array type');

        $fieldContainingResultsInResponse = $properties->keys()->first();

        $extraction = $document->questionableUsing()->extract(
            structuredResponseModel: json_encode($flowConfiguration->schema),
            document: new DtoDocument($content['id'], $content['lang'], $content['data']->toArray()),
            sections: $flowConfiguration->document_sections,
            instructions: $flowConfiguration->instructions,
        );

        $extractionResponse = $extraction->content;

        // I have field-UUID => json_attribute
        $fieldToAttribute = $flowConfiguration->attributes_to_field;

        $catalog = $flow->catalog;
        

        $fields = $catalog->fields()->whereIn('uuid', array_keys($flowConfiguration->attributes_to_field))->get()->keyBy('uuid');

        $createEntry = app()->make(CreateCatalogEntry::class);

        $extractedEntries = collect($extractionResponse[$fieldContainingResultsInResponse] ?? []);

        $extractedEntries->each(function($extraction) use ($createEntry, $fields, $document, $fieldToAttribute, $flow, $flowRun){


            $values = $fields->map(function($field) use ($extraction, $fieldToAttribute){
                return [
                    'field' => $field->getKey(),
                    'value' => $extraction[$fieldToAttribute[$field->uuid]],
                ];
            })->values();
            
            $entry = $createEntry(
                catalog: $flow->catalog,
                data: [
                    'entry_index' => null,
                    'document_id' => $document->getKey(),
                    'project_id' => $document->project?->getKey(),
                    'values' => $values->all(),
                ], 
                user: $flow->user,
            );

            if(filled($flowRun)){
                $entry->catalog_flow_run_id = $flowRun->getKey();
                $entry->save();
            }
        });

        return $extractionResponse;
    }
}
