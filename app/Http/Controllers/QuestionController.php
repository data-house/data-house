<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Question::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchQuery = $request->has('s') ? e($request->input('s')) : null;

        $questions = $searchQuery ? 
            Question::search(e($searchQuery))
                ->query(fn (Builder $query) => $query->with(['questionable', 'user']))
                ->paginate(200)
            :
            Question::query()->with(['questionable', 'user'])
                ->orderBy('status')
                ->orderBy('created_at', 'DESC')
                ->where(function($query){
                    return $query->whereNotNull('user_id')->orWhereNotNull('team_id');
                })
                ->paginate(200);
        
        return view('question.index', [
            'questions' => $questions,
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $question->load([
            'questionable',
            'user',
            'children.questionable',
            'ancestors.questionable',
        ]);

        return view('question.show', [
            'question' => $question,
        ]);
    }

}
