<?php

namespace App\Http\Controllers;

use App\Models\QuestionReview;
use App\Models\ReviewEvaluationResult;
use App\Models\ReviewStatus;
use App\Models\Visibility;
use App\Notifications\QuestionReviewCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

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
            'current_user_is_coordinator' => $questionReview->status !== ReviewStatus::COMPLETED && $questionReview->isCoordinator($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionReview $questionReview)
    {
        abort_unless($questionReview->isCoordinator($request->user()), 401);

        $validated = $this->validate($request, [
            'evaluation' => ['required', Rule::enum(ReviewEvaluationResult::class)],
            'updated_answer' => 'nullable|string|max:4000|required_if:evaluation,' . ReviewEvaluationResult::CHANGES_APPLIED->value,
            'remark' => 'nullable|string|max:4000|required_if:evaluation,' . ReviewEvaluationResult::REJECTED->value,
        ]);

        $question = $questionReview->question;

        $updatedReview = DB::transaction(function() use ($questionReview, $validated, $question) {

            $questionReview->status = ReviewStatus::COMPLETED;

            $questionReview->evaluation_result = ReviewEvaluationResult::from($validated['evaluation']);

            if(!empty($validated['remark'])){
                $questionReview->remarks = $validated['remark'];
            }

            if($questionReview->isDirty()){
                $questionReview->save();
            }

            if(!empty($validated['updated_answer'])){
                $question->fill([
                    'answer' => [
                        'text' => $validated['updated_answer'],
                        'references' => $question['answer']['references'],
                    ],
                ]);
    
            }

            if($questionReview->evaluation_result !== ReviewEvaluationResult::REJECTED){
                $question->visibility = Visibility::PROTECTED;
            }
                
            if($question->isDirty()){
                $question->save();
            }

            return $questionReview->fresh();
        });

        Notification::send($updatedReview->subscribers(), new QuestionReviewCompleted($updatedReview));

        return to_route('question-reviews.show', $questionReview)
            ->with('flash.banner', __('Question review completed.'));
    }

}
