<?php

namespace App\Actions\Catalog;

use App\CatalogFieldType;
use App\Models\Catalog;
use App\Models\CatalogField;
use App\Models\SkosCollection;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Smalot\PdfParser\Exception\NotImplementedException;

class CreateCatalogField
{

    /**
     * Add a field to a catalog schema
     */
    public function __invoke(Catalog $catalog, string $title, CatalogFieldType $fieldType, ?SkosCollection $skosCollection = null, ?string $description = null , ?User $user = null): CatalogField
    {
        // TODO: think about contraints

        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('update', $catalog), AuthorizationException::class);
        throw_unless($user->can('create', CatalogField::class), AuthorizationException::class);

        $input = [
            'title' => $title,
            'description' => $description,
            'skos_collection' => $skosCollection?->getKey() ?? null,
        ];

        Validator::make($input, [
            'title' => ['required',
                'string',
                'min:1',
                'max:255',
                // TODO: verify field has unique title within catalog
            ],
            'description' => ['nullable', 'string', 'max:6000'],
            'skos_collection' => ['nullable', Rule::requiredIf($fieldType == CatalogFieldType::SKOS_CONCEPT)],
        ])->validate();

        return CatalogField::forceCreate([
                'catalog_id' => $catalog->getKey(),
                'title' => $title,
                'description' => $description,
                'data_type' => $fieldType,
                'user_id' => $user->getKey(),
                'skos_collection_id' => $skosCollection?->getKey(),
            ]);
    }
}
