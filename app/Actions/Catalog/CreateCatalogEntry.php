<?php

namespace App\Actions\Catalog;

use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\Document;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCatalogEntry
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(Catalog $catalog, array $data,  ?User $user = null): CatalogEntry
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        
        throw_unless($user->can('create', [CatalogEntry::class, $catalog]), AuthorizationException::class);

        
        $validatedData = Validator::make($data, [
            'entry_index' => ['nullable', 'integer'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.field' => ['required', 'integer', Rule::exists('catalog_fields', 'id')->where(function($query) use ($catalog){
                $query->where('catalog_id', $catalog->getKey());
            }) ],
            'values.*.value' => ['nullable'],
        ])->validate();

        // TODO: validate each field against own constraint when fields can have additional constraints

        $fields = $catalog->fields()->get()->mapWithKeys(fn($field) => [$field->id => $field->data_type]);


        $valuesMappedToCatalogValues = collect($validatedData['values'])->map(function($val) use ($user, $catalog, $fields){

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
        $entry = DB::transaction(function() use ($user, $validatedData, $catalog, $valuesMappedToCatalogValues, $document, $project) {

            $entry = CatalogEntry::create([
                    'entry_index' => $validatedData['entry_index'] ?? null,
                    'catalog_id' => $catalog->getKey(),
                    'document_id' => $document?->getKey() ?? null,
                    'project_id' => $project?->getKey() ?? null,
                    'user_id' => $user->getKey(),
            ]);

            $entry->catalogValues()->createMany($valuesMappedToCatalogValues);

            return $entry;
        });



        return $entry;
    }
}
