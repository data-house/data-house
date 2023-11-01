<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportMap;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImportMapsController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(ImportMap $mapping)
    {
        $this->authorize($mapping);

        $mapping->load(['import', 'documents']);

        return view('import-map.show', [
            'mapping' => $mapping,
            'import' => $mapping->import,
            'documents' => $mapping->documents,
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
        ]);

        // TODO: figure out a way to validate if the specified paths exists in the import file system

        $import->maps()->create([
            'mapped_team' => $validated['team'],
            'mapped_uploader' => auth()->user()->getKey(),
            'recursive' => $validated['recursive'] ?? false,
            'filters' => [
                'paths' => $validated['paths']
            ],
        ]);


        return redirect()->route('imports.show', $import);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImportMap $importMap)
    {
        $this->authorize($importMap);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImportMap $importMap)
    {
        $this->authorize($importMap);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImportMap $importMap)
    {
        $this->authorize($importMap);
    }
}
