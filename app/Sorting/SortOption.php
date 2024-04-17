<?php

namespace App\Sorting;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilderRequest;

class SortOption 
{


    public function __construct(public string $name, public string $field, public string $direction)
    {
        
    }


    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }


    public function __toString()
    {
        return ($this->direction === 'DESC' ? '-' : '') . $this->name;
    }

    public function toFieldString()
    {
        return ($this->direction === 'DESC' ? '-' : '') . $this->field;
    }
    
    public function toAllowedSort(): AllowedSort
    {
        return AllowedSort::field(($this->direction === 'DESC' ? '-' : '') . $this->name, $this->field);
    }
    
}
