<?php

namespace App\Http\Controllers;

use App\Http\Requests\RetrievalRequest;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Sorting\Sorting;
use App\Topics\Facades\Topic;
use Illuminate\Http\Request;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Project::class);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(QueryBuilderRequest $request)
    {
        $searchQuery = $request->has('s') ? $request->input('s') : null;

        $filters = $request->hasAny(['countries', 'type', 'region', 'topics', 'status']) ? $request->only(['countries', 'type', 'region', 'topics', 'status']) : [];

        $sorting = Sorting::for(Project::class);

        $defaultSort = $sorting->defaultSort();

        $sorts = $sorting->mapRequested($request->sorts())->whenEmpty(function($collection) use ($defaultSort){
            return $collection->push($defaultSort);
        });

        $projects = $searchQuery || $filters 
            ? Project::advancedSearch($searchQuery, $filters, $sorts)->paginate(48)
            : QueryBuilder::for(Project::class, $request)
                ->defaultSort($defaultSort->toFieldString())
                ->allowedSorts($sorting->allowedSortsForBuilder())
                ->allowedFilters([])
                ->allowedFields([])
                ->allowedIncludes([])
                ->paginate(48);

        $projects->withQueryString();

        $countries = Project::pluck('countries')->flatten()->unique('value');

        $facets = [
            'type' => ProjectType::cases(),
            'countries' => $countries->map->getNameInLanguage(LanguageAlpha2::English)->sort(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(),
            'status' => ProjectStatus::facets(),
        ];

        return view('project.index', [
            'projects' => $projects,
            'searchQuery' => $searchQuery,
            'filters' => $filters,
            'is_search' => $searchQuery || $filters,
            'facets' => $facets,
            'topics' => Topic::facets(),
            'applied_filters_count' => count(array_keys($filters ?? [] )),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, RetrievalRequest $request)
    {
        $searchFilters = $request->filters();

        $documents = Document::retrieve($request, $project)->paginate(50);

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

        return view('project.show', [
            'project' => $project,
            'documents' => $documents,
            'topics' => $project->formattedTopics(),
            'searchQuery' => $request->searchQuery(),
            'filters' => $searchFilters->except('team_id')->toArray(),
            'is_search' => $request->isSearch() || $request->hasAppliedFilters(),
            'facets' => $facets,
            'search_topics' => Topic::facets(),
            'sorting' => $request->sorts()->join(','),
            'applied_filters_count' => $request->appliedFiltersCount(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
