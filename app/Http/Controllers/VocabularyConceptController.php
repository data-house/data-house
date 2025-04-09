<?php

namespace App\Http\Controllers;

use App\Models\SkosConcept;
use App\SkosRelationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VocabularyConceptController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(SkosConcept $vocabularyConcept)
    {

        $team = auth()->user()->currentTeam;

        $vocabularyConcept
            ->loadCount(['documents'  => fn($query) => $query->whereBelongsTo($team)])
            ->load([
                'conceptScheme',
                'related',
                'narrower',
                'broader',
                'documents' => fn($query) => $query->whereBelongsTo($team)->limit(6),
                'mappingMatches',
                'mappingMatches.conceptScheme',
            ]);

        return view('vocabulary-concept.show', [
            'concept' => $vocabularyConcept,
            'vocabulary' => $vocabularyConcept->conceptScheme,
            'team' => $team,
            'mappingMatches' => $vocabularyConcept->mappingMatches->groupBy('conceptScheme.pref_label'),
        ]);
    }

}
