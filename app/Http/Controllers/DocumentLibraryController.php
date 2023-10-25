<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\Topic;
use Illuminate\Http\Request;

class DocumentLibraryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $searchQuery = $request->has('s') ? e($request->input('s')) : null;

        $filters = $request->hasAny(['project_countries', 'type', 'project_region', 'project_topics']) ? $request->only(['project_countries', 'type', 'project_region', 'project_topics']) : [];

        // TODO: SEARCH should show protected and public doc + user visibility doc + doc with team visibility that are part of current_team

        $documents = ($searchQuery || $filters)
            ? Document::tenantSearch(e($searchQuery), $filters)->paginate(150)
            : Document::query()->visibleBy(auth()->user())->paginate(150);

        $countries = Project::pluck('countries')->flatten()->unique('value');

        $facets = [
            'type' => DocumentType::cases(),
            'countries' => $countries->map->toCountryName(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(),
        ];

        return view('library.index', [
            'documents' => $documents,
            'searchQuery' => $searchQuery,
            'filters' => $filters,
            'is_search' => $searchQuery || $filters,
            'facets' => $facets,
            'applied_filters_count' => count(array_keys($filters ?? [] )),
        ]);
    }
}
