<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;

enum ConceptRelationType: int
{
    /**
     * Enables the representation of associative (non-hierarchical) links, such as the
     * relationship between one type of event and a category of entities which
     * typically participate in it. Another use for skos:related is between
     * two categories where neither is more general or more specific
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     * @link skos:related
     */
    case RELATED = 1;

    /**
     * To assert that one concept is broader in meaning (i.e. more general) than another
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     * @link skos:broader
     */
    case BROADER = 10;

    /**
     * Inverse of broader, when one concept is narrower in meaning (i.e. more specific) than another
     *
     * @link https://www.w3.org/TR/2009/NOTE-skos-primer-20090818/#secrel
     * @link skos:narrower
     */
    case NARROWER = 20;

}
