<?php

namespace App\Copilot;

use App\Copilot\Events\ModelsQuestionable;
use App\Copilot\Events\ModelsUnquestionable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Scope;

class QuestionableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(EloquentBuilder $builder, Model $model)
    {
        //
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('questionable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('copilot.chunk.questionable', 50), function ($models) {
                $models->filter->shouldBeQuestionable()->questionable();

                event(new ModelsQuestionable($models));
            });
        });

        $builder->macro('unquestionable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('copilot.chunk.unquestionable', 50), function ($models) {
                $models->unquestionable();

                event(new ModelsUnquestionable($models));
            });
        });

        HasManyThrough::macro('questionable', function ($chunk = null) {
            /** @var HasManyThrough $this */
            $this->chunkById($chunk ?: config('copilot.chunk.questionable', 50), function ($models) {
                $models->filter->shouldBeQuestionable()->questionable();

                event(new ModelsQuestionable($models));
            });
        });

        HasManyThrough::macro('unquestionable', function ($chunk = null) {
            /** @var HasManyThrough $this */
            $this->chunkById($chunk ?: config('copilot.chunk.questionable', 50), function ($models) {
                $models->unquestionable();

                event(new ModelsUnquestionable($models));
            });
        });
    }
}
