<?php

namespace App\Livewire;

use App\Copilot\CopilotManager;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Question;
use App\Models\QuestionTarget;
use App\Models\Visibility;
use Meilisearch\Endpoints\Indexes;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;

class MultipleQuestionInput extends Component
{

    public $strategy; 

    /**
     * @var \App\Models\Collection|null
     */
    public $collection;

    public $question;

    public $length = 0;

    public $exceededMaximumLength = false;

    public $askingQuestion = false;

    public $guided = false;

    public $dailyQuestionLimit = null;

    public function rules() 
    {
        return [
            'question' => 'required|min:2|max:'.config('copilot.limits.question_length'),
        ];
    }

    public function mount($strategy, $collection = null)
    {
        // :target="DocumentSelection || Collection || Document || Askable"
        $this->strategy = $strategy;
        $this->collection = $collection;
    }
    
    protected function getListeners()
    {
        return ['copilot_answer' => 'handleAnswer'];
    }

    public function switchToGuided()
    {
        $this->guided = true;
    }

    public function switchToFreeForm()
    {
        $this->guided = false;
    }


    public function makeQuestion()
    {
        
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    #[Computed()]
    public function user()
    {
        return auth()->user();
    }

    #[Computed()]
    public function similarQuestions()
    {
        if(Str::length($this->question ?? '') <= 2)
        {
            return collect();
        }

        $user_id = $this->user->getKey();
        $team_id = $this->user->currentTeam?->getKey();
        $visibility = Visibility::PROTECTED->value;

        /**
         * @var \MeiliSearch\Search\SearchResult
         */
        $searchInternalMatches = null;


        $foundQuestions = Question::search(e($this->question), function(Indexes $meilisearch, string $query, array $options) use ($team_id, $user_id, $visibility, &$searchInternalMatches){

            // using same strategy as the scout driver
            // this will be the entrypoint to use the extra facets information
            // included in the search result response

            // Filtering questions to respect permission levels

            $targetFilter = 'target = ' . QuestionTarget::MULTIPLE->name;

            $options["filter"] = $targetFilter . ' AND (' . ($team_id ? "user_id IN [{$user_id}] OR team_id IN [{$team_id}] OR visibility IN [{$visibility}]" : "user_id IN [{$user_id}] OR visibility IN [{$visibility}]") . ')';
            
            $options['attributesToHighlight'] = ['question'];
            $options['highlightPreTag'] = '**';
            $options['highlightPostTag'] = '**';

            return $searchInternalMatches = $meilisearch->search($query, $options);

        })
        ->query(fn (Builder $query) => $query->with(['questionable', 'user']))
        ->paginate(5);

        $internalSearchRepresentationOfHits = collect($searchInternalMatches?->getHits() ?? [])->mapWithKeys(function($el){
            return [$el['id'] => $el];
        });

        if($internalSearchRepresentationOfHits->isNotEmpty()){
            $foundQuestions->map(function($question) use ($internalSearchRepresentationOfHits) {

                $match = $internalSearchRepresentationOfHits[$question->getKey()] ?? collect();

                $question->setRelation('search_match', ['question' => $match['_formatted']['question'] ?? null]);
                return $question;
            });
        }

        return $foundQuestions;
    }

    public function handleAnswer()
    {
        $this->question = '';
    }


    public function render()
    {

        $this->length = Str::length($this->question ?? '');

        $this->exceededMaximumLength = $this->length > config('copilot.limits.question_length');

        $this->dailyQuestionLimit = CopilotManager::questionLimitFor(auth()->user());

        return view('livewire.multiple-question-input');
    }
}
