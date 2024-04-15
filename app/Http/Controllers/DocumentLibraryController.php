<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\Topic;
use Illuminate\Http\Request;
use PrinsFrank\Standards\Language\LanguageAlpha2;

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

        $starredFilters = $request->hasAny(['starred']) ? ['stars' => $request->user()->getKey()] : [];

        $filters = $request->hasAny(['project_countries', 'format', 'type', 'project_region', 'project_topics']) ? $request->only(['project_countries', 'format', 'type', 'project_region', 'project_topics']) : [];

        $searchFilters = array_merge($filters, $teamFilters, $starredFilters);

        $documents = ($searchQuery || $searchFilters)
            ? Document::tenantSearch($searchQuery, $searchFilters)->paginate(50)
            : Document::queryUsingBuilder()->paginate(50);

        $documents->withQueryString();

        $countries = Project::pluck('countries')->flatten()->unique('value');

        $facets = [
            'source' => ['all-teams', 'current-team'],
            'format' => [
                'PDF',
                'Word',
                'Spreadsheet',
                'Slideshow',
                'Image',
                'Compressed folder',
            ],
            'type' => DocumentType::cases(),
            'countries' => $countries->map->getNameInLanguage(LanguageAlpha2::English)->sort(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(),
        ];

        return view('library.index', [
            'documents' => $documents,
            'searchQuery' => $searchQuery,
            'filters' => array_merge($filters, $starredFilters, $sourceFilters),
            'is_search' => $searchQuery || $searchFilters,
            'facets' => $facets,
            'search_topics' => Topic::facets(),
            'applied_filters_count' => count(array_keys($searchFilters ?? [] )),
        ]);
    }
}
