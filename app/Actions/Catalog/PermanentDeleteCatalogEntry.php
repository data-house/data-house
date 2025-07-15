<?php

namespace App\Actions\Catalog;

use App\Models\CatalogEntry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PermanentDeleteCatalogEntry
{

    /**
     * Permanently delete an entry and its associated values
     */
    public function __invoke(CatalogEntry $entry, User $user): void
    {
        throw_unless($user->can('forceDelete', $entry), AuthorizationException::class);

        DB::transaction(function() use ($entry, $user) {
            $entry->catalogValues()->delete();

            $entry->catalogFlowRun?->delete();

            $entry->forceDelete();
        });

    }
}
