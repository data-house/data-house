<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Visibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Meilisearch\Endpoints\Indexes;

class QuestionController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Question::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchQuery = $request->has('s') ? e($request->input('s')) : null;

        $user_id = auth()->user()->getKey();
        $team_id = auth()->user()->currentTeam->getKey();
        $visibility = Visibility::PROTECTED->value;


        $questions = $searchQuery ? 
            Question::search(e($searchQuery), function(Indexes $meilisearch, string $query, array $options) use ($team_id, $user_id, $visibility){

                    // using same strategy as the scout driver
                    // this will be the entrypoint to use the extra facets information
                    // included in the search result response

                    // Filtering questions to respect permission levels

                    $options["filter"] = "user_id IN [{$user_id}] OR team_id IN [{$team_id}] OR visibility IN [{$visibility}]";
                    
                    return $meilisearch->rawSearch($query, $options);
        
                })
                ->query(fn (Builder $query) => $query->with(['questionable', 'user']))
                // A more resilient solution could be to extend the Scout driver
                // and use tentant tokens that allows to configure what a
                // user can see in a multi-tenant index
                // https://www.meilisearch.com/docs/learn/security/tenant_tokens
                ->paginate(200)
            :
            Question::query()->with(['questionable', 'user'])
                ->orderBy('status')
                ->orderBy('created_at', 'DESC')
                ->where(function($query){
                    return $query->whereNotNull('user_id')->orWhereNotNull('team_id');
                })
                ->viewableBy($request->user())
                ->paginate(200);
        
        return view('question.index', [
            'questions' => $questions,
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $question->load([
            'questionable',
            'user',
            'children.questionable',
            'ancestors.questionable',
        ]);

        return view('question.show', [
            'question' => $question,
        ]);
    }

}
