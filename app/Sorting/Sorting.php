<?php

namespace App\Sorting;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedSort;

class Sorting 
{

    protected $allowedSorts;
    
    protected $defaultSort;
    

    protected function __construct($model)
    {
        $this->allowedSorts = collect(config("library.{$model}.sorting.allowed_sorts", []));

        $this->defaultSort = config("library.{$model}.sorting.default_sort", null);

        throw_if(is_null($this->defaultSort), InvalidArgumentException::class, 'Default sorting configuration required.');
    }



    public function defaultSort(): string
    {
        return $this->defaultSort;
    }
    
    public function allowerdSorts()
    {
        return $this->allowedSorts;
    }
    
    public function options(): Collection
    {
        return $this->allowedSorts->keys();
    }
    
    public function defaultSortForBuilder(): string
    {
        $sortingOrder = (strpos($this->defaultSort, '-') === 0 ? '-' : '') ;

        return  $sortingOrder . ($this->allowedSorts[ltrim($this->defaultSort, '-+')] ?? $this->allowedSorts[$this->defaultSort]);
    }

    public function allowedSortsForBuilder(): array
    {
        return $this->allowedSorts->map(fn($field, $sort) => AllowedSort::field($sort, ltrim($field, '-+')))->toArray();
    }


    /**
     * Get the sorting configuration for the specified resource
     */
    public static function for(string $model): self
    {
        return new Sorting(ltrim($model, '\\'));
    }
}
