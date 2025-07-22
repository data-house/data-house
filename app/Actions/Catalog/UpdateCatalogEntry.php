<?php

namespace App\Actions\Catalog;

use App\Actions\Catalog\Flow\ExecuteCatalogFieldFlow;
use App\CatalogFieldType;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\CatalogValue;
use App\Models\Project;
use App\Models\SkosCollection;
use App\Models\User;
use App\Models\Document;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Smalot\PdfParser\Exception\NotImplementedException;

class UpdateCatalogEntry
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(CatalogEntry $entry, Catalog $catalog, array $data,  ?User $user = null): CatalogEntry
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('update', $entry), AuthorizationException::class);
        
        $validatedData = Validator::make($data, [
            'entry_index' => ['nullable', 'integer'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.field' => ['required', 'integer', /* 'uuid' */ ],
            'values.*.value' => ['nullable'],
        ])->validate();

        // TODO: get current instances of values for each field and then update them

        $fields = $catalog->fields()->get()->mapWithKeys(fn($field) => [$field->id => $field->data_type]);

        $currentValues = $entry->catalogValues->keyBy('catalog_field_id');

        $newValues = collect($validatedData['values'])->keyBy('field');

        $currentFieldsToUpdateWithNewValues = $currentValues->intersectByKeys($newValues);

        $newValuesToCreate = $newValues->diffKeys($currentValues)->map(function($val) use ($user, $catalog, $fields){

            $type = $fields[$val['field']];

            if(blank($val['value'])) {
                return null;
            }

            return [
                $type->valueFieldName() => $val['value'],
                'user_id' => $user->getKey(),
                'catalog_id' => $catalog->getKey(),
                'catalog_field_id' => $val['field'],
            ];
        })->filter();

        $document = filled($validatedData['document_id'] ?? null) ? Document::findOrFail($validatedData['document_id'])->load('project') : null;
        $project = filled($validatedData['project_id'] ?? null) ? Project::findOrFail($validatedData['project_id']) : ($document?->project ?? null);

        /**
         * @var \App\Models\CatalogEntry
         */
        $entry = DB::transaction(function() use ($entry, $user, $currentFieldsToUpdateWithNewValues, $newValues, $catalog, $newValuesToCreate, $document, $project, $fields) {

            // Touch the entry

            $entry->updated_by = $user->getKey();
            $entry->document_id = $document?->getKey() ?? null;
            $entry->project_id = $project?->getKey() ?? null;

            $entry->save();

            // Update existing field values 

            $currentFieldsToUpdateWithNewValues->each(function(CatalogValue $catalogValue) use ($newValues, $user, $fields){
                $catalogValue->updated_by = $user->getKey();

                $type = $fields[$catalogValue->catalog_field_id];

                $updatedValue = $newValues[$catalogValue->catalog_field_id];

                $catalogValue->{$type->valueFieldName()} = $updatedValue['value'] ?? null;

                $catalogValue->save();
            });

            // Create missing values
            $entry->catalogValues()->createMany($newValuesToCreate);

            return $entry;
        });

        $executeFlow = app()->make(ExecuteCatalogFieldFlow::class);


        // Get all fields with their values and flows
        $catalog->fields()
            ->with(['flows' => function($query) {
                $query->where('auto_trigger', true);
            }])
            ->get()
            ->pluck('flows')
            ->flatten()
            ->each(function($flow) use ($entry, $executeFlow, $user) {
                try {
                    $executeFlow($flow, $entry, user: $user);
                } catch (\Throwable $th) {
                    report($th);
                }
            });

        return $entry;
    }
}
