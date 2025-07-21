<?php

namespace App\Actions\Catalog;

use App\Catalog\Flow\FlowTargetEntity;
use App\Catalog\Flow\FlowTrigger;
use App\CatalogFieldActionType;
use App\CatalogFieldType;
use App\Data\Catalog\Flows\FlowConfiguration;
use App\Models\Catalog;
use App\Models\CatalogEntry;
use App\Models\CatalogField;
use App\Models\CatalogFieldFlow;
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

class CreateCatalogFieldFlow
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(Catalog $catalog, string $title, CatalogFieldActionType $action, FlowConfiguration $configuration, CatalogField $source, CatalogField $target, ?string $description = null,  ?User $user = null): CatalogFieldFlow
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();
        
        throw_unless($user->can('create', [CatalogFieldFlow::class, $catalog]), AuthorizationException::class);
        
        $validatedData = Validator::make([
            'title' => $title,
            'description' => $description,
            // TODO: validate that configuration is compatible with action
        ], [
            'title' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:6000'],
        ])->validate();


        $flow = $source->flows()->create([
            ...$validatedData,
            'catalog_id' => $catalog->getKey(),
            'action' => $action,
            'target_field_id' => $target->getKey(),
            'configuration' => $configuration,
            'user_id' => $user->getKey(),
        ]);

        return $flow;
    }
}
