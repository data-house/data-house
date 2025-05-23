<?php

namespace App\Models;

use App\Copilot\AnswerAggregationCopilotRequest;
use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use App\Copilot\Exceptions\CopilotException;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use Oneofftech\LaravelLanguageRecognizer\Support\Facades\LanguageRecognizer;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Question extends Model implements Htmlable
{
    use HasFactory;

    use HasUuids;

    use Searchable;

    use LogsActivity;

    protected static $recordEvents = ['updated'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question',
        'hash',
        'user_id',
        'team_id',
        'questionable',
        'language',
        'answer',
        'status',
        'execution_time',
        'target',
        'type',
        'visibility',
    ];

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = [
        'likes',
        'improvables',
        'dislikes',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => QuestionStatus::CREATED,
        'target' => QuestionTarget::SINGLE,
        'type' => QuestionType::FREE,
        'visibility' => Visibility::TEAM,
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the questioned model.
     */
    public function questionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function feedbacks()
    {
        return $this->hasMany(QuestionFeedback::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(QuestionReview::class);
    }
    
    public function likes()
    {
        return $this->feedbacks()->positive();
    }
    
    public function improvables()
    {
        return $this->feedbacks()->neutral();
    }

    public function dislikes()
    {
        return $this->feedbacks()->negative();
    }

    /**
     * Related questions to this question
     */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_relationship', 'source', 'target')
            ->using(QuestionRelationship::class)
            ->withPivot(['type']);
    }

    /**
     * Get the previous try of the same question
     */
    public function retryOf(): BelongsToMany
    {
        return $this->related()->wherePivot('type', QuestionRelation::RETRY);
    }

    /**
     * Children of this question
     */
    public function children(): BelongsToMany
    {
        return $this->related()->wherePivot('type', QuestionRelation::CHILDREN);
    }
    
    /**
     * Parent questions
     */
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_relationship', 'target', 'source')
            ->using(QuestionRelationship::class)
            ->wherePivot('type', QuestionRelation::CHILDREN)
            ->withPivot(['type']);
    }

    public function scopeHash(Builder $query, string $hash): void
    {
        $query->where('hash', $hash);
    }
    
    public function scopeAnswered(Builder $query): void
    {
        $query
            ->whereIn('status', [
                QuestionStatus::CANCELLED->value,
                QuestionStatus::ERROR->value,
                QuestionStatus::PROCESSED->value,
            ]);
    }
    
    public function scopePending(Builder $query): void
    {
        $query->whereIn('status', [
            QuestionStatus::PROCESSING->value,
            QuestionStatus::ANSWERING->value,
            QuestionStatus::CREATED->value,
        ]);
    }
    
    /**
     * Filter by questions asked by a user or the users' current team
     */
    public function scopeBelongingToUserOrTeam(Builder $query, User $user): void
    {
        $query->where(function($query) use ($user): void{
            $query
                ->where('team_id', $user->current_team_id)
                ->orWhere('user_id', $user->getKey());
        });
    }

    public function scopeAskedTo(Builder $query, Model $model): void
    {
        $query->where('questionable_id', $model->getKey()); // whereMorph? https://laravel.com/docs/10.x/eloquent-relationships#querying-morph-to-relationships
    }

    public function scopeAskedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }
    
    public function scopeNotAskedBy(Builder $query, User $user): void
    {
        $query->where(function($query) use ($user): void{
            $query->whereNull('user_id')->orWhere('user_id', '!=', $user->getKey());
        });
    }

    public function scopeRecentlyAsked(Builder $query, $minutes = 5)
    {
        $query->where('updated_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Filter for questions that the user can view
     */
    public function scopeViewableBy(Builder $query, User $user)
    {
        $query->where(function($query) use ($user){
            return $query->where('visibility', '>=', Visibility::PROTECTED->value)
                ->orWhere('user_id', $user->getKey())
                ->when($user->currentTeam, fn($subQuery) => $subQuery->orWhere('team_id', $user->currentTeam->getKey()));
        });
    }


    public function answerAsCopilotResponse()
    {
        return (new CopilotResponse($this->answer['text'], $this->answer['references']))
            ->setExecutionTime($this->execution_time);
    }

    
    public function isSingle()
    {
        return $this->target === QuestionTarget::SINGLE;
    }
    
    public function isMultiple()
    {
        return $this->target === QuestionTarget::MULTIPLE;
    }


    
    public function lockKey(): string
    {
        return 'question-lock:' . $this->uuid;
    }

    /**
     * Ask the question to the Copilot and wait for an answer
     */
    public function ask(): self
    {
        // if($this->status !== QuestionStatus::CREATED){
        //     return $this;
        // }

        $language = $this->language ?? $this->recognizeLanguage();

        Cache::lock($this->lockKey())->block(30, function() use($language): void {
        
            $this->fill([
                'language' => $language ?? 'en',
                'status' => QuestionStatus::ANSWERING,
            ]);

            $this->save();

        });

        $request = new CopilotRequest($this->uuid, $this->question, [''.$this->questionable->getCopilotKey()], $language);

        // TODO: maybe a check on another user asking the same question
        // $previouslyExecutedQuestion = Question::hash($request->hash())->first();

        // if($previouslyExecutedQuestion){
        //     return $previouslyExecutedQuestion->answerAsCopilotResponse();
        // }
        
        // We cache the response for each user as it requires time and resources.
        // This improves also the responsiveness of the system on the short run.
        // TODO: add a command that invalidates the questions based on the modified documents

        $response = Cache::remember('q-'.$request->hash(), config('copilot.cache.ttl'), function() use ($request) {
            return $this->executeQuestionRequest($request);
        });

        $this->disableLogging();

        Cache::lock($this->lockKey())->block(30, function() use($request, $response): void {
        
            $this->fill([
                'language' => $request->language,
                'answer' => [
                    'text' => $response->text,
                    'references' => $response->references,
                ],
                'execution_time' => $response->executionTime,
                'status' => QuestionStatus::PROCESSED,
            ]);

            $this->save();

        });
        
        return $this->fresh();
    }

    /**
     * Decompose the multiple question to single questions for each questionable item in the collection
     */
    public function decompose(): array
    {
        // if($this->status !== QuestionStatus::CREATED){
        //     return $this;
        // }

        $language = $this->language ?? $this->recognizeLanguage();

        Cache::lock($this->lockKey())->block(30, function() use($language): void {
        
            $this->fill([
                'language' => $language,
                'status' => QuestionStatus::ANSWERING,
            ]);

            $this->save();

        });

        // TODO: make this generic as we cannot assume that the questionable will have a documents relation and all entries are of type Document
        $documents = $this->questionable->documents()->select(['documents.'.((new Document())->getCopilotKeyName())])->get()->map->getCopilotKey();

        $request = new CopilotRequest($this->uuid, $this->question, $documents->toArray(), $language, $this->type?->copilotTemplate());

        // TODO: maybe a check on another user asking the same question
        // $previouslyExecutedQuestion = Question::hash($request->hash())->first();

        // if($previouslyExecutedQuestion){
        //     return $previouslyExecutedQuestion->answerAsCopilotResponse();
        // }
        
        // We cache the response for each user as it requires time and resources.
        // This improves also the responsiveness of the system on the short run.
        // TODO: add a command that invalidates the questions based on the modified documents


        $response = Cache::remember('q-'.$request->hash(), config('copilot.cache.ttl'), function() use ($request) {
            return $this->executeQuestionRequest($request);
        });

        $questions = null;

        Cache::lock($this->lockKey())->block(45, function() use($response, &$questions): void {
            $questions = DB::transaction(function() use($response) {

                $questions = $this->questionable->documents->map(function($document) use ($response) {
                    
                    logs()->info('this', ['this' => $this->toArray(), 'document' => $document->toArray()]);
                    
                    $singleQuestion = $response->text;

                    $question = $document->question($singleQuestion, $this->language);
                    
                    logs()->info('created question', ['question' => $question->toArray(), 'document' => $document->toArray()]);
    
                    $this->related()->attach($question, ['type' => QuestionRelation::CHILDREN]);
    
                    return $question;
                });

                return $questions;
            });
        });

        
        return [$this->fresh(), $questions ?? collect()];
    }

    /**
     * Aggregate all children answers into a single answer
     */
    public function aggregateAnswers(): self
    {
        $subQuestions = $this->children()->with('questionable.project')->whereNotNull('answer')->where('answer', '!=', 'null')->get();

        if($subQuestions->isEmpty()){
            throw new CopilotException(__('No answers to aggregate'));
        }

        $answers = $subQuestions->map(fn($q) => [
            'id' => $q->uuid,
            'lang' => $q->language,
            ...$q['answer']
        ])->toArray();

        $append = $subQuestions->map(fn($q) => [
            'id' => $q->uuid,
            'text' => $q->questionable instanceof Document ? 
                ($q->questionable->project ? "## Project **{$q->questionable->project->title}**" . PHP_EOL . PHP_EOL : "## Document **{$q->questionable->title}**" . PHP_EOL . PHP_EOL)
                : "",
        ])->toArray();

        $request = new AnswerAggregationCopilotRequest($this->uuid, $this->question, $answers, $this->language, $this->type?->copilotTemplate() ?? '0', $append);
        
        // We cache the response for each user as it requires time and resources.
        // This improves also the responsiveness of the system on the short run.
        // TODO: add a command that invalidates the questions based on the modified documents
        $response = Cache::remember('qa-'.$request->hash(), config('copilot.cache.ttl'), function() use ($request) {
            return $this->executeAggregationRequest($request);
        });

        $this->disableLogging();

        Cache::lock($this->lockKey())->block(30, function() use($request, $response, $subQuestions): void {
        
            $this->fill([
                'language' => $request->language,
                'answer' => [
                    'text' => $response->text,
                    'references' => $response->references,
                ],
                'execution_time' => $response->executionTime + $subQuestions->sum('execution_time'),
                'status' => QuestionStatus::PROCESSED,
            ]);

            $this->save();

        });

        
        return $this->fresh();
    }


    protected function executeQuestionRequest(CopilotRequest $request): CopilotResponse
    {
        /**
         * @var \App\Copilot\CopilotResponse
         */
        $response = null;

        // TODO: benchmarking should be at driver level

        $timing = Benchmark::measure(function() use ($request, &$response): void {
            $response = $this->questionable->questionableUsing()->question($request);
        });

        $response?->setExecutionTime($timing);

        return $response;
    }
    
    protected function executeAggregationRequest(AnswerAggregationCopilotRequest $request): CopilotResponse
    {
        /**
         * @var \App\Copilot\CopilotResponse
         */
        $response = null;

        // TODO: benchmarking should be at driver level

        $timing = Benchmark::measure(function() use ($request, &$response): void {
            $response = $this->questionable->questionableUsing()->aggregate($request);
        });

        $response?->setExecutionTime($timing);

        return $response;
    }

    /**
     * Attempt to recognize the language 
     */
    protected function recognizeLanguage(): ?string
    {
        try {
            $possibleLanguages = LanguageRecognizer::recognize($this->question);
    
            if($possibleLanguages['eng'] ?? $possibleLanguages['en'] ?? false){
                return 'en';
            }
    
            if($possibleLanguages['deu'] ?? $possibleLanguages['de'] ?? false){
                return 'de';
            }
    
            return 'en';
        } catch (\Throwable $th) {
            
            logs()->error("Failed to run language recognition", ['error' => $th->getMessage()]);

            return 'en';
        }
    }


    /**
     * Get the html representation of this response
     */
    public function toHtml(): HtmlString
    {
        return str($this->answer['text'] ?? $this->generateProgressReports())->markdown([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])->toHtmlString();
    }
    
    public function toText()
    {
        return $this->answer['text'] ?? '';
    }

    public function formattedText(): HtmlString
    {
        return $this->type ? str($this->type->formatQuestion($this->question))->markdown([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])->toHtmlString() : str($this->question)->markdown([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])->toHtmlString();
    }

    public function references(): SupportCollection
    {
        return collect($this->answer['references'] ?? [])
            ->sortBy('page_number', SORT_NATURAL)
            ->sortByDesc('score')
            ->values();
    }

    
    public function url()
    {
        return route('questions.show', $this);
    }

    protected function generateProgressReports()
    {
        if($this->status === QuestionStatus::ERROR){
            return __('Cannot generate answer due to communication error.');
        }

        if($this->status === QuestionStatus::CANCELLED){
            return __('Answer creation cancelled.');
        }

        if(!$this->language){
            return __('Recognizing the language of the question...');
        }


        if($this->status === QuestionStatus::PROCESSING){
            return __('Reading your document...');
        }

        return __('Writing the answer...');
    }


    public function isPending()
    {
        return in_array($this->status, [
            QuestionStatus::PROCESSING,
            QuestionStatus::ANSWERING,
            QuestionStatus::CREATED,
        ]);
    }
    
    public function hasError()
    {
        return $this->status == QuestionStatus::ERROR;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['team', 'user']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        logs()->info("Making question [{$this->id}] searchable");

        return [
            'id' => $this->id,
            'question' => $this->question,
            'questionable_id' => $this->questionable_id,
            'questionable_type' => $this->questionable_type,
            'answer' => $this->answer['text'] ?? null,
            'created_at' => $this->created_at,
            'user_id' => $this->user?->getKey(),
            'team_id' => $this->team?->getKey(),
            'author' => $this->user?->name,
            'team_name' => $this->team?->name,
            'target' => $this->target?->name,            
            'type' => $this->type?->name,
            'language' => $this->language,
            'visibility' => $this->visibility?->value,
        ];
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('question')
            ->logOnly(['answer'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
        // Chain fluent methods for configuration options
    }
    protected function casts(): array
    {
        return [
            // 'language' => LanguageAlpha2::class,
            'status' => QuestionStatus::class,
            'answer' => AsArrayObject::class,
            'execution_time' => 'float',
            'target' => QuestionTarget::class,
            'type' => QuestionType::class,
            'visibility' => Visibility::class,
        ];
    }

}
