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
use Spatie\QueryBuilder\QueryBuilderRequest;

class SortingDropdown extends Component
{

    protected $request;

    protected $currentSorts;

    protected $configuration;

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
    }


    protected function url(SortOption $option): string
    {
        $path = $this->request->url();
        
        $query = collect($this->request->query())->except(['page']);

        $parameters = ['sort' => (string)$option];

        $parameters = array_merge($query->toArray(), $parameters);

        return $path
                .(str_contains($path, '?') ? '&' : '?')
                .Arr::query($parameters);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
 
        $options = $this->configuration->options()
            ->mapWithKeys(function($option, $name){
                return [$name => $this->url($option)];
            });

        $current = collect($this->currentSorts)->first() ?? ($this->request->isSearch() ? '_best_match' : $this->configuration->defaultSort());


        return view('components.sorting-dropdown', [
            'is_search' => $this->request->isSearch(),
            'options' => $options,
            'current' => ltrim($current, '-+'),
            
        ]);
    }
}
