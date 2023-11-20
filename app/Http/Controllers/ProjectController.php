<?php

namespace App\Http\Controllers;

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

        $filters = $request->hasAny(['countries', 'type', 'region', 'topics']) ? $request->only(['countries', 'type', 'region', 'topics']) : [];

        $projects = $searchQuery || $filters 
            ? Project::advancedSearch($searchQuery, $filters)->paginate(50)
            : Project::query()->paginate(50);


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
    public function show(Project $project)
    {
        $project->load('documents');

        return view('project.show', [
            'project' => $project,
            'documents' => $project->documents,
            'topics' => Topic::from($project->topics),
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
