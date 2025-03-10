<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Question;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {

        $documents = Document::query()
            ->orderBy('updated_at', 'DESC')
            ->visibleBy($request->user())
            ->limit(8)
            ->get();

        $questions = Question::query()
            ->with(['questionable', 'user'])
            ->orderBy('status')
            ->orderBy('updated_at', 'DESC')
            ->where(function($query){
                return $query->whereNotNull('user_id')->orWhereNotNull('team_id');
            })
            ->viewableBy($request->user())
            ->paginate(8);

        return view('dashboard', [
            'documents' => $documents,
            'questions' => $questions,
        ]);
    }
}
