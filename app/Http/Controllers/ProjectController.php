<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\GeographicRegion;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Topic;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Project::class);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchQuery = $request->has('s') ? $request->input('s') : null;

        $filters = $request->hasAny(['countries', 'type', 'region', 'topics', 'status']) ? $request->only(['countries', 'type', 'region', 'topics', 'status']) : [];

        $projects = $searchQuery || $filters 
            ? Project::advancedSearch($searchQuery, $filters)->paginate(50)
            : Project::query()->orderBy('title', 'ASC')->paginate(50);

        $projects->withQueryString();

        $countries = Project::pluck('countries')->flatten()->unique('value');

        $facets = [
            'type' => ProjectType::cases(),
            'countries' => $countries->map->toCountryName(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(), // Project::pluck('topics')->flatten()->unique(),
        ];

        return view('project.index', [
            'projects' => $projects,
            'searchQuery' => $searchQuery,
            'filters' => $filters,
            'is_search' => $searchQuery || $filters,
            'facets' => $facets,
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
    public function show(Project $project, Request $request)
    {
        $searchQuery = $request->has('s') ? $request->input('s') : null;

        $sourceFilters = $request->hasAny(['source']) ? $request->only(['source']) : ['source' => 'all-teams'];

        $teamFilters = $sourceFilters['source'] === 'current-team' ? ['team_id' => $request->user()->currentTeam->getKey()] : [];

        $filters = $request->hasAny(['project_countries', 'format', 'type', 'project_region', 'project_topics']) ? $request->only(['project_countries', 'format', 'type', 'project_region', 'project_topics']) : [];

        $searchFilters = array_merge($filters, $teamFilters);

        $documents = ($searchQuery || $searchFilters)
            ? Document::tenantSearch($searchQuery, $searchFilters, $request->user(), $project)->paginate(50)
            : Document::query()->inProject($project)->visibleBy($request->user())->paginate(50);

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
            'countries' => $countries->map->toCountryName(),
            'regions' => GeographicRegion::facets($countries?->map->value),
            'organizations' => [],
            'topic' => Topic::facets(),
        ];

        return view('project.show', [
            'project' => $project,
            'documents' => $documents,
            'topics' => Topic::from($project->topics),

            'searchQuery' => $searchQuery,
            'filters' => array_merge($filters, $sourceFilters),
            'is_search' => $searchQuery || $searchFilters,
            'facets' => $facets,
            'applied_filters_count' => count(array_keys($searchFilters ?? [] )),
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
