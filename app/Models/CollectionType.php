<?php

namespace App\Models;

enum CollectionType: int
{
    /**
     * Collection content is static and does not automatically update
     */
    case STATIC = 10;

    /**
     * Collection content is dynamic and can change over time in an automatic way 
     * Collections created from search results are examples of a dynamic collection
     */
    case DYNAMIC = 20;
}
