<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionStrategy;
use App\Models\Visibility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CreateMultipleQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $this->validate($request, [
            'question' => ['required', 'string', 'min:1', 'max:'.config('copilot.limits.question_length')],
            'strategy' => ['required', new Enum(CollectionStrategy::class)],
        ]);

        $collection = Collection::query()
            ->where('visibility', Visibility::SYSTEM->value)
            ->where('strategy', CollectionStrategy::LIBRARY->value)
            ->first();

        $question = $collection->question($validated['question']);

        return redirect()->route('questions.show', $question);
    }
}
