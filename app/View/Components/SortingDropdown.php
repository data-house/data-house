<?php

namespace App\View\Components;

use App\Sorting\Sorting;
use App\Sorting\SortOption;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Nette\InvalidStateException;
use Spatie\QueryBuilder\QueryBuilderRequest;

class SortingDropdown extends Component
{

    protected $request;

    protected $currentSorts;

    protected $configuration;
    
    /**
     * @var \App\Sorting\SortOption
     */
    public $current;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $model
    )
    {
        $this->request = app(Request::class);

        $this->currentSorts = QueryBuilderRequest::fromRequest($this->request)->sorts();

        $this->configuration = Sorting::for($model);

        $this->current = $this->currentOption();
    }


    public function url(SortOption $option): string
    {
        $path = $this->request->url();
        
        $query = collect($this->request->query())->except(['page']);

        $parameters = ['sort' => (string)$option];

        $parameters = array_merge($query->toArray(), $parameters);

        return $path
                .(str_contains($path, '?') ? '&' : '?')
                .Arr::query($parameters);
    }


    public function currentOption(): SortOption
    {
        if($this->currentSorts->isEmpty()){
            return $this->configuration->defaultSort();
        }

        $currentSortName = $this->currentSorts->first();

        /**
         * @var \App\Sorting\SortOption
         */
        $option = $this->configuration->options()->get(ltrim($currentSortName, '-'));

        $option->setDirection(strpos($currentSortName, '-') === 0 ? 'DESC': 'ASC');

        if(is_null($option)){
            throw new InvalidStateException(__('Requested sort is not available.'));
        }

        return $option;
    }

    public function isCurrent(SortOption $option): bool
    {
        return $this->current->is($option);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    { 
        $options = $this->configuration->options();

        return view('components.sorting-dropdown', [
            'is_search' => $this->request->isSearch(),
            'options' => $options,
            'current' => $this->current,
            'current_direction' => strtolower($this->current->direction),
        ]);
    }
}
