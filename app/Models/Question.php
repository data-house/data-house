<?php

namespace App\Models;

use App\Copilot\CopilotRequest;
use App\Copilot\CopilotResponse;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use Oneofftech\LaravelLanguageRecognizer\Support\Facades\LanguageRecognizer;

class Question extends Model implements Htmlable
{
    use HasFactory;

    use HasUuids;

    use Searchable;

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
        'questionable',
        'language',
        'answer',
        'status',
        'execution_time',
    ];

    protected $casts = [
        // 'language' => LanguageAlpha2::class,
        'status' => QuestionStatus::class,
        'answer' => AsArrayObject::class,
        'execution_time' => 'float',
    ];

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = [
        'likes',
        'dislikes',
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
    
    public function likes()
    {
        return $this->feedbacks()->like();
    }
    
    public function dislikes()
    {
        return $this->feedbacks()->dislike();
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
    
    public function scopeAskedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->getKey());
    }


    public function answerAsCopilotResponse()
    {
        return (new CopilotResponse($this->answer['text'], $this->answer['references']))
            ->setExecutionTime($this->execution_time);
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

        Cache::lock($this->lockKey())->block(30, function() use($language) {
        
            $this->fill([
                'language' => $language,
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

        Cache::lock($this->lockKey())->block(30, function() use($request, $response) {
        
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


    protected function executeQuestionRequest(CopilotRequest $request): CopilotResponse
    {
        /**
         * @var \App\Copilot\CopilotResponse
         */
        $response = null;

        $timing = Benchmark::measure(function() use ($request, &$response) {
            $response = $this->questionable->questionableUsing()->question($request);
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
    
            return null;
        } catch (\Throwable $th) {
            
            logs()->error("Failed to run language recognition", ['error' => $th->getMessage()]);

            return null;
        }
    }


    /**
     * Get the html representation of this response
     */
    public function toHtml()
    {
        return Str::markdown($this->answer['text'] ?? $this->generateProgressReports());
    }
    
    public function toText()
    {
        return $this->answer['text'];
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

}
