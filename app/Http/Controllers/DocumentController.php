<?php

namespace App\Http\Controllers;

use App\Actions\DeleteDocument;
use App\Models\Disk;
use App\Models\Document;
use App\Models\SkosConcept;
use App\Models\Visibility;
use App\Pipelines\PipelineTrigger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
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
            'sections' => function($query): void{
                $query->whereNull('level')->orWhere('level', '<=', 2);
            },
            'sections.latestSummary',
            'concepts' =>  function($query): void{
                $query->whereRelation('conceptScheme', 'uri', '!=', 'https://vocabulary.oneofftech.xyz/sdg/SDG');
            },
            'concepts.conceptScheme',
        ]);


        $disk = Storage::disk(Disk::DOCUMENT_CLASSIFICATION_RESULTS->value);

        $sdg = null;
        $sdg_stats = null;

        $sdgConcepts = null;

        if($disk->exists("{$document->ulid}/sdg.json")){
            $json = $disk->json("{$document->ulid}/sdg.json");
            $fullClassification = collect($json['classification'] ?? $json['result'] ?? $json)->sortByDesc('score');

            $classification = $fullClassification->take(5);

            $sdg = $classification->where('score', $classification->max('score'))->first();

            $others = $fullClassification->skip(5)->sum('score');


            $sdg_stats = $classification->sortBy('name', SORT_NATURAL)->map(function($entry){

                return [
                    'name' => $entry['name'],
                    'goal' => 'Goal ' . trans("sdg.{$entry['name']}.goal"),
                    'title' => trans("sdg.{$entry['name']}.label"),
                    'color' => trans("sdg.{$entry['name']}.color"),
                    'percentage' => Number::percentage($entry['score'] * 100),
                    'score' => $entry['score'],
                ];
            })->add([
                    'goal' => 'Others',
                    'title' => 'Other goals',
                    'color' => '#d6d3d1',
                    'percentage' => Number::percentage($others * 100),
                    'score' => $others,
                ]);

            $sdgConcepts = SkosConcept::whereIn('notation', $classification->pluck('name'))->get()->keyBy('notation');

            
        }

        $allConcepts = $document->concepts
            ->sortBy([
                ['top_concept', 'desc'],
                ['pref_label', 'asc'],
            ]);

        return view('document.show', [
            'document' => $document,
            'hasActivePipelines' => $document->hasActivePipelines(),
            'importDocument' => null,
            'sdg_stats' => $sdg_stats,
            'sdg' => $sdg,
            'sdgConcepts' => $sdgConcepts,
            'sdgConcept' => $sdgConcepts[$sdg['name']] ?? null,
            'concepts' => $allConcepts->take(6),
            'remaining_concepts' => $allConcepts->skip(6),
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
        ]);

        $document->update([
            'title' => $validated['title'],
        ]);
        
        return to_route('documents.edit', $document)
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
