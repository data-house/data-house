<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\QuestionType;
use App\Models\Visibility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Laravel\Pennant\Feature;
use Nette\InvalidStateException;

class CreateMultipleQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $this->validate($request, [
            'question' => ['required', 'string', 'min:2', 'max:'.config('copilot.limits.question_length')],
            'strategy' => ['required', new Enum(CollectionStrategy::class)],
            'collection' => ['nullable', 'exists:collections,id', 'required_if:strategy,' . CollectionStrategy::STATIC->value],
            'guidance' => ['sometimes', 'nullable', 'boolean'],
        ]);

        // Strategy LIBRARY is only available is feature flag 'ai.question-whole-library' is set
        abort_if($validated['strategy'] === CollectionStrategy::LIBRARY->value && Feature::inactive('ai.question-whole-library'), 400);

        $useTemplate = $request->boolean('guidance', false);

        $collection = ($validated['collection'] ?? false) ? Collection::find($validated['collection']) : Collection::query()
            ->where('visibility', Visibility::SYSTEM->value)
            ->where('strategy', CollectionStrategy::LIBRARY->value)
            ->first();

        try {
            $question = $collection->question($validated['question'], $useTemplate ? QuestionType::DESCRIPTIVE : QuestionType::FREE);
    
            return redirect()->route('questions.show', $question);
        } catch (InvalidStateException $th) {
            throw ValidationException::withMessages(['question' => $th->getMessage()]);
        }
    }
}
