<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DocumentDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        $disposition = $request->string('disposition', HeaderUtils::DISPOSITION_ATTACHMENT);
        
        $original = $request->boolean('original', false);

        if(!in_array($disposition, [HeaderUtils::DISPOSITION_ATTACHMENT, HeaderUtils::DISPOSITION_INLINE])){
            $disposition = HeaderUtils::DISPOSITION_ATTACHMENT;
        }

        if(!$original && ($document->conversion_file_mime && $document->conversion_disk_path)){

            return response()
                ->download(Storage::disk($document->conversion_disk_name)->path($document->conversion_disk_path), $document->filenameForDownload(false), [], $disposition);
        }

        return response()
            ->download(Storage::disk($document->disk_name)->path($document->disk_path), $document->filenameForDownload(), [], $disposition);
    }
}
