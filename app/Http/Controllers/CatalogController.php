<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Visibility;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $catalogs = Catalog::query()
            ->visibleTo($request->user())
            ->latest('updated_at')
            ->paginate();

        $catalogWithTeamVisibilityExists = Catalog::query()
            ->visibleTo($request->user())
            ->where('visibility', Visibility::TEAM)
            ->exists();
        
        $catalogWithFieldExists = Catalog::query()
            ->visibleTo($request->user())
            ->whereHas('fields')
            ->exists();

        return view('catalog.index', [
            'catalogs' => $catalogs,
            'is_search' => false,
            'hint_create_done' => $catalogs->isNotEmpty(),
            'hint_structure_done' => $catalogWithFieldExists,
            'hint_share_done' => $catalogWithTeamVisibilityExists,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Catalog $catalog)
    {
        $flows = $catalog->flows;

        return view('catalog.show', [
            'catalog' => $catalog,
            'flows' => $flows,
        ]);
    }
}
