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

        $documents = Document::all();

        return view('library.index', [
            'documents' => $documents,
        ]);
    }
}
