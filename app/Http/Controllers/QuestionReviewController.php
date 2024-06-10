<?php

namespace App\Http\Controllers;

use App\Models\QuestionReview;
use Illuminate\Http\Request;

class QuestionReviewController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(QuestionReview::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userTeam = auth()->user()->current_team_id;

        $questionReviews = QuestionReview::query()
            ->with('question', 'assignees')
            ->where('team_id', $userTeam)
            ->orderBy('status', 'ASC')
            ->paginate();

        return view('question-review.index', [
            'reviews' => $questionReviews,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionReview $questionReview)
    {

        $questionReview->load('question', 'assignees');

        return view('question-review.show', [
            'review' => $questionReview,
            'question' => $questionReview->question,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QuestionReview $questionReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionReview $questionReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionReview $questionReview)
    {
        //
    }
}
