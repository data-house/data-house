<?php

namespace App\Http\Controllers;

use App\Actions\Collection\CreateCollection;
use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\CollectionType;
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
            'visibility' => Visibility::PERSONAL,
            'type' => CollectionType::STATIC,
            'strategy' => CollectionStrategy::STATIC,
            'draft' => false,
        ]);

        return redirect()->route('collections.show', $collection);
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        $collection->load('documents');

        return view('collection.show', [
            'collection' => $collection,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        return view('collection.edit', [
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

        return view('collection.show', [
            'collection' => $collection,
        ]);
    }
}
