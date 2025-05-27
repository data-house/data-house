<?php

namespace App\Actions\Catalog;

use App\CatalogFieldType;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\SkosCollection;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Smalot\PdfParser\Exception\NotImplementedException;

class CreateCatalogEntry
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(Catalog $catalog, array $data,  ?User $user = null): CatalogEntry
    {
        // TODO: think about contraints


        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('update', $catalog), AuthorizationException::class);
        throw_unless($user->can('create', CatalogEntry::class), AuthorizationException::class);

        Validator::make($data, [
            'document_id' => ['nullable', 'exists:documents,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'description' => ['nullable', 'string', 'max:6000'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.field' => ['required', 'string', /* 'uuid' */ ], // TODO: validate existence in catalog fields
            'values.*.value' => ['nullable'],
        ])->validate();

        // TODO: after initial validation all fields should be validated against the specific field constraints


        $valuesMappedToCatalogValues = collect($data['values'])->map(function($val) use ($user, $catalog){
            return [
                'value_text' => $val['value'],
                'value_int' => null,
                'value_date' => null,
                'value_float' => null,
                'value_bool' => null,
                'value_concept' => null,
                'user_id' => $user->getKey(),
                'catalog_id' => $catalog->getKey(),
                'catalog_field_id' => $val['field'],
            ];
        });

        /**
         * @var \App\Models\CatalogEntry
         */
        $entry = DB::transaction(function() use ($user, $data, $catalog, $valuesMappedToCatalogValues){

            $entry = CatalogEntry::forceCreate([
                    'catalog_id' => $catalog->getKey(),
                    // 'document_id' => ,
                    // 'project_id' => ,
                    'user_id' => $user->getKey(),
            ]);

            $entry->catalogValues()->createMany($valuesMappedToCatalogValues);

            return $entry;
        });



        return $entry;
    }
}
