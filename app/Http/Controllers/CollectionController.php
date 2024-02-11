<?php

namespace App\Http\Controllers;

use App\Actions\Collection\CreateCollection;
use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
use App\Models\Document;
use App\Models\Visibility;
use Illuminate\Http\Request;

class CollectionController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Collection::class, 'collection');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('collection.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateCollection $createCollection)
    {
        $validated = $this->validate($request, [
            'title' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $collection = $createCollection(auth()->user(), [
            ...$validated,
        ]);

        return redirect()
            ->route('collections.show', $collection)
            ->with('flash.banner', __('Collection created.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Collection $collection)
    {
        $collection->load(['documents' => function($query){
            $query
                ->visibleBy(auth()->user());
        }, 'questions' => function($query){
            $query
                ->viewableBy(auth()->user())
                ->orderBy('created_at', 'DESC')
                ->limit(3);
        }]);

        return view('collection.show', [
            'collection' => $collection,
            'documents' => $collection->documents,
            'questions' => $collection->questions ?? [],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        $collection->load(['user', 'team']);

        return view('collection.edit', [
            'owner_user' => $collection->user,
            'owner_team' => $collection->team,
            'collection' => $collection,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        $validated = $this->validate($request, [
            'title' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $collection->title = $validated['title'];

        $collection->save();

        return redirect()
            ->route('collections.show', $collection)
            ->with('flash.banner', __('Collection updated.'));
    }
}
