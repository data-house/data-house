<?php

namespace App\Http\Controllers;

use App\Models\Import;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StartImportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $this->authorize('create', Import::class);

        $validated = $this->validate($request, [
            'import' => ['required', Rule::exists('imports', 'id')->where(function (Builder $query) use ($request) {
                return $query->where('created_by', $request->user()->getKey());
            })],
        ]);

        $import = Import::find($validated['import']);

        $import->start();

        return redirect()->route('imports.show', $import)
            ->with('flash.banner', __('Import started. It might take a while.'));
    }
}
