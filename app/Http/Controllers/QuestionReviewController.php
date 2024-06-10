<?php

namespace App\Http\Controllers;

use App\Models\QuestionReview;
use App\Models\ReviewStatus;
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
            ->with('question', 'assignees', 'coordinator')
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
        $user = auth()->user();

        $questionReview->load('question', 'assignees', 'feedbacks.user');

        $questionReview->loadCount('assignees');

        return view('question-review.show', [
            'review' => $questionReview,
            'question' => $questionReview->question,
            'current_user_review_missing' => $questionReview->status !== ReviewStatus::COMPLETED && $questionReview->isAssigned($user) && !$questionReview->feedbacks->pluck('reviewer_user_id')->contains($user->getKey()),
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
