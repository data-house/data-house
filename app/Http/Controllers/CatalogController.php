<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $catalogs = Catalog::query()->orderBy('updated_at', 'DESC')->get();

        // TODO: ensure users can only see catalogs they can view

        return view('catalog.index', [
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Catalog $catalog)
    {
        return view('catalog.show', [
            'catalog' => $catalog,
        ]);
    }
}
