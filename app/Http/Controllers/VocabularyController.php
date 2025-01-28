<?php

namespace App\Http\Controllers;

use App\Models\SkosConceptScheme;
use App\Models\Team;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('vocabulary.index', [
            'vocabularies' => SkosConceptScheme::query()->orderBy('pref_label', 'ASC')->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SkosConceptScheme $vocabulary, Request $request)
    {

        /**
         * @var Team
         */
        $team = auth()->user()->currentTeam;

        $recents = $vocabulary->concepts()->orderBy('updated_at', 'DESC')->limit(6)->get();

        $topMostLinkedConcepts = $vocabulary->concepts()
            ->whereHas('documents', fn($query) => $query->whereBelongsTo($team), '>=', 1)
            ->withCount(['documents'=> fn($query) => $query->whereBelongsTo($team) ])
            ->with(['documents' => fn($query) => $query->whereBelongsTo($team)->limit(3)->orderByPivot('created_at', 'desc')])
            ->orderBy('documents_count', 'DESC')
            ->limit(10)
            ->get()
            ;
        

        return view('vocabulary.show', [
            'vocabulary' => $vocabulary,
            'recentConcepts' => $recents,
            'topMostLinkedConcepts' => $topMostLinkedConcepts,
        ]);
    }


}
