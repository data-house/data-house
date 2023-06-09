<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\MimeType;
use Illuminate\Http\Request;
use PrinsFrank\Standards\Http\HttpStatusCode;

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

        if(($document->mime !== MimeType::APPLICATION_PDF->value && is_null($document->conversion_file_mime)) 
            || ($document->mime !== MimeType::APPLICATION_PDF->value && $document->conversion_file_mime && $document->conversion_file_mime !== MimeType::APPLICATION_PDF->value)){
            return response('', HttpStatusCode::Unsupported_Media_Type->value);
        }

        return view('pdf.viewer', [
            'document' => $document,
            'page' => $request->integer('page', 1),
        ]);
    }
}
