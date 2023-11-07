<?php

namespace App\Http\Controllers;

use App\Models\Disk;
use App\Models\Document;
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
        return view('document.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        
        $document = Document::create([
            'disk_name' => Disk::DOCUMENTS->value,
            'disk_path' => $path,
            'title' => $file->getClientOriginalName(),
            'mime' => Storage::disk(Disk::DOCUMENTS->value)->mimeType($path),
            'uploaded_by' => $request->user()->getKey(),
            'team_id' => $request->user()->currentTeam->getKey(),
        ]);

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
            'importDocument',
            'importDocument.import',
        ]);

        return view('document.show', [
            'document' => $document,
            'hasActivePipelines' => $document->hasActivePipelines(),
            'importDocument' => $document->importDocument && $document->importDocument->isVisibleBy(auth()->user()) ? $document->importDocument : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {       
        return view('document.edit', [
            'document' => $document
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $this->validate($request, [
            'title' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $document->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);
        
        return to_route('documents.show', $document)
            ->with('flash.banner', __(':document updated.', [
                'document' => $validated['title']
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
