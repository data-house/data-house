<?php

namespace App\Http\Controllers;

use App\Models\Question;
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

        $questions = $searchQuery ? Question::with(['questionable', 'user'])->search(e($searchQuery))->get() : Question::query()->with(['questionable', 'user'])->orderBy('status')->orderBy('created_at', 'DESC')->get();
        
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
        $question->load(['questionable', 'user']);

        return view('question.show', [
            'question' => $question,
        ]);
    }

}
