<?php

namespace App\Actions;

use App\Copilot\CopilotSummarizeRequest;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Visibility;
use App\PdfProcessing\DocumentContent;
use App\PdfProcessing\Facades\Pdf;
use App\PdfProcessing\PdfDriver;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use \Illuminate\Support\Str;

class ChangeDocumentVisibility
{
    /**
     * Change the visibility of a document
     *
     * @param  \App\Models\Document  $document
     */
    public function __invoke(Document $document, Visibility $visibility, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if(is_null($user)){
            throw new InvalidArgumentException(__('User not recognized. Authentication is required to change a document visibility'));
        }

        if ($user->cannot('update', $document)) {
            throw new AuthorizationException(__('User not allowed to edit document'));
        }

        $document->visibility = $visibility;

        return $document->save();
    }

}
