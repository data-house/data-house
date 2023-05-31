<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class PdfViewerController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $document = Document::whereUlid($request->input('document'))->firstOrFail();
        
        $this->authorize('view', $document);

        return view('pdf.viewer', [
            'document' => $document,
            'page' => $request->integer('page', 1),
        ]);
    }
}
