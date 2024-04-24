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

    public function invertDirection()
    {
        return new self($this->name, $this->field, $this->direction === 'DESC' ? 'ASC' : 'DESC');
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


    public function is(SortOption|string $option)
    {
        if(is_string($option)){
            return $this->name === $option;
        }

        return $this->name === $option->name;
    }
    
}
