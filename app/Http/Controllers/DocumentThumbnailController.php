<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DocumentThumbnailController extends Controller
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

        abort_if(is_null($document->thumbnail_disk_name), 404);

        $cachingHeaders = ['Cache-Control' => 'public, max-age=31536000'];

        return response()
            ->download(
                Storage::disk($document->thumbnail_disk_name)->path($document->thumbnail_disk_path),
                null,
                $cachingHeaders,
                HeaderUtils::DISPOSITION_ATTACHMENT
            );
    }
}
