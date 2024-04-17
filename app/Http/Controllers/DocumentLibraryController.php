<?php

namespace App\Http\Controllers;

use App\Http\Requests\RetrievalRequest;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\Topic;
use App\Sorting\Sorting;
use Illuminate\Http\Request;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Spatie\QueryBuilder\QueryBuilderRequest;

class DocumentLibraryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RetrievalRequest $request)
    {
        $searchFilters = $request->filters();

        $documents = Document::retrieve($request)->paginate(50);

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
            'searchQuery' => $request->searchQuery(),
            'filters' => $searchFilters->except('team_id')->toArray(),
            'is_search' => $request->isSearch() || $request->hasAppliedFilters(),
            'facets' => $facets,
            'search_topics' => Topic::facets(),
            'sorting' => $request->sorts()->join(','),
            'applied_filters_count' => $request->appliedFiltersCount(),
        ]);
    }
}
