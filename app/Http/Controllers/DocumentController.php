<?php

namespace App\Http\Controllers;

use App\Actions\DeleteDocument;
use App\Models\Disk;
use App\Models\Document;
use App\Models\Visibility;
use App\Pipelines\PipelineTrigger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class DocumentController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Document::class);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(config('library.upload.allow_direct_upload'), 404);

        return view('document.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(config('library.upload.allow_direct_upload'), 404);
        
        $validated = $this->validate($request, [
            'document' => [
                'required',
                File::types(['png', 'jpg', 'pdf', 'docx', 'pptx'])
                    ->min(1)
                    ->max(12 * 1024),
            ]
        ]);

        $file = $request->file('document');

        $path = $file->store('', Disk::DOCUMENTS->value);
        
        $document = Document::withoutEvents(fn() => Document::create([
            'disk_name' => Disk::DOCUMENTS->value,
            'disk_path' => $path,
            'title' => $file->getClientOriginalName(),
            'mime' => Storage::disk(Disk::DOCUMENTS->value)->mimeType($path),
            'uploaded_by' => $request->user()->getKey(),
            'team_id' => $request->user()->currentTeam->getKey(),
            'visibility' => Visibility::defaultDocumentVisibility(),
        ]));

        $document->dispatchPipeline(PipelineTrigger::MODEL_CREATED);

        return redirect()
            ->route('documents.library')
            ->with('flash.banner', __('Document uploaded.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $document->load([
            'uploader',
            'team',
            'project',
            'latestSummary',
        ]);

        return view('document.show', [
            'document' => $document,
            'hasActivePipelines' => $document->hasActivePipelines(),
            'importDocument' => null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $document->load([
            'latestSummary',
        ]);

        return view('document.edit', [
            'document' => $document,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $this->validate($request, [
            'title' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);

        $summary = $document->latestSummary;

        if($summary && !$summary->isAiGenerated()){
            $summary->update([
                'text' => $validated['description'],
                'user_id' => auth()->user()->getKey(),
            ]);
        }
        else {
            $document->summaries()->create([
                'language' => 'en',
                'text' => $validated['description'],
                'user_id' => auth()->user()->getKey(),
            ]);
        }

        $document->update([
            'title' => $validated['title'],
        ]);

        
        
        return to_route('documents.show', $document)
            ->with('flash.banner', __(':document updated.', [
                'document' => $validated['title']
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document, DeleteDocument $deleteDocument)
    {
        $deleteDocument($document);

        return redirect()
            ->route('documents.library')
            ->with('flash.banner', __(':Document deleted.', ['document' => $document->title]));
    }
}
