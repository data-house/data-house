<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConceptCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminTaxonomiesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Gate::authorize('admin-area');

        return view('admin.taxonomy.index', [
            'collections' => ConceptCollection::all(),
        ]);
    }
}
