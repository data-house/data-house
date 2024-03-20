<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportSource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;

class ImportController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Import::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $imports = Import::query()
            ->createdBy(auth()->user())
            ->withCount('maps')
            ->get();

        return view('import.index', [
            'imports' => $imports,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        return view('import.create', [
            'sources' => ImportSource::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'source' => ['required', new Enum(ImportSource::class)],
            'url' => ['required', 'string', 'url', 'max:1000'],
            'user' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string', 'max:1000'],
        ]);

        $import = $request->user()->imports()->create([
            'source' => $validated['source'],
            'configuration' => Arr::except($validated, ['source']),
        ]);

        return redirect()->route('imports.show', $import);
    }

    /**
     * Display the specified resource.
     */
    public function show(Import $import)
    {

        $import->load(['maps', 'maps.mappedTeam']);

        return view('import.show', [
            'import' => $import,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Import $import)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Import $import)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Import $import)
    {
        //
    }
}
