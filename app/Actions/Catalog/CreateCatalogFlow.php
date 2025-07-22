<?php

namespace App\Actions\Catalog;

use App\Catalog\Flow\FlowSourceEntity;
use App\Catalog\Flow\FlowTrigger;
use App\CatalogFieldType;
use App\Data\Catalog\Flows\FlowConfiguration;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\CatalogFlow;
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

class CreateCatalogFlow
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(Catalog $catalog, string $title, FlowConfiguration $configuration, FlowTrigger $trigger = FlowTrigger::MANUAL, FlowSourceEntity $entity = FlowSourceEntity::DOCUMENT, ?string $description = null,  ?User $user = null): CatalogFlow
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();
        
        throw_unless($user->can('create', [CatalogFlow::class, $catalog]), AuthorizationException::class);
        
        // TODO: think how to validate the flow configuration schema
        // TODO: fields uuid should be validated against current catalog
        
        $validatedData = Validator::make([
            'title' => $title,
            'description' => $description,
        ], [
            'title' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:6000'],
        ])->validate();


        $flow = $catalog->flows()->create([
            ...$validatedData,
            'trigger' => $trigger,
            'target_entity' => $entity,
            'configuration' => $configuration,
            'user_id' => $user->getKey(),
        ]);

        return $flow;
    }
}
