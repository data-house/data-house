<?php

namespace App\Copilot;

use App\Jobs\AskMultipleQuestionJob;
use App\Jobs\AskQuestionJob;
use App\Models\Question;
use App\Models\QuestionStatus;
use App\Models\QuestionTarget;
use App\Models\QuestionType;
use \Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;
use Nette\InvalidStateException;

trait AskMultipleQuestion
{
    /**
     * Get all of the model's questions.
     */
    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'questionable');
    }


    /**
     * Ask a question using the configured Copilot engine
     * 
     * @param string $query
     * @return \App\Models\Question
     */
    public function question(string $query, QuestionType $type = QuestionType::FREE, ?string $language = null): Question
    {
        if(Copilot::disabled() || ! Copilot::hasQuestionFeatures()){
            throw new InvalidStateException(__('Question and answer module is disabled'));
        }

        $uuid = Str::uuid();

        $request = new CopilotRequest($uuid, trim($query), [''.$this->getCopilotKey()], $language, $type->copilotTemplate());

        $previouslyExecutedQuestion = Question::hash($request->hash())->first();

        // TODO: decide if duplicate the question for the current user

        if($previouslyExecutedQuestion){
            return $previouslyExecutedQuestion;
        }

        if(!CopilotManager::hasRemainingQuestions(auth()->user())){
            throw new InvalidStateException(__('You reached your daily limit of :amount questions/day. You will be able to ask questions tomorrow.', [
                'amount' => config('copilot.limits.questions_per_user_per_day'),
            ]));
        }

        // Save question and response as part of user's history

        $question = $this->questions()->create([
            'uuid' => $uuid,
            'question' => $request->question,
            'hash' => $request->hash(),
            'user_id' => auth()->user()?->getKey(),
            'team_id' => auth()->user()?->currentTeam?->getKey(),
            'language' => $language,
            'target' => QuestionTarget::MULTIPLE,
            'type' => $type,
        ]);

        AskMultipleQuestionJob::dispatch($question);

        return $question;
    }

    public function getCopilotKey(): string
    {
        return $this->documents->map->getCopilotKey()->join('-');
    }

    /**
     * Get the Copilot engine for the model.
     *
     * @return \App\Copilot\Engines\Engine
     */
    public function questionableUsing()
    {
        return app(CopilotManager::class)->driver();
    }

}
