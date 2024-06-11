<?php

namespace App\Http\Controllers;

use App\Models\FeedbackVote;
use App\Models\QuestionReview;
use App\Models\ReviewFeedback;
use App\Models\ReviewStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewFeedbackController extends Controller
{
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, QuestionReview $questionReview)
    {
        abort_unless($request->user(), 401);

        $this->authorize('update', $questionReview);

        $validated = $this->validate($request, [
            'rating' => ['required', Rule::enum(FeedbackVote::class)],
            'comment' => 'nullable|string|max:4000',
        ]);

        DB::transaction(function() use ($questionReview, $validated) {
            $questionReview->feedbacks()->create([
                'reviewer_user_id' => auth()->user()->getKey(),
                'vote' => FeedbackVote::from($validated['rating']),
            ]);

            if($questionReview->status === ReviewStatus::SUBMITTED){
                $questionReview->status = ReviewStatus::IN_PROGRESS;
                $questionReview->save();
            }

            if(!empty($validated['comment'])){
                $questionReview->addNote($validated['comment']);
            }
        });

        return to_route('question-reviews.show', $questionReview)
            ->with('flash.banner', __('Review registered.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReviewFeedback $reviewFeedback)
    {
        abort_unless(auth()->user(), 401);

        $review = $reviewFeedback->review;

        $this->authorize('update', $review);

        abort_unless($reviewFeedback->reviewer_user_id === auth()->user()->getKey(), 401);


        $reviewFeedback->delete();

        return to_route('question-reviews.show', $review)
            ->with('flash.banner', __('Review deleted.'));
    }
}
