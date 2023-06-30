<?php

namespace App\Http\Controllers;

use App\Models\Import;
use Illuminate\Http\Request;

class StartImportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $this->validate($request, [
            'import' => ['required', 'exists:imports,id'],
        ]);

        $import = Import::find($validated['import']);

        $import->start();

        return redirect()->route('imports.show', $import)
            ->with('flash.banner', __('Import started. It might take a while.'));
    }
}
