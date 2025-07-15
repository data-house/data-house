<?php

namespace App\Actions\Catalog;

use App\Models\CatalogEntry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class RestoreCatalogEntry
{

    /**
     * Restore a catalog entry from the trash
     */
    public function __invoke(CatalogEntry $entry, User $user): CatalogEntry
    {
        throw_unless($user->can('restore', $entry), AuthorizationException::class);

        /**
         * @var \App\Models\CatalogEntry
         */
        $entry = DB::transaction(function() use ($entry, $user) {

            $entry->restore();

            $entry->trashed_by = null;
            $entry->save();

            return $entry;
        });



        return $entry;
    }
}
