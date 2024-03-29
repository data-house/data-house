<?php

namespace App\Http\Controllers;

use App\Models\Star;
use Illuminate\Http\Request;

class StarController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Star::class);
    }


    public function index(Request $request)
    {
        $searchQuery = $request->has('s') ? $request->input('s') : null;

        $stars = ($searchQuery)
            ? Star::search($searchQuery)->where('user_id', $request->user()->getKey())->paginate(50)
            : Star::query()->byUser(auth()->user())->paginate(50);

        $stars->load('starrable');

        $stars->withQueryString();

        return view('star.index', [
            'stars' => $stars,
            'documents' => collect($stars->items())->map->starrable,
            'searchQuery' => $searchQuery,
            'filters' => [],
            'is_search' => $searchQuery,
        ]);
    }


}
