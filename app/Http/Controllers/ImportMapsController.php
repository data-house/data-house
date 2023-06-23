<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportMap;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImportMapsController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(ImportMap::class);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Import $import)
    {
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

        $userTeams = auth()->user()->allTeams();

        // dd($request->all());

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImportMap $importMap)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImportMap $importMap)
    {
        //
    }
}
