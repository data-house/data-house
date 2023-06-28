<?php

namespace App\Copilot;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;

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
        return $query;
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
     * @return mixed
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
