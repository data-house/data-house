<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportMap;
use App\Models\Visibility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ImportMapsController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(ImportMap $mapping)
    {
        $this->authorize($mapping);

        $mapping->load(['import']);

        return view('import-map.show', [
            'mapping' => $mapping,
            'import' => $mapping->import,
            'documents' => $mapping->documents()->paginate(50),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Import $import)
    {
        $this->authorize(ImportMap::class);

        return view('import-map.create', [
            'import' => $import,
            'teams' => auth()->user()->allTeams(),
            'uploader' => auth()->user(),
            'defaultVisibility' => Visibility::defaultDocumentVisibility(),
            'availableVisibility' => [
                Visibility::TEAM,
                Visibility::PROTECTED,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Import $import)
    {
        $this->authorize(ImportMap::class);

        $userTeams = auth()->user()->allTeams();

        $validated = $this->validate($request, [
            'recursive' => [ 'nullable', 'boolean' ],
            'team' => ['required', Rule::in($userTeams->map->getKey()) ],
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string', 'max:1000'],
            'visibility' => ['nullable', 'integer', new Enum(Visibility::class), Rule::in(Visibility::forDocuments())],
        ]);

        // TODO: figure out a way to validate if the specified paths exists in the import file system

        $import->maps()->create([
            'mapped_team' => $validated['team'],
            'mapped_uploader' => auth()->user()->getKey(),
            'recursive' => $validated['recursive'] ?? false,
            'filters' => [
                'paths' => $validated['paths']
            ],
            'visibility' => $validated['visibility'] ?? Visibility::defaultDocumentVisibility(),
        ]);

        return redirect()->route('imports.show', $import);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImportMap $mapping)
    {
        $this->authorize($mapping);

        return view('import-map.edit', [
            'mapping' => $mapping,
            'paths' => $mapping->filters['paths'] ?? [],
            'import' => $mapping->import,
            'teams' => auth()->user()->allTeams(),
            'uploader' => auth()->user(),
            'defaultVisibility' => Visibility::defaultDocumentVisibility(),
            'availableVisibility' => [
                Visibility::TEAM,
                Visibility::PROTECTED,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImportMap $mapping)
    {
        $this->authorize($mapping);

        $userTeams = auth()->user()->allTeams();

        $validated = $this->validate($request, [
            'recursive' => [ 'nullable', 'boolean' ],
            'team' => ['required', Rule::in($userTeams->map->getKey()) ],
            'visibility' => ['nullable', 'integer', new Enum(Visibility::class), Rule::in(Visibility::forDocuments())],
        ]);

        $mapping->update([
            'mapped_team' => $validated['team'],
            'mapped_uploader' => auth()->user()->getKey(),
            'recursive' => $validated['recursive'] ?? false,
            'visibility' => $validated['visibility'] ?? Visibility::defaultDocumentVisibility(),
        ]);

        return redirect()->route('mappings.show', $mapping);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImportMap $mapping)
    {
        $this->authorize($mapping);
    }
}
