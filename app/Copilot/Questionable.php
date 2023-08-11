<?php

namespace App\Copilot;

use App\Jobs\AskQuestionJob;
use App\Models\Question;
use App\Models\QuestionStatus;
use \Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;

trait Questionable
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootQuestionable()
    {
        static::addGlobalScope(new QuestionableScope);

        (new static)->registerQuestionableMacros();
    }

    /**
     * Register the questionable macros.
     *
     * @return void
     */
    public function registerQuestionableMacros()
    {
        $self = $this;

        BaseCollection::macro('questionable', function () use ($self) {
            $self->queueMakeQuestionable($this);
        });

        BaseCollection::macro('unquestionable', function () use ($self) {
            $self->queueRemoveFromQuestionable($this);
        });
    }

    /**
     * Dispatch the job to make the given models questionable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueMakeQuestionable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        return $models->first()->questionableUsing()->update($models);
    }

    /**
     * Dispatch the job to make the given models unquestionable.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function queueRemoveFromQuestionable($models)
    {
        if ($models->isEmpty()) {
            return;
        }
        
        return $models->first()->questionableUsing()->delete($models);
    }

    /**
     * Determine if the model should be questionable.
     *
     * @return bool
     */
    public function shouldBeQuestionable()
    {
        return true;
    }

    /**
     * Get all of the model's questions.
     */
    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'questionable');
    }


    /**
     * Ask a question to the document using the configured Copilot engine
     * 
     * @param string $query
     * @return \App\Models\Question
     */
    public function question(string $query, ?string $language = null): Question
    {
        $uuid = Str::uuid();

        $request = new CopilotRequest($uuid, trim($query), [''.$this->getCopilotKey()], $language);

        $previouslyExecutedQuestion = Question::hash($request->hash())->first();

        // TODO: decide if duplicate the question for the current user

        if($previouslyExecutedQuestion){
            return $previouslyExecutedQuestion;
        }

        // Save question and response as part of user's history

        $question = $this->questions()->create([
            'uuid' => $uuid,
            'question' => $request->question,
            'hash' => $request->hash(),
            'user_id' => auth()->user()?->getKey(),
            'team_id' => auth()->user()?->currentTeam?->getKey(),
            'language' => $language,
        ]);

        AskQuestionJob::dispatch($question);

        return $question;
    }

    protected function executeQuestionRequest(CopilotRequest $request): CopilotResponse
    {
        /**
         * @var \App\Copilot\CopilotResponse
         */
        $response = null;

        $timing = Benchmark::measure(function() use ($request, &$response) {
            $response = $this->questionableUsing()->question($request);
        });

        $response?->setExecutionTime($timing);

        return $response;
    }

    /**
     * Add all instances of the model to Copilot and making them questionable.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function addAllToCopilot($chunk = null)
    {
        $self = new static;

        $self->newQuery()
            ->when(true, function ($query) use ($self) {
                $self->addAllToCopilotUsing($query);
            })
            ->orderBy(
                $self->qualifyColumn($self->getCopilotKeyName())
            )
            ->questionable($chunk);
    }
    
    /**
     * Remove all instances of the model from Copilot.
     *
     * @param  int  $chunk
     * @return void
     */
    public static function removeAllFromCopilot($chunk = null)
    {
        $self = new static;

        $self->newQuery()
            ->when(true, function ($query) use ($self) {
                $self->addAllToCopilotUsing($query);
            })
            ->orderBy(
                $self->qualifyColumn($self->getCopilotKeyName())
            )
            ->unquestionable($chunk);
    }

    /**
     * Modify the query used to retrieve models when making all of the models questionable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function addAllToCopilotUsing(EloquentBuilder $query)
    {
        return $query->where('mime', 'application/pdf');
    }

    /**
     * Make the given model instance questionable.
     *
     * @return void
     */
    public function questionable()
    {
        $this->newCollection([$this])->questionable();
    }

    /**
     * Remove the given model instance from the Copilot.
     *
     * @return void
     */
    public function unquestionable()
    {
        $this->newCollection([$this])->unquestionable();
    }


    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toQuestionableArray()
    {
        return $this->toArray();
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

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getCopilotKey()
    {
        return $this->getKey();
    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getCopilotKeyName()
    {
        return $this->getKeyName();
    }
}
