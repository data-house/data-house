<?php

namespace App\Actions\Catalog;

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

class DeleteCatalogEntry
{

    /**
     * Add an entry with values to a catalog
     */
    public function __invoke(CatalogEntry $entry, ?User $user = null): CatalogEntry
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('delete', $entry), AuthorizationException::class);

        /**
         * @var \App\Models\CatalogEntry
         */
        $entry = DB::transaction(function() use ($entry, $user) {

            $entry->trashed_by = $user->getKey();
            $entry->save();

            $entry->delete();

            return $entry;
        });



        return $entry;
    }
}
