<?php

namespace App\Sorting;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilderRequest;

class Sorting 
{

    protected $allowedSorts;
    
    protected $defaultSort;
    

    protected function __construct($model)
    {
        $this->allowedSorts = collect(config("library.{$model}.sorting.allowed", []))
            ->mapWithKeys(
                fn($field, $name) => [ltrim($name, '-+') => new SortOption(ltrim($name, '-+'), $field, strpos($name, '-') === 0 ? 'DESC' : 'ASC')]
            );

        $this->defaultSort = config("library.{$model}.sorting.default", null);

        throw_if(is_null($this->defaultSort), InvalidArgumentException::class, 'Default sorting configuration required.');
        throw_if(is_null($this->allowedSorts[ltrim($this->defaultSort, '-+')] ?? null), InvalidArgumentException::class, 'Default sorting must be allowed in configuration.');
    }



    public function defaultSort(): SortOption
    {
        $option = $this->allowedSorts[ltrim($this->defaultSort, '-+')];

        $sortingOrder = (strpos($this->defaultSort, '-') === 0 ? 'DESC' : 'ASC') ;

        return  $option->setDirection($sortingOrder);
    }
    
    public function allowerdSorts()
    {
        return $this->allowedSorts;
    }
    
    public function options(): Collection
    {
        return $this->allowedSorts;
    }
    

    public function allowedSortsForBuilder(): array
    {
        return $this->allowedSorts->map->toAllowedSort()->toArray();
    }
    
    public function mapRequested(Collection $requested)
    {
        return $requested->map(function($sort){
            $cleanName = ltrim($sort, '-+');

            if(!$this->allowedSorts->has($cleanName)){
                return null;
            }

            return $this->allowedSorts->get($cleanName)->setDirection(strpos($sort, '-') === 0 ? 'DESC' : 'ASC');
        });
    }

    // public function from(Request $request): Collection
    // {
    //     $requested = QueryBuilderRequest::fromRequest($request)->sorts();

    //     if($requested->isEmpty()){

    //         $sort = $this->defaultSortForBuilder();

    //         return collect([ltrim($sort, '-+') => strpos($this->defaultSort, '-') === 0 ? 'DESC' : 'ASC']);
    //     }

    //     // TODO: remove best_match if present

    //     return $requested->filter(function($sort){
    //         return $sort !== '_best_match' && $sort !== '-_best_match';
    //     })->mapWithKeys(function($sort){

    //         $order = $this->allowedSorts[ltrim($sort, '-+')] ?? $this->allowedSorts['-'.$sort];

    //         return [$order => strpos($sort, '-') === 0 ? 'DESC' : 'ASC'];
    //     });
    // }


    /**
     * Get the sorting configuration for the specified resource
     */
    public static function for(string $model): self
    {
        return new Sorting(ltrim($model, '\\'));
    }
}
