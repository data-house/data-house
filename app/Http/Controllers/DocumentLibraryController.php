<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentLibraryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $searchQuery = $request->has('s') ? e($request->input('s')) : null;

        $documents = $searchQuery ? Document::search(e($searchQuery))->get() : Document::all();

        return view('library.index', [
            'documents' => $documents,
            'searchQuery' => $searchQuery,
        ]);
    }
}
