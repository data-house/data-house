<?php

namespace App\Actions\Catalog\Flow;

use App\Actions\Catalog\CreateCatalogEntry;
use App\CatalogFieldActionType;
use App\CatalogFieldType;
use App\Copilot\CopilotManager;
use App\Copilot\CopilotResponse;
use App\Data\Catalog\Flows\RewriteConfigurationData;
use App\Data\Catalog\Flows\StructuredExtractionConfigurationData;
use App\Models\CatalogEntry;
use App\Models\CatalogFieldFlow;
use App\Models\CatalogFieldFlowRun;
use App\Models\CatalogFlow;
use App\Models\CatalogFlowRun;
use App\Models\CatalogValue;
use App\Models\Document;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use OneOffTech\LibrarianClient\Dto\Document as DtoDocument;

class ExecuteCatalogFieldFlow
{

    /**
     * Execute the flow over a catalog entry and store the result
     */
    public function __invoke(CatalogFieldFlow $flow, CatalogEntry $entry, ?CatalogFieldFlowRun $flowRun = null, ?User $user = null): array
    {
        throw_unless($flow->configuration instanceof RewriteConfigurationData, __('Only rewrite flows are supported'));
        
        throw_unless($flow->action == CatalogFieldActionType::AI_REWRITE, __('Only rewrite flows are supported'));

        /**
         * @var \App\Data\Catalog\Flows\RewriteConfigurationData
         */
        $flowConfiguration = $flow->configuration;

        // GET value from $entry field

        $sourceFieldId = $flow->source_field_id;
        $sourceValue = $entry->catalogValues()
            ->where('catalog_field_id', $sourceFieldId)
            ->first();

        throw_if(blank($sourceValue), __('Field empty'));

        $fieldValue = $sourceValue->{$sourceValue->catalogField->data_type->valueFieldName()};

        throw_if(blank($fieldValue), __('Field empty'));

        // Execute action

        
        /**
         * @var \App\Copilot\CopilotResponse
         */
        $result = app(CopilotManager::class)->driver()->chat(
            user: $fieldValue,
            prompt: $flowConfiguration->prompt,
            chatId: $entry->uuid,
        );

        $response = str($result->text)
            ->remove(['<abbr>', '</abbr>'])
            ->replaceMatches('/<cluster>.*?<\/cluster>/s', '')
            ->__toString();

        throw_if(blank($response), __('Cannot generate'));

        // Store value in the target field catalog value

        // TODO: How I can identify a negative reply?

        

        $targetField = $flow->targetField;
        $targetValue = $entry->catalogValues()
            ->where('catalog_field_id', $targetField->getKey())
            ->first();

        if(blank($targetValue)){
            $entry->catalogValues()->create([
                $targetField->data_type->valueFieldName() => $response,
                'user_id' => $user->getKey(),
                'catalog_id' => $entry->catalog->getKey(),
                'catalog_field_id' => $targetField->getKey(),
            ]);
        }
        else if($flow->overwrite_existing){

            $targetValue->updated_by = $user->getKey();

            $targetValue->{$targetField->data_type->valueFieldName()} = $response;

            $targetValue->save();

        }
        else if(!$flow->overwrite_existing ){
            throw new Exception(__('Field already with value. Overwrite not allowed'));
        }

        // create or update CatalogFieldFlowRun

        logs()->info("Field flow executed [flow: {$flow->getKey()}] [entry: {$entry->getKey()}]", ['response' => $response]);

        return [
            'response' => $response,
        ];
    }
}
