<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

class InternalDocumentDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Document $document)
    {
        $disposition = $request->string('disposition', HeaderUtils::DISPOSITION_ATTACHMENT);

        return response()
            ->download(Storage::disk($document->disk_name)->path($document->disk_path), null, [], $disposition);
    }
}
