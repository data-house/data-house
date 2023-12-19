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
        $searchQuery = $request->has('s') ? $request->input('s') : null;

        $sourceFilters = $request->hasAny(['source']) ? $request->only(['source']) : ['source' => 'all-teams'];

        $teamFilters = $sourceFilters['source'] === 'current-team' ? ['team_id' => $request->user()->currentTeam->getKey()] : [];

        $filters = $request->hasAny(['project_countries', 'type', 'project_region', 'project_topics']) ? $request->only(['project_countries', 'type', 'project_region', 'project_topics']) : [];

        $searchFilters = array_merge($filters, $teamFilters);

        $documents = ($searchQuery || $searchFilters)
            ? Document::tenantSearch($searchQuery, $searchFilters)->paginate(150)
            : Document::query()->visibleBy(auth()->user())->paginate(150);

        $countries = Project::pluck('countries')->flatten()->unique('value');

        $facets = [
            'source' => ['all-teams', 'current-team'],
            'type' => DocumentType::cases(),
            'countries' => $countries->map->toCountryName(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(),
        ];

        return view('library.index', [
            'documents' => $documents,
            'searchQuery' => $searchQuery,
            'filters' => array_merge($filters, $sourceFilters),
            'is_search' => $searchQuery || $searchFilters,
            'facets' => $facets,
            'applied_filters_count' => count(array_keys($searchFilters ?? [] )),
        ]);
    }
}
